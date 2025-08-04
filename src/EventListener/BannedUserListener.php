<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsEventListener(event: 'kernel.controller')]
class BannedUserListener
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RouterInterface $router
    ) {
    }

    public function __invoke(ControllerEvent $event): void
    {
        $token = $this->tokenStorage->getToken();
        
        if (!$token instanceof TokenInterface) {
            return;
        }

        $user = $token->getUser();
        
        if (!$user instanceof User) {
            return;
        }

        // Si l'utilisateur est banni
        if ($user->isBanned()) {
            $request = $event->getRequest();
            $route = $request->attributes->get('_route');
            
            // Ne pas rediriger si on est déjà sur la page de ban ou de logout
            if ($route === 'app_banned' || $route === 'app_logout') {
                return;
            }
            
            // Rediriger vers une page d'information sur le bannissement
            $response = new RedirectResponse($this->router->generate('app_banned'));
            $event->setController(function() use ($response) {
                return $response;
            });
        }
    }
}