<?php
namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom du jeu',
                'attr' => ['class' => 'input w-full'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du jeu est obligatoire.']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ],
            ])
            ->add('source', TextType::class, [
                'label' => 'Source / plateforme',
                'required' => false,
                'attr' => ['class' => 'input w-full'],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'La source ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ],
            ])
            ->add('free', CheckboxType::class, [
                'label' => 'Gratuit ?',
                'required' => false,
                'attr' => ['class' => 'accent-neonBlue']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
