<?php
// src/Form/FrontReservationType.php

// src/Form/FrontReservationType.php
namespace App\Form;

use App\Entity\Reservations;
use App\Entity\Trips;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FrontReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On injecte l'entité Trip avant le handleRequest()
        if ($options['trip'] instanceof Trips) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
                $reservation = $event->getForm()->getData();
                if ($reservation) {
                    $reservation->setTrip($options['trip']);
                }
            });
        }

        $builder
            // Champ caché pour récupérer la liste des numéros de siège (ex: "1,4,6")
            ->add('seatNumber', HiddenType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Vous devez sélectionner au moins un siège.'),
                ],
            ])
            // Champ caché pour récupérer le type de chaque siège (ex: "Standard,Premium,Standard")
            ->add('seatType', HiddenType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Le type de siège est requis.'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Reservations::class,
            'trip'            => null,
            'csrf_protection' => true,
        ]);

        $resolver->setAllowedTypes('trip', ['null', Trips::class]);
    }
}
