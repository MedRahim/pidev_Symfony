<?php

namespace App\Form\Ines;

use App\Entity\Ines\Rendezvous;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateRendezVous', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du rendez-vous',
            ])
            ->add('timeRendezVous', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Heure du rendez-vous',
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu du rendez-vous',
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut du rendez-vous',
                'data' => 'En attente',
            ])
            ->add('idMedecin', IntegerType::class, [
                'label' => 'ID du Médecin',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer le rendez-vous',
                'attr' => ['class' => 'btn btn-success'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rendezvous::class,
        ]);
    }
}