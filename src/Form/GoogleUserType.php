<?php

// src/Form/GoogleUserType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class GoogleUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('CIN', null, [
                'label' => 'CIN/National ID',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8, 'max' => 8]),
                    new Regex('/^[0-9]{8}$/')
                ]
            ])
            ->add('Phone', TelType::class)
            ->add('Address')
            ->add('birthday', null, [
                'widget' => 'single_text',
                'label' => 'Date of Birth'
            ])
            ->add('bio', null, [
                'label' => 'Biography',
                'required' => false
            ])
            ->add('Password', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8, 'max' => 32]),
                    new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/')
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'disabled_fields' => ['Name', 'Email']
        ]);
    }
}