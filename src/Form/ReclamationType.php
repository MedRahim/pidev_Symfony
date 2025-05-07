<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientId', TextType::class, [
                'label' => 'Client ID',
                'attr' => [
                    'placeholder' => 'Enter your client ID'
                ]
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Description cannot be blank.']),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Description must be at least {{ limit }} characters long.'
                    ])
                ],
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Describe your issue in detail (minimum 10 characters)'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Problème d\'application' => 'Problème d\'application',
                    'Réclamation Service administratif' => 'Réclamation Service administratif',
                    'Réclamation service de transport' => 'Réclamation service de transport',
                    'Réclamation service hospitalier' => 'Réclamation service hospitalier',
                    'Réclamation service supermarché en ligne' => 'Réclamation service supermarché en ligne',
                    'Autre problème' => 'Autre problème'
                ],
                'placeholder' => 'Select a complaint type',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Please select a complaint type.'])
                ]
            ])
            ->add('photo', FileType::class, [
                'required' => false,
                'label' => 'Attachment (optional)',
                'mapped' => true,
                'data_class' => null,
                'attr' => [
                    'accept' => 'image/*,.pdf'
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Email cannot be blank.']),
                    new Email(['message' => 'Please enter a valid email address.'])
                ],
                'attr' => [
                    'placeholder' => 'your@email.com'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}