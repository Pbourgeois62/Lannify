<?php
namespace App\Form;

use App\Entity\ParticipantGame;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantGameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('owned', CheckboxType::class, [
                'required' => false,
                'label' => 'Je possède',
            ])
            ->add('interested', CheckboxType::class, [
                'required' => false,
                'label' => 'Je veux jouer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParticipantGame::class,
        ]);
    }
}
