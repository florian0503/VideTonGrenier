<?php

namespace App\Controller\Admin;

use App\Entity\Report;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AnnonceReportCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return Report::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $queryBuilder->andWhere('entity.type = :type')
                     ->setParameter('type', Report::TYPE_ANNONCE);
        
        return $queryBuilder;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Signalement d\'annonce')
            ->setEntityLabelInPlural('Signalements d\'annonces')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'En attente' => Report::STATUS_PENDING,
                'Examiné' => Report::STATUS_REVIEWED,
                'Rejeté' => Report::STATUS_DISMISSED,
                'Action prise' => Report::STATUS_ACTION_TAKEN,
            ])
            ->renderAsBadges([
                Report::STATUS_PENDING => 'warning',
                Report::STATUS_REVIEWED => 'info',
                Report::STATUS_DISMISSED => 'secondary',
                Report::STATUS_ACTION_TAKEN => 'success',
            ]);

        yield TextField::new('reason', 'Motif')->hideOnForm();
        
        yield AssociationField::new('reporter', 'Signalé par')
            ->hideOnForm()
            ->formatValue(function ($value, $entity) {
                return $value ? $value->getFullName() . ' (' . $value->getEmail() . ')' : '';
            });

        yield AssociationField::new('reportedUser', 'Propriétaire de l\'annonce')
            ->hideOnForm()
            ->formatValue(function ($value, $entity) {
                return $value ? $value->getFullName() . ' (' . $value->getEmail() . ')' : '';
            });

        yield AssociationField::new('annonce', 'Annonce')
            ->hideOnForm()
            ->formatValue(function ($value, $entity) {
                return $value ? $value->getTitre() : '';
            });

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('reviewedAt', 'Examiné le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield AssociationField::new('reviewedBy', 'Examiné par')
            ->hideOnForm()
            ->formatValue(function ($value, $entity) {
                return $value ? $value->getFullName() : '';
            });

        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextareaField::new('description', 'Description')
                ->renderAsHtml();
            yield TextareaField::new('adminComment', 'Commentaire admin')
                ->renderAsHtml();
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewAnnonce = Action::new('viewAnnonce', 'Voir l\'annonce', 'fas fa-eye')
            ->linkToCrudAction('viewAnnonce')
            ->setCssClass('btn btn-info');

        $banUser = Action::new('banUser', 'Bannir l\'utilisateur', 'fas fa-ban')
            ->linkToCrudAction('banUser')
            ->displayIf(static function (Report $report) {
                return $report->isPending() && !$report->getReportedUser()->isBanned();
            })
            ->setCssClass('btn btn-danger');

        $unbanUser = Action::new('unbanUser', 'Débannir l\'utilisateur', 'fas fa-undo')
            ->linkToCrudAction('unbanUser')
            ->displayIf(static function (Report $report) {
                return $report->getReportedUser()->isBanned();
            })
            ->setCssClass('btn btn-success');

        $markReviewed = Action::new('markReviewed', 'Marquer comme examiné', 'fas fa-check')
            ->linkToCrudAction('markReviewed')
            ->displayIf(static function (Report $report) {
                return $report->isPending();
            })
            ->setCssClass('btn btn-info');

        $dismiss = Action::new('dismiss', 'Rejeter', 'fas fa-times')
            ->linkToCrudAction('dismiss')
            ->displayIf(static function (Report $report) {
                return $report->isPending();
            })
            ->setCssClass('btn btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $viewAnnonce)
            ->add(Crud::PAGE_INDEX, $banUser)
            ->add(Crud::PAGE_INDEX, $unbanUser)
            ->add(Crud::PAGE_INDEX, $markReviewed)
            ->add(Crud::PAGE_INDEX, $dismiss)
            ->add(Crud::PAGE_DETAIL, $viewAnnonce)
            ->add(Crud::PAGE_DETAIL, $banUser)
            ->add(Crud::PAGE_DETAIL, $unbanUser)
            ->add(Crud::PAGE_DETAIL, $markReviewed)
            ->add(Crud::PAGE_DETAIL, $dismiss)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function banUser(AdminContext $context, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $reportId = $context->getRequest()->query->get('entityId');
        
        if (!$reportId) {
            $this->addFlash('error', 'ID du signalement manquant.');
            return $this->redirectToRoute('admin');
        }
        
        /** @var Report $report */
        $report = $this->entityManager->getRepository(Report::class)->find($reportId);
        
        if (!$report) {
            $this->addFlash('error', 'Signalement introuvable.');
            return $this->redirectToRoute('admin');
        }
        
        $admin = $this->getUser();
        $report->getReportedUser()->ban($admin, 'Banni suite au signalement d\'annonce #' . $report->getId());
        $report->markAsActionTaken($admin, 'Utilisateur banni');
        $this->entityManager->flush();
        
        $this->addFlash('success', sprintf('L\'utilisateur %s a été banni avec succès.', $report->getReportedUser()->getFullName()));
        
        $url = $adminUrlGenerator
            ->setController(AnnonceReportCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
            
        return $this->redirect($url);
    }

    public function unbanUser(AdminContext $context, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $reportId = $context->getRequest()->query->get('entityId');
        
        if (!$reportId) {
            $this->addFlash('error', 'ID du signalement manquant.');
            return $this->redirectToRoute('admin');
        }
        
        /** @var Report $report */
        $report = $this->entityManager->getRepository(Report::class)->find($reportId);
        
        if (!$report) {
            $this->addFlash('error', 'Signalement introuvable.');
            return $this->redirectToRoute('admin');
        }
        
        $report->getReportedUser()->unban();
        $this->entityManager->flush();
        
        $this->addFlash('success', sprintf('L\'utilisateur %s a été débanni avec succès.', $report->getReportedUser()->getFullName()));
        
        $url = $adminUrlGenerator
            ->setController(AnnonceReportCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
            
        return $this->redirect($url);
    }

    public function markReviewed(AdminContext $context, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $reportId = $context->getRequest()->query->get('entityId');
        
        if (!$reportId) {
            $this->addFlash('error', 'ID du signalement manquant.');
            return $this->redirectToRoute('admin');
        }
        
        /** @var Report $report */
        $report = $this->entityManager->getRepository(Report::class)->find($reportId);
        
        if (!$report) {
            $this->addFlash('error', 'Signalement introuvable.');
            return $this->redirectToRoute('admin');
        }
        
        $admin = $this->getUser();
        $report->markAsReviewed($admin);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Le signalement a été marqué comme examiné.');
        
        $url = $adminUrlGenerator
            ->setController(AnnonceReportCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
            
        return $this->redirect($url);
    }

    public function dismiss(AdminContext $context, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $reportId = $context->getRequest()->query->get('entityId');
        
        if (!$reportId) {
            $this->addFlash('error', 'ID du signalement manquant.');
            return $this->redirectToRoute('admin');
        }
        
        /** @var Report $report */
        $report = $this->entityManager->getRepository(Report::class)->find($reportId);
        
        if (!$report) {
            $this->addFlash('error', 'Signalement introuvable.');
            return $this->redirectToRoute('admin');
        }
        
        $admin = $this->getUser();
        $report->dismiss($admin, 'Signalement rejeté après examen');
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Le signalement a été rejeté.');
        
        $url = $adminUrlGenerator
            ->setController(AnnonceReportCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
            
        return $this->redirect($url);
    }

    public function viewAnnonce(AdminContext $context): Response
    {
        $reportId = $context->getRequest()->query->get('entityId');
        
        if (!$reportId) {
            $this->addFlash('error', 'ID du signalement manquant.');
            return $this->redirectToRoute('admin');
        }
        
        /** @var Report $report */
        $report = $this->entityManager->getRepository(Report::class)->find($reportId);
        
        if (!$report) {
            $this->addFlash('error', 'Signalement introuvable.');
            return $this->redirectToRoute('admin');
        }
        
        if (!$report->getAnnonce()) {
            $this->addFlash('error', 'Aucune annonce associée à ce signalement.');
            return $this->redirectToRoute('admin');
        }
        
        return $this->render('admin/report/annonce.html.twig', [
            'report' => $report,
            'annonce' => $report->getAnnonce(),
        ]);
    }
}