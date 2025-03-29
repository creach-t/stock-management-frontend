<?php

namespace App\Form;

use App\Model\Product;
use App\Service\ApiService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Type;

class ProductType extends AbstractType
{
    private $apiService;
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupération des catégories depuis l'API
        $categoriesData = $this->apiService->getCategories();
        $categories = [];
        
        if (isset($categoriesData['success']) && $categoriesData['success'] === false) {
            $categories['Erreur de chargement des catégories'] = '';
        } else {
            foreach ($categoriesData as $category) {
                $categories[$category['name']] = $category['id'];
            }
        }
        
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du produit'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom pour le produit'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description du produit',
                    'rows' => 4
                ],
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 500,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prix du produit'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un prix pour le produit'
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'Le prix doit être un nombre'
                    ]),
                    new PositiveOrZero([
                        'message' => 'Le prix ne peut pas être négatif'
                    ])
                ]
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Quantité en stock'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une quantité pour le produit'
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'La quantité doit être un nombre entier'
                    ]),
                    new PositiveOrZero([
                        'message' => 'La quantité ne peut pas être négative'
                    ])
                ]
            ])
            ->add('sku', TextType::class, [
                'label' => 'SKU',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Référence unique du produit'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un SKU pour le produit'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 20,
                        'minMessage' => 'Le SKU doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le SKU ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('categoryId', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => $categories,
                'attr' => [
                    'class' => 'form-control'
                ],
                'placeholder' => 'Sélectionnez une catégorie',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une catégorie'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}