<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichFileType::class, [
                'label' => false,
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'constraints' => [
                    new ImageConstraint([
                        'maxSize' => '2M', // taille maximale
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'], // types autorisés
                        'mimeTypesMessage' => 'Seules les images JPG, PNG ou WEBP sont autorisées.',
                        'maxSizeMessage' => 'L’image ne peut pas dépasser 2 Mo.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
