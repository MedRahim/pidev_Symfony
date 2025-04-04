<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
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
            ->add('Email', EmailType::class)
            ->add('Password', PasswordType::class)
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
