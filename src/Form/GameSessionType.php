<?php

namespace App\Form;

use App\Entity\GameSession;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class GameSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var GameSession|null $gameSession */
        $gameSession = $options['data'] ?? null;
        $currentCount = $gameSession ? count($gameSession->getParticipants()) : 1;

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Ex: Quelques games de League of Legends'],
                'constraints' => [
                    new NotBlank(['message' => 'La description de l’événement est obligatoire.']),
                    new Length([
                        'min' => 2,
                        'max' => 30,
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
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date de début est obligatoire.']),
                    new GreaterThan(['value' => 'today', 'message' => 'La date de début doit être ultérieure à aujourd’hui.']),
                ],
            ])
            ->add('estimatedDuration', RangeType::class, [
                'label' => 'Durée estimée',
                'attr' => [
                    'min' => 15,
                    'max' => 240,
                    'step' => 15,
                    'class' => 'w-full accent-neonBlue cursor-pointer',
                    'oninput' => "this.nextElementSibling.value = this.value + ' min'",
                ],
            ])
            ->add('maxParticipants', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => [
                    'placeholder' => 'Ex : 8',
                    'min' => $currentCount,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le nombre de places maximum.']),
                    new GreaterThanOrEqual([
                        'value' => $currentCount,
                        'message' => 'Le nombre de places doit être au moins égal au nombre de participants déjà inscrits ({{ compared_value }}).'
                    ]),
                    new LessThanOrEqual([
                        'value' => 30,
                        'message' => 'Le nombre de places ne peut pas dépasser {{ compared_value }}.'
                    ]),
                ],
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description (facultative)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Pour s\'amuser sans prise de tête',
                    'rows' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GameSession::class,
        ]);
    }
}
