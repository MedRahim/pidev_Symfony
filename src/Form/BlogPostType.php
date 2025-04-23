<?php

namespace App\Form;

use App\Entity\BlogPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BlogPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Technology' => 'technology',
                    'Health' => 'health',
                    'Lifestyle' => 'lifestyle',
                    'Education' => 'education',
                    'Travel' => 'travel',
                ],
                'placeholder' => 'Select Category',
                'required' => true,
            ])
            // Use a file upload field that is not mapped directly to the entity
            ->add('imageFile', FileType::class, [
                'label'    => 'Upload Image',
                'mapped'   => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
        ]);
    }
}