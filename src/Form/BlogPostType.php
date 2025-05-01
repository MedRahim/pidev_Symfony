<?php

namespace App\Form;

use App\Entity\BlogPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;

class BlogPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Technology' => 'Technology',
                    'Travel' => 'Travel',
                    'Food' => 'Food',
                    'Lifestyle' => 'Lifestyle',
                    'Fashion' => 'Fashion',
                    'Health' => 'Health',
                    'Sports' => 'Sports',
                    'Business' => 'Business'
                ],
                'required' => true,
                'placeholder' => 'Select a category',
                'attr' => [
                    'class' => 'form-control',
                    'data-validation' => 'required'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a category.'
                    ]),
                    new Choice([
                        'choices' => ['Technology', 'Travel', 'Food', 'Lifestyle', 'Fashion', 'Health', 'Sports', 'Business'],
                        'message' => 'Please select a valid category.'
                    ])
                ]
            ])
            // Use a file upload field that is not mapped directly to the entity
            ->add('imageFile', FileType::class, [
                'required' => false,
                'mapped' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
        ]);
    }
}