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

    public function findUserConversations(int $userId): array
{
    $qb = $this->createQueryBuilder('m')
        ->select('IDENTITY(m.sender) as senderId, IDENTITY(m.receiver) as receiverId, MAX(m.sendAt) as lastMessageDate')
        ->where('m.sender = :userId OR m.receiver = :userId')
        ->setParameter('userId', $userId)
        ->groupBy('senderId, receiverId')
        ->orderBy('lastMessageDate', 'DESC');

    return $qb->getQuery()->getArrayResult();
}

//    /**
//     * @return Message[] Returns an array of Message objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Message
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
