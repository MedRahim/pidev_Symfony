<?php
// src/Form/FrontReservationType.php
namespace App\Form;

use App\Entity\Reservations;
use App\Entity\Trips;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FrontReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Hidden fields mapped to entity storage
        $builder->add('seatNumber', HiddenType::class, [
            'constraints' => [new Assert\NotBlank(message: 'Vous devez sélectionner au moins un siège.')],
        ])
        ->add('seatType', HiddenType::class, [
            'constraints' => [new Assert\NotBlank(message: 'Le type de siège est requis.')],
        ]);

        // Add selectors for edit mode
        if ($options['trip'] instanceof Trips) {
            $capacity = $options['trip']->getCapacity();
            $seatChoices = [];
            for ($i = 1; $i <= $capacity; $i++) {
                $seatChoices[$i] = (string)$i;
            }

            $builder
                ->add('seatNumbersList', ChoiceType::class, [
                    'choices'  => $seatChoices,
                    'multiple' => true,
                    'expanded' => true,
                    'mapped'   => false,
                ])
                ->add('seatTypesList', ChoiceType::class, [
                    'choices'  => [
                        'Standard'    => Reservations::SEAT_STANDARD,
                        'Premium'     => Reservations::SEAT_PREMIUM,
                        'Économique'  => Reservations::SEAT_ECONOMIQUE,
                    ],
                    'multiple' => true,
                    'expanded' => true,
                    'mapped'   => false,
                ]);

            // Use SUBMIT event to merge unmapped into entity
            $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) {
                /** @var Reservations $reservation */
                $reservation = $event->getData();
                $form = $event->getForm();

                $numbers = $form->get('seatNumbersList')->getData();
                $types   = $form->get('seatTypesList')->getData();
                if (is_array($numbers) && is_array($types)) {
                    $reservation->setSeatNumber(count($numbers));
                    $reservation->setSeatType(implode(',', $types));
                }
                $event->setData($reservation);
            });
        }
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
