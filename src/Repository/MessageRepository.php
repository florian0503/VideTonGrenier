<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Trouve les conversations d'un utilisateur (groupées par annonce)
     */
    public function findConversationsByUser($user): array
    {
        $conversations = $this->createQueryBuilder('m')
            ->select('a.id, a.titre, MAX(m.createdAt) as lastMessageDate')
            ->join('m.annonce', 'a')
            ->where('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->groupBy('a.id, a.titre')
            ->orderBy('lastMessageDate', 'DESC')
            ->getQuery()
            ->getResult();

        // Ajouter le compte des messages non lus pour chaque conversation
        foreach ($conversations as &$conversation) {
            $unreadCount = $this->createQueryBuilder('m2')
                ->select('COUNT(m2.id)')
                ->where('m2.annonce = :annonceId')
                ->andWhere('m2.receiver = :user')
                ->andWhere('m2.isRead = false')
                ->setParameter('annonceId', $conversation['id'])
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleScalarResult();
            
            $conversation['unreadCount'] = $unreadCount;
        }

        return $conversations;
    }

    /**
     * Trouve tous les messages d'une conversation entre un utilisateur et une annonce
     */
    public function findConversationMessages($annonce, $user): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.annonce = :annonce')
            ->andWhere('m.sender = :user OR m.receiver = :user')
            ->setParameter('annonce', $annonce)
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les messages non lus d'un utilisateur
     */
    public function countUnreadMessages($user): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.receiver = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve tous les messages d'une conversation pour l'admin (sans restriction d'utilisateur)
     */
    public function findAllConversationMessages($annonce): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.annonce = :annonce')
            ->setParameter('annonce', $annonce)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
