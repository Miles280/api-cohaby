<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OkeeyController extends AbstractController
{
    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function test(): JsonResponse
    {
        return $this->json([
            'message' => 'Tu es bien connecté.',
        ]);
    }
}
