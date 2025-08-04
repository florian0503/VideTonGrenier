<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AnnonceCrudController;
use App\Controller\Admin\AnnonceReportCrudController;
use App\Controller\Admin\ConversationReportCrudController;
use App\Controller\Admin\PendingAnnonceCrudController;
use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Entity\Report;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        
        return $this->redirect($adminUrlGenerator
            ->setController(AnnonceCrudController::class)
            ->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('📦 VideTonGrenier - Administration')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::section('Modération');
        yield MenuItem::linkToCrud('⏳ Annonces en attente', 'fas fa-clock', Annonce::class, null, PendingAnnonceCrudController::class);
        yield MenuItem::linkToRoute('💬 Signalements de conversations', 'fas fa-comments', 'admin_conversation_reports');
        yield MenuItem::linkToRoute('📋 Signalements d\'annonces', 'fas fa-flag', 'admin_annonce_reports');
        
        yield MenuItem::section('Gestion du contenu');
        yield MenuItem::linkToUrl('📋 Toutes les annonces', 'fas fa-list', '/admin/annonce');
        yield MenuItem::linkToCrud('🏷️ Catégories', 'fas fa-tags', Categorie::class);
        
        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('👥 Utilisateurs', 'fas fa-users', User::class);
        
        yield MenuItem::section('Retour au site');
        yield MenuItem::linkToRoute('🏠 Voir le site', 'fa fa-eye', 'app_home');
    }

    #[Route('/admin/signalements/conversations', name: 'admin_conversation_reports')]
    public function conversationReports(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        
        return $this->redirect($adminUrlGenerator
            ->setController(ConversationReportCrudController::class)
            ->setAction('index')
            ->generateUrl());
    }

    #[Route('/admin/signalements/annonces', name: 'admin_annonce_reports')]
    public function annonceReports(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        
        return $this->redirect($adminUrlGenerator
            ->setController(AnnonceReportCrudController::class)
            ->setAction('index')
            ->generateUrl());
    }

}
