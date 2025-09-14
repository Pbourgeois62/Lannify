<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Need;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', null, [
                'label' => 'Intitulé du besoin',
                'attr' => ['placeholder' => 'Switch 8 bits'],
            ])
            ->add('quantity', null, [
                'label' => 'Quantité',
                'attr' => ['placeholder' => '2'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Need::class,
        ]);
    }
}
