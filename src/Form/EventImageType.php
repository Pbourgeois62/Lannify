<?php

namespace App\Form;

use App\Entity\EventImage;
use Symfony\Component\Form\AbstractType;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EventImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('caption', null, [
                'label' => 'Légende',
                'required' => false,
                'attr' => ['placeholder' => 'Optionnel : ajoutez une légende à votre photo'],
            ])
            ->add('imageFile', DropzoneType::class, [
                'label' => 'Ajouter une photo',
                'required' => true,                
                'attr' => ['accept' => 'image/*',
                           'placeholder' => 'Glissez-déposez un fichier ou cliquez pour parcourir',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'La photo ne doit pas dépasser {{ limit }}.',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Seules les images JPG, PNG ou WebP sont acceptées.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventImage::class,
        ]);
    }
}
