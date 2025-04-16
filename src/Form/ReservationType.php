<?php
// src/Form/ReservationsType.php

namespace App\Form;

use App\Entity\Reservations;
use App\Entity\Trips;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class ReservationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seatNumber', IntegerType::class, [
                'label'       => 'Nombre de sièges',
                'attr'        => ['class' => 'form-control', 'min' => 1, 'max' => 10],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un nombre de sièges']),
                    new Positive(['message'   => 'Le nombre de sièges doit être positif']),
                    new LessThanOrEqual([
                        'value'   => 10,
                        'message' => 'Maximum {{ compared_value }} sièges'
                    ]),
                ],
            ])
            ->add('trip', EntityType::class, [
                'class'        => Trips::class,
                'choice_label' => function(Trips $t) {
                    return sprintf(
                        '%s → %s (%s)',
                        $t->getDeparture(),
                        $t->getDestination(),
                        $t->getDepartureTime()->format('d/m/Y H:i')
                    );
                },
                'label'       => 'Trajet',
                'placeholder' => 'Sélectionnez un trajet',
                'attr'        => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'Choisissez un trajet']),
                ],
            ])
            ->add('seatType', ChoiceType::class, [
                'label'   => 'Type de siège',
                'choices' => ['Standard' => 'Standard', 'Premium' => 'Premium'],
                'attr'    => ['class' => 'form-select'],
            ])
            ->add('status', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => [
                    'En attente' => 'Pending',
                    'Confirmée'  => 'Confirmed',
                    'Annulée'    => 'Cancelled'
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('paymentStatus', ChoiceType::class, [
                'label'   => 'Statut de paiement',
                'choices' => [
                    'En attente' => 'Pending',
                    'Payé'       => 'Paid',
                    'Annulé'     => 'Cancelled',
                    'Remboursé'  => 'Refunded'
                ],
                'attr' => ['class' => 'form-select'],
            ])
            // On retire reservationTime : il est fixé en controller
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservations::class,
        ]);
    }
}
