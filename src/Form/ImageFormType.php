<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Image;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Imagen',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg','image/png'],
                        'mimeTypesMessage' => 'Por favor seleccione una imagen válida',
                    ])
                ],
                'attr' => ['class'=>'file-input']
            ])
            ->add('category', EntityType::class, [
                'label' => 'Categoría',
                'class' => Category::class,
                'choice_label' => 'name'
            ])
            ->add('Enviar', SubmitType::class, [
                'label' => 'Subir Imagen',
                'attr'=>['class'=>'btn btn-success']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
