<?php

namespace App\Form\Wizard;

use App\Entity\Annonce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class Step3DescriptionPriceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Décrivez votre annonce en détail',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Décrivez votre article, son état, ses caractéristiques...'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire']),
                    new Length(['min' => 20, 'minMessage' => 'La description doit faire au moins 20 caractères'])
                ]
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Quel est le prix ? (optionnel)',
                'currency' => 'EUR',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '0.00'
                ],
                'constraints' => [
                    new PositiveOrZero(['message' => 'Le prix doit être positif'])
                ],
                'required' => false,
                'help' => 'Laissez vide si vous préférez ne pas indiquer de prix'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
