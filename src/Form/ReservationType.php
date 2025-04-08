<?php

// src/Form/ReservationsType.php

namespace App\Form;

use App\Entity\Reservations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class ReservationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seatNumber', IntegerType::class, [
                'label' => 'Nombre de sièges',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 10
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un nombre de sièges']),
                    new Positive(['message' => 'Le nombre de sièges doit être positif']),
                    new LessThanOrEqual([
                        'value' => 10,
                        'message' => 'Vous ne pouvez pas réserver plus de 10 sièges'
                    ])
                ]
            ])
            ->add('seatType', ChoiceType::class, [
                'label' => 'Type de siège',
                'choices' => [
                    'Standard' => 'Standard',
                    'Premium' => 'Premium'
                ],
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Choisissez un type de siège'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservations::class,
        ]);
    }

}
