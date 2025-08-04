<?php

namespace App\Controller;

use App\Service\CookieConsentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cookies')]
class CookieController extends AbstractController
{
    public function __construct(
        private CookieConsentService $cookieConsentService
    ) {}

    #[Route('/preferences', name: 'cookie_preferences')]
    public function preferences(Request $request): Response
    {
        $categories = $this->cookieConsentService->getCookieCategories();
        $currentPreferences = $this->cookieConsentService->getConsentData($request);

        return $this->render('cookies/preferences.html.twig', [
            'categories' => $categories,
            'current_preferences' => $currentPreferences,
        ]);
    }

    #[Route('/accept-all', name: 'cookie_accept_all', methods: ['POST'])]
    public function acceptAll(): JsonResponse
    {
        $response = new JsonResponse(['status' => 'success']);
        $this->cookieConsentService->acceptAll($response);

        return $response;
    }

    #[Route('/reject-all', name: 'cookie_reject_all', methods: ['POST'])]
    public function rejectAll(): JsonResponse
    {
        $response = new JsonResponse(['status' => 'success']);
        $this->cookieConsentService->rejectAll($response);

        return $response;
    }

    #[Route('/save-preferences', name: 'cookie_save_preferences', methods: ['POST'])]
    public function savePreferences(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $preferences = [
            'analytics' => $data['analytics'] ?? false,
            'marketing' => $data['marketing'] ?? false,
            'functional' => $data['functional'] ?? false,
        ];

        $response = new JsonResponse(['status' => 'success']);
        $this->cookieConsentService->setConsent($response, $preferences);

        return $response;
    }

    #[Route('/policy', name: 'cookie_policy')]
    public function policy(): Response
    {
        return $this->render('cookies/policy.html.twig');
    }
}
