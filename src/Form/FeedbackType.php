<?php

namespace App\Form;

use App\Entity\Feedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'input text-white focus:ring-2 focus:ring-neonBlue focus:border-neonBlue w-full',                    
                ],
                'label_attr' => ['class' => 'text-white'],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'input text-white focus:ring-2 focus:ring-neonBlue focus:border-neonBlue w-full',                    
                    'rows' => 5,
                ],
                'label_attr' => ['class' => 'text-white'],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => array_combine(Feedback::CATEGORIES, Feedback::CATEGORIES),
                'placeholder' => 'Sélectionnez une catégorie',
                'attr' => [
                    'class' => 'input text-white focus:ring-2 focus:ring-neonBlue focus:border-neonBlue w-full',
                ],
                'label_attr' => ['class' => 'text-white'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
