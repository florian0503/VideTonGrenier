<?php

namespace App\Form\Wizard;

use App\Entity\Annonce;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class Step2CategoryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', EntityType::class, [
                'label' => 'Dans quelle catégorie se situe votre annonce ?',
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('c.nom', 'ASC');
                },
                'attr' => ['class' => 'form-select form-select-lg'],
                'placeholder' => 'Choisissez une catégorie',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une catégorie'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
