<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Message;
use App\Entity\Report;
use App\Form\MessageType;
use App\Form\ReportType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/messages')]
#[IsGranted('ROLE_USER')]
final class MessageController extends AbstractController
{
    #[Route('/', name: 'app_message_index', methods: ['GET'])]
    public function index(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        
        $conversations = $messageRepository->findConversationsByUser($user);
        
        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/annonce/{id}', name: 'app_message_conversation', methods: ['GET', 'POST'])]
    public function conversation(Annonce $annonce, Request $request, MessageRepository $messageRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        $messages = $messageRepository->findConversationMessages($annonce, $user);
        
        foreach ($messages as $message) {
            if ($message->getReceiver() === $user && !$message->isRead()) {
                $message->setIsRead(true);
            }
        }
        $entityManager->flush();
        
        $newMessage = new Message();
        $newMessage->setSender($user);
        $newMessage->setAnnonce($annonce);
        
        $receiver = $annonce->getUser() !== $user ? $annonce->getUser() : null;
        if (!$receiver && !empty($messages)) {
            foreach ($messages as $msg) {
                if ($msg->getSender() !== $user) {
                    $receiver = $msg->getSender();
                    break;
                } elseif ($msg->getReceiver() !== $user) {
                    $receiver = $msg->getReceiver();
                    break;
                }
            }
        }
        
        if ($receiver) {
            $newMessage->setReceiver($receiver);
        }
        
        $form = $this->createForm(MessageType::class, $newMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $receiver) {
            $entityManager->persist($newMessage);
            $entityManager->flush();

            $this->addFlash('success', 'Message envoyé avec succès !');
            
            return $this->redirectToRoute('app_message_conversation', ['id' => $annonce->getId()]);
        }

        return $this->render('message/conversation.html.twig', [
            'annonce' => $annonce,
            'messages' => $messages,
            'form' => $form,
            'receiver' => $receiver,
        ]);
    }

    #[Route('/contacter/{id}', name: 'app_message_contact', methods: ['GET', 'POST'])]
    public function contactSeller(Annonce $annonce, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if ($annonce->getUser() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas vous contacter vous-même !');
            return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
        }
        
        if (!$annonce->isPublished()) {
            $this->addFlash('error', 'Cette annonce n\'est pas disponible.');
            return $this->redirectToRoute('app_annonce_index');
        }
        
        $message = new Message();
        $message->setSender($user);
        $message->setReceiver($annonce->getUser());
        $message->setAnnonce($annonce);
        
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            
            return $this->redirectToRoute('app_message_conversation', ['id' => $annonce->getId()]);
        }

        return $this->render('message/contact.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    #[Route('/non-lus/count', name: 'app_message_unread_count', methods: ['GET'])]
    public function unreadCount(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $count = $messageRepository->countUnreadMessages($user);
        
        return $this->json(['count' => $count]);
    }

    #[Route('/signaler/{id}', name: 'app_message_report', methods: ['GET', 'POST'])]
    public function reportConversation(Annonce $annonce, Request $request, EntityManagerInterface $entityManager, MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        
        $messages = $messageRepository->findConversationMessages($annonce, $user);
        if (empty($messages)) {
            $this->addFlash('error', 'Vous ne pouvez signaler que les conversations auxquelles vous participez.');
            return $this->redirectToRoute('app_message_index');
        }
        
        $reportedUser = null;
        foreach ($messages as $message) {
            if ($message->getSender() !== $user) {
                $reportedUser = $message->getSender();
                break;
            } elseif ($message->getReceiver() !== $user) {
                $reportedUser = $message->getReceiver();
                break;
            }
        }
        
        if (!$reportedUser) {
            $this->addFlash('error', 'Impossible de déterminer l\'utilisateur à signaler.');
            return $this->redirectToRoute('app_message_conversation', ['id' => $annonce->getId()]);
        }
        
        $report = new Report();
        $report->setType(Report::TYPE_CONVERSATION);
        $report->setReporter($user);
        $report->setReportedUser($reportedUser);
        $report->setAnnonce($annonce);
        
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($report);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre signalement a été envoyé. Notre équipe va l\'examiner rapidement.');
            return $this->redirectToRoute('app_message_conversation', ['id' => $annonce->getId()]);
        }
        
        return $this->render('message/report.html.twig', [
            'form' => $form,
            'annonce' => $annonce,
            'reportedUser' => $reportedUser,
        ]);
    }

    #[Route('/signaler-annonce/{id}', name: 'app_annonce_report', methods: ['GET', 'POST'])]
    public function reportAnnonce(Annonce $annonce, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if ($annonce->getUser() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas signaler votre propre annonce.');
            return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
        }
        
        if (!$annonce->isPublished()) {
            $this->addFlash('error', 'Cette annonce n\'est pas disponible.');
            return $this->redirectToRoute('app_annonce_index');
        }
        
        $report = new Report();
        $report->setType(Report::TYPE_ANNONCE);
        $report->setReporter($user);
        $report->setReportedUser($annonce->getUser());
        $report->setAnnonce($annonce);
        
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($report);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre signalement a été envoyé. Notre équipe va l\'examiner rapidement.');
            return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
        }
        
        return $this->render('annonce/report.html.twig', [
            'form' => $form,
            'annonce' => $annonce,
        ]);
    }
}

