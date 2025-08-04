<?php

namespace App\Controller\Admin;

use App\Entity\Annonce;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AnnonceCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Annonce::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Annonce')
            ->setEntityLabelInPlural('Annonces')
            ->setSearchFields(['titre', 'description', 'user.firstName', 'user.lastName'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $approveAction = Action::new('approve', 'Approuver', 'fas fa-check')
            ->linkToCrudAction('approveAnnonce')
            ->displayIf(static function (Annonce $annonce) {
                return $annonce->isPending();
            })
            ->setCssClass('btn btn-success');

        $rejectAction = Action::new('reject', 'Refuser', 'fas fa-times')
            ->linkToCrudAction('rejectAnnonce')
            ->displayIf(static function (Annonce $annonce) {
                return $annonce->isPending();
            })
            ->setCssClass('btn btn-danger');

        return $actions
            ->add(Crud::PAGE_INDEX, $approveAction)
            ->add(Crud::PAGE_INDEX, $rejectAction)
            ->add(Crud::PAGE_DETAIL, $approveAction)
            ->add(Crud::PAGE_DETAIL, $rejectAction);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status', 'Statut')
                ->setChoices([
                    'En attente' => Annonce::STATUS_PENDING,
                    'Publiée' => Annonce::STATUS_PUBLISHED,
                    'Refusée' => Annonce::STATUS_REJECTED,
                    'Vendue' => Annonce::STATUS_SOLD,
                    'Brouillon' => Annonce::STATUS_DRAFT,
                    'Archivée' => Annonce::STATUS_ARCHIVED,
                ])
                ->canSelectMultiple(false))
            ->add('categorie', null, 'Catégorie')
            ->add('user', null, 'Utilisateur');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('titre'),
            TextareaField::new('description')->hideOnIndex(),
            MoneyField::new('prix')->setCurrency('EUR'),
            ChoiceField::new('type')->setChoices([
                'Vente' => Annonce::TYPE_SELL,
                'Achat' => Annonce::TYPE_BUY,
                'Service' => Annonce::TYPE_SERVICE,
            ]),
            ChoiceField::new('status')->setChoices([
                'Brouillon' => Annonce::STATUS_DRAFT,
                'En attente' => Annonce::STATUS_PENDING,
                'Publiée' => Annonce::STATUS_PUBLISHED,
                'Refusée' => Annonce::STATUS_REJECTED,
                'Vendue' => Annonce::STATUS_SOLD,
                'Archivée' => Annonce::STATUS_ARCHIVED,
            ])->renderAsBadges([
                Annonce::STATUS_PENDING => 'warning',
                Annonce::STATUS_PUBLISHED => 'success',
                Annonce::STATUS_REJECTED => 'danger',
                Annonce::STATUS_SOLD => 'info',
                Annonce::STATUS_DRAFT => 'secondary',
                Annonce::STATUS_ARCHIVED => 'dark',
            ]),
            AssociationField::new('categorie'),
            AssociationField::new('user'),
            TextField::new('ville')->hideOnIndex(),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('publishedAt')->hideOnForm(),
            DateTimeField::new('moderatedAt')->hideOnForm()->hideOnIndex(),
            TextareaField::new('moderationComment')->hideOnForm()->hideOnIndex(),
        ];
    }

    public function approveAnnonce(AdminContext $context): RedirectResponse
    {
        $annonce = $context->getEntity()->getInstance();
        
        if ($annonce instanceof Annonce && $annonce->isPending()) {
            $annonce->approve();
            $this->entityManager->flush();
            
            $this->addFlash('success', sprintf('L\'annonce "%s" a été approuvée avec succès.', $annonce->getTitre()));
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function rejectAnnonce(AdminContext $context): RedirectResponse
    {
        $annonce = $context->getEntity()->getInstance();
        
        if ($annonce instanceof Annonce && $annonce->isPending()) {
            $annonce->reject('Annonce refusée par l\'administrateur');
            $this->entityManager->flush();
            
            $this->addFlash('warning', sprintf('L\'annonce "%s" a été refusée.', $annonce->getTitre()));
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

}
