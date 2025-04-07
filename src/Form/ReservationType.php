<?php

namespace App\Form;

use App\Entity\Reservations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seatNumber', null, [
                'label' => 'Nombre de sièges à réserver',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('seatType', ChoiceType::class, [
                'label' => 'Type de siège',
                'choices' => [
                    'Standard' => 'Standard',
                    'Premium' => 'Premium',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            // D'autres champs comme paymentStatus ou transportId peuvent être omis
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservations::class,
        ]);
    }
}
