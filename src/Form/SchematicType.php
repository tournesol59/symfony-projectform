<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Entity\Schematic;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchematicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('updatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('imagePath', FileType::class, [
                'label' => 'Schematic Image File',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'application/pdf'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid jpeg file'
                    ]) 
                ]
            ])
            ->add('conference', EntityType::class, [
                'class' => Conference::class,
                'choice_label' => 'id',
            ])
            ->add('comment', EntityType::class, [
                'class' => Comment::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Schematic::class,
        ]);
    }
}
