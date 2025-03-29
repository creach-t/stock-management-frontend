<?php

namespace App\Form;

use App\Model\StockUpdate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Type;

class StockUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productId', HiddenType::class)
            ->add('operationType', ChoiceType::class, [
                'label' => 'Opération',
                'choices' => StockUpdate::getOperationTypes(),
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une opération'
                    ])
                ]
            ])
            ->add('quantityChange', NumberType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une quantité'
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'La quantité doit être un nombre entier'
                    ]),
                    new PositiveOrZero([
                        'message' => 'La quantité doit être positive'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockUpdate::class,
        ]);
    }
}