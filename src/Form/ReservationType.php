<?php
// src/Form/ReservationsType.php

namespace App\Form;

use App\Entity\Reservations;
use App\Entity\Trips;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    IntegerType,
    ChoiceType,
    DateTimeType
};
use Symfony\Component\Validator\Constraints\{
    NotBlank,
    Positive,
    LessThanOrEqual
};

class ReservationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seatNumber', IntegerType::class, [
                'label'       => 'Nombre de sièges',
                'attr'        => ['min' => 1, 'max' => 10],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un nombre de sièges']),
                    new Positive(['message' => 'Le nombre de sièges doit être positif']),
                    new LessThanOrEqual([
                        'value'   => 10,
                        'message' => 'Maximum {{ compared_value }} sièges'
                    ]),
                ],
            ])
            ->add('trip', EntityType::class, [
                'class'        => Trips::class,
                'choice_label' => fn(Trips $t) => sprintf(
                    '%s → %s (%s)',
                    $t->getDeparture(),
                    $t->getDestination(),
                    $t->getDepartureTime()->format('d/m/Y H:i')
                ),
                'label'       => 'Trajet',
                'placeholder' => 'Sélectionnez un trajet',
                'constraints' => [
                    new NotBlank(['message' => 'Choisissez un trajet']),
                ],
            ])
            ->add('seatType', ChoiceType::class, [
                'label'   => 'Type de siège',
                'choices' => [
                    'Standard' => Reservations::SEAT_STANDARD,
                    'Premium'  => Reservations::SEAT_PREMIUM,
                    'Économique' => Reservations::SEAT_ECONOMIQUE,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => [
                    'En attente' => Reservations::STATUS_PENDING,
                    'Confirmée'  => Reservations::STATUS_CONFIRMED,
                    'Annulée'    => Reservations::STATUS_CANCELED,
                ],
            ])
            ->add('reservationTime', DateTimeType::class, [
                'widget'  => 'single_text',
                'label'   => 'Date de réservation',
                'required'=> false,
            ])
            ->add('paymentStatus', ChoiceType::class, [
                'label'   => 'Statut de paiement',
                'choices' => [
                    'En attente' => Reservations::PAYMENT_PENDING,
                    'Payé'       => Reservations::PAYMENT_PAID,
                    'Annulé'     => Reservations::PAYMENT_FAILED,
                    'Remboursé'  => Reservations::PAYMENT_REFUNDED,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservations::class,
        ]);
    }
}
