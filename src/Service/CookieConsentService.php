<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieConsentService
{
    private const COOKIE_NAME = 'cookie_consent';
    private const COOKIE_DURATION = 365 * 24 * 60 * 60; // 1 an

    public function hasConsent(Request $request): bool
    {
        return $request->cookies->has(self::COOKIE_NAME);
    }

    public function getConsentData(Request $request): array
    {
        $consent = $request->cookies->get(self::COOKIE_NAME);
        if (!$consent) {
            return [];
        }

        return json_decode($consent, true) ?: [];
    }

    public function isConsentGiven(Request $request, string $category): bool
    {
        $consentData = $this->getConsentData($request);
        return isset($consentData[$category]) && $consentData[$category] === true;
    }

    public function setConsent(Response $response, array $preferences): void
    {
        $consentData = [
            'necessary' => true, // Toujours accepté
            'analytics' => $preferences['analytics'] ?? false,
            'marketing' => $preferences['marketing'] ?? false,
            'functional' => $preferences['functional'] ?? false,
            'timestamp' => time(),
        ];

        $cookie = Cookie::create(
            self::COOKIE_NAME,
            json_encode($consentData),
            time() + self::COOKIE_DURATION,
            '/',
            null,
            true, // Secure en HTTPS
            true, // HttpOnly
            false,
            'Lax'
        );

        $response->headers->setCookie($cookie);
    }

    public function acceptAll(Response $response): void
    {
        $this->setConsent($response, [
            'analytics' => true,
            'marketing' => true,
            'functional' => true,
        ]);
    }

    public function rejectAll(Response $response): void
    {
        $this->setConsent($response, [
            'analytics' => false,
            'marketing' => false,
            'functional' => false,
        ]);
    }

    public function getCookieCategories(): array
    {
        return [
            'necessary' => [
                'name' => 'Cookies nécessaires',
                'description' => 'Ces cookies sont indispensables au fonctionnement du site. Ils permettent d\'utiliser les principales fonctionnalités comme la connexion, la sécurité et l\'accessibilité.',
                'required' => true,
            ],
            'functional' => [
                'name' => 'Cookies fonctionnels',
                'description' => 'Ces cookies permettent d\'améliorer les fonctionnalités et la personnalisation du site (préférences de langue, thème, etc.).',
                'required' => false,
            ],
            'analytics' => [
                'name' => 'Cookies analytiques',
                'description' => 'Ces cookies nous aident à comprendre comment les visiteurs utilisent notre site en collectant des informations anonymes.',
                'required' => false,
            ],
            'marketing' => [
                'name' => 'Cookies marketing',
                'description' => 'Ces cookies sont utilisés pour afficher des publicités pertinentes et mesurer l\'efficacité de nos campagnes publicitaires.',
                'required' => false,
            ],
        ];
    }
}
