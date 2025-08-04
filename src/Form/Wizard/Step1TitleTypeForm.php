<?php

namespace App\Form\Wizard;

use App\Entity\Annonce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class Step1TitleTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Quel est le titre de votre annonce ?',
                'attr' => [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Ex: iPhone 13 en excellent état, Vélo électrique, Cours de guitare...'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire']),
                    new Length(['min' => 5, 'max' => 255, 'minMessage' => 'Le titre doit faire au moins 5 caractères'])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Que souhaitez-vous faire ?',
                'choices' => [
                    'Je vends quelque chose' => Annonce::TYPE_SELL,
                    'Je cherche à acheter' => Annonce::TYPE_BUY,
                    'Je propose un service' => Annonce::TYPE_SERVICE,
                ],
                'attr' => ['class' => 'form-select form-select-lg'],
                'expanded' => true,
                'multiple' => false,
                'data' => Annonce::TYPE_SELL // Valeur par défaut
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
