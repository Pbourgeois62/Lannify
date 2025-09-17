<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'Rue',
                'constraints' => [
                    new NotBlank(['message' => 'La rue est obligatoire.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'La rue ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'constraints' => [
                    new NotBlank(['message' => 'La ville est obligatoire.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [
                    new NotBlank(['message' => 'Le code postal est obligatoire.']),
                    new Regex([
                        'pattern' => '/^\d{5}$/',
                        'message' => 'Le code postal doit contenir exactement 5 chiffres.'
                    ]),
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'constraints' => [
                    new NotBlank(['message' => 'Le pays est obligatoire.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le pays ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
