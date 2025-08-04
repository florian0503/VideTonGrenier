<?php

namespace App\Controller\Admin;

use App\Entity\Annonce;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class PendingAnnonceCrudController extends AbstractCrudController
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
            ->setEntityLabelInSingular('Annonce en attente')
            ->setEntityLabelInPlural('Annonces en attente')
            ->setSearchFields(['titre', 'description', 'user.firstName', 'user.lastName'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // Filtrer uniquement les annonces en attente
        $queryBuilder->andWhere('entity.status = :pending')
            ->setParameter('pending', Annonce::STATUS_PENDING);
        
        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        $approveAction = Action::new('approve', 'Approuver', 'fas fa-check')
            ->linkToRoute('admin_approve_annonce', function (Annonce $annonce) {
                return ['id' => $annonce->getId()];
            })
            ->setCssClass('btn btn-success');

        $rejectAction = Action::new('reject', 'Refuser', 'fas fa-times')
            ->linkToRoute('admin_reject_annonce', function (Annonce $annonce) {
                return ['id' => $annonce->getId()];
            })
            ->setCssClass('btn btn-danger');

        return $actions
            ->add(Crud::PAGE_INDEX, $approveAction)
            ->add(Crud::PAGE_INDEX, $rejectAction)
            ->add(Crud::PAGE_DETAIL, $approveAction)
            ->add(Crud::PAGE_DETAIL, $rejectAction);
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
            AssociationField::new('categorie'),
            AssociationField::new('user'),
            TextField::new('ville')->hideOnIndex(),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }

    #[Route('/admin/annonce/{id}/approve', name: 'admin_approve_annonce', methods: ['GET'])]
    public function approveAnnonce(int $id): RedirectResponse
    {
        $annonce = $this->entityManager->getRepository(Annonce::class)->find($id);
        
        if ($annonce && $annonce->isPending()) {
            $annonce->approve();
            $this->entityManager->flush();
            
            $this->addFlash('success', sprintf('L\'annonce "%s" a été approuvée avec succès.', $annonce->getTitre()));
        } else {
            $this->addFlash('error', 'Annonce introuvable ou déjà traitée.');
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    #[Route('/admin/annonce/{id}/reject', name: 'admin_reject_annonce', methods: ['GET'])]
    public function rejectAnnonce(int $id): RedirectResponse
    {
        $annonce = $this->entityManager->getRepository(Annonce::class)->find($id);
        
        if ($annonce && $annonce->isPending()) {
            $annonce->reject('Annonce refusée par l\'administrateur');
            $this->entityManager->flush();
            
            $this->addFlash('warning', sprintf('L\'annonce "%s" a été refusée.', $annonce->getTitre()));
        } else {
            $this->addFlash('error', 'Annonce introuvable ou déjà traitée.');
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}