<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class UserMeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function __invoke(UserInterface $user, SerializerInterface $serializer): JsonResponse {
        /** @var User $user */

        $data = $serializer->normalize($user, 'json', ['groups' => ['user:read']]);
        return new JsonResponse($data);
    }
}
