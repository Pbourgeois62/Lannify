<?php

namespace App\Form;

use App\Entity\Need;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;

class NeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', null, [
                'label' => 'Intitulé du besoin',
                'required' => true,
                'attr' => ['placeholder' => 'Switch 8 bits'],
                'constraints' => [
                    new NotBlank(['message' => 'L’intitulé du besoin est obligatoire.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'L’intitulé ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('quantity', null, [
                'label' => 'Quantité',
                'required' => true,
                'attr' => ['placeholder' => '2'],
                'constraints' => [
                    new NotBlank(['message' => 'La quantité est obligatoire.']),
                    new Positive(['message' => 'La quantité doit être un nombre positif.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Need::class,
        ]);
    }
}
