<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('CIN')
            ->add('Name')
            ->add('Email')
            ->add('Password')
            ->add('Role')
            ->add('Phone')
            ->add('Address')
            ->add('isActive')
            ->add('pathtopic')
            ->add('birthday', null, [
                'widget' => 'single_text',
            ])
            ->add('isVerified')
            ->add('accountCreationDate', null, [
                'widget' => 'single_text',
            ])
            ->add('lastLoginDate', null, [
                'widget' => 'single_text',
            ])
            ->add('failedLoginAttempts')
            ->add('bio')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text',
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
