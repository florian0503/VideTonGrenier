<?php

namespace App\Form;

use App\Entity\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'label' => 'Motif du signalement',
                'choices' => [
                    'Contenu inapproprié' => 'contenu_inapproprie',
                    'Spam ou publicité' => 'spam',
                    'Harcèlement' => 'harcelement',
                    'Arnaque ou fraude' => 'arnaque',
                    'Contenu illégal' => 'contenu_illegal',
                    'Autre' => 'autre',
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description détaillée (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez le problème en détail...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}