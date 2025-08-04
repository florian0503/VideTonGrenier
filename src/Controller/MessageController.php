<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Message;
use App\Form\MessageType;
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
        
        // Récupérer les conversations (groupées par annonce)
        $conversations = $messageRepository->findConversationsByUser($user);
        
        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/annonce/{id}', name: 'app_message_conversation', methods: ['GET', 'POST'])]
    public function conversation(Annonce $annonce, Request $request, MessageRepository $messageRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Récupérer tous les messages pour cette annonce et cet utilisateur
        $messages = $messageRepository->findConversationMessages($annonce, $user);
        
        // Marquer les messages reçus comme lus
        foreach ($messages as $message) {
            if ($message->getReceiver() === $user && !$message->isRead()) {
                $message->setIsRead(true);
            }
        }
        $entityManager->flush();
        
        // Créer un nouveau message
        $newMessage = new Message();
        $newMessage->setSender($user);
        $newMessage->setAnnonce($annonce);
        
        // Déterminer le destinataire
        $receiver = $annonce->getUser() !== $user ? $annonce->getUser() : null;
        if (!$receiver && !empty($messages)) {
            // Si l'utilisateur est le propriétaire, trouver l'autre participant
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
        
        // Vérifier que l'utilisateur n'est pas le propriétaire de l'annonce
        if ($annonce->getUser() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas vous contacter vous-même !');
            return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
        }
        
        // Vérifier que l'annonce est publiée
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
}