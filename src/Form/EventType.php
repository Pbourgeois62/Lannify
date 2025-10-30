<?php

namespace App\Form;

use App\Entity\Event;
use App\Form\ImageType;
use App\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Désignation de la LAN',
                'attr' => ['placeholder' => 'RDV des geekos'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l’événement est obligatoire.']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom doit faire au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date de début est obligatoire.']),
                    new GreaterThan(['value' => 'today', 'message' => 'La date de début doit être ultérieure à aujourd’hui.']),
                ],
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date et heure de fin',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date de fin est obligatoire.']),
                    new GreaterThan(['value' => 'today', 'message' => 'La date de fin doit être antérieure à aujourd’hui.']),
                    new Callback(function ($endDate, ExecutionContextInterface $context) {
                        $form = $context->getRoot();
                        $startDate = $form->get('startDate')->getData();
                        if ($startDate && $endDate && $endDate <= $startDate) {
                            $context->buildViolation('La date de fin doit être postérieure à la date de début.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('address', AddressType::class)
            ->add('coverImage', ImageType::class, [
                'label' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
