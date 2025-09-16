<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MessageController extends AbstractController
{
    #[Route('/api/conversations/{userId}/{otherId}', name: 'get_conversation', methods: ['GET'])]
    public function getConversation(
        int $userId,
        int $otherId,
        MessageRepository $messageRepository
    ): JsonResponse {
        $messages = $messageRepository->createQueryBuilder('m')
            ->where('(m.sender = :userId AND m.receiver = :otherId) OR (m.sender = :otherId AND m.receiver = :userId)')
            ->setParameter('userId', $userId)
            ->setParameter('otherId', $otherId)
            ->orderBy('m.sendAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json($messages, 200, [], ['groups' => 'message:read']);
    }

    #[Route('/api/conversations/{userId}', methods: ['GET'])]
    public function getConversations(MessageRepository $repo, int $userId): JsonResponse
    {
        $conversations = $repo->findUserConversations($userId);

        return $this->json($conversations);
    }
}
