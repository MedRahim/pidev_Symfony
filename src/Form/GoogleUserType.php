<?php

// src/Form/GoogleUserType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                'label' => 'CIN/National ID'
            ])
            ->add('Name', null, [
                'label' => 'Full Name'
            ])
            // Usually, email is set and should not be changed, so you can remove it or set it as disabled
            // ->add('Email', EmailType::class, ['disabled' => true])
            ->add('Password', PasswordType::class, [
                'label' => 'Set a Password',
                'required' => true // Force user to set a password if not set yet
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
