<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventUserChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('userchoice', ChoiceType::class, [
            'choices' => $options['users'],
            'choice_label' => fn($user) =>  $user->getProfile()?->getNickName() ?: $user->getEmail(),
            'choice_value' => 'id',
            'label' => false,          
            'placeholder' => 'Choisir un utilisateur...',            
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
        $resolver->setRequired('users');
    }
}
