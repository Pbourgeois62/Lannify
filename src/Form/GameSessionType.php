<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\GameSession;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class GameSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('title', TextType::class, [
            //     'label' => 'Titre du groupe multi',
            //     'attr' => ['placeholder' => 'RDV des geekos'],
            //     'constraints' => [
            //         new NotBlank(['message' => 'Le nom de l’événement est obligatoire.']),
            //         new Length([
            //             'min' => 3,
            //             'max' => 255,
            //             'minMessage' => 'Le nom doit faire au moins {{ limit }} caractères.',
            //             'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'
            //         ]),
            //     ],
            // ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'RDV des geekos'],
                'constraints' => [
                    new NotBlank(['message' => 'La description de l’événement est obligatoire.']),
                    new Length([
                        'min' => 10,
                        'max' => 255,
                        'minMessage' => 'Le nom doit faire au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('isPrivate', CheckboxType::class, [
                'label' => 'Événement privé ?',
                'required' => false,
                'label_attr' => ['class' => 'ml-2 text-sm font-medium text-gray-300'],
                'attr' => ['class' => 'rounded border-gray-600 text-neonBlue focus:ring-neonBlue'],
                'help' => 'Cochez cette case si vous souhaitez que la LAN soit privée.',
            ])
            // ->add('game', EntityType::class, [
            //     'class' => Game::class,
            //     'choice_label' => 'label',
            //     'label' => 'Jeu',
            //     'placeholder' => 'Choisir un jeu...',
            // ])
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
            
            // ->add('title', TextType::class, [
            //     'label' => 'Jeu',
            //     'attr' => [
            //         'class' => 'input',
            //         'placeholder' => 'Rechercher un jeu...',
            //         'data-game-autocomplete-target' => 'input'
            //     ],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GameSession::class,
        ]);
    }
}
