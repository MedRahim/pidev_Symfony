<?php
// src/Form/FrontReservationType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Reservations;
use App\Entity\Trips;
use Symfony\Component\Validator\Constraints as Assert;

class FrontReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['trip'] instanceof Trips) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
                $reservation = $event->getForm()->getData();
                if ($reservation) {
                    $reservation->setTrip($options['trip']);
                }
            });
        }

        $builder
            ->add('seatNumber', IntegerType::class, [
                'label'       => 'Nombre de sièges',
                // ❌ 'html5' => false supprimé
                'attr'        => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nombre de sièges est obligatoire.']),
                    new Assert\Positive(['message' => 'Le nombre de sièges doit être un entier positif.']),
                ],
                'invalid_message' => 'Veuillez saisir un nombre de sièges valide.',
            ])
            ->add('seatType', ChoiceType::class, [
                'label'       => 'Type de siège',
                'choices'     => ['Standard' => 'Standard', 'Premium' => 'Premium'],
                'attr'        => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type de siège est obligatoire.']),
                    new Assert\Choice([
                        'choices' => ['Standard', 'Premium'],
                        'message' => 'Le type de siège doit être Standard ou Premium.',
                    ]),
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
