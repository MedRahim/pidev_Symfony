<?php
// src/Form/FrontReservationType.php
namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FrontReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seatNumber', IntegerType::class, [
                'label' => 'Nombre de sièges',
                'attr'  => [
                    'min'   => 1,
                    'max'   => 10,
                    'class' => 'form-control',
                ],
            ])
            ->add('seatType', ChoiceType::class, [
                'label'   => 'Type de siège',
                'choices' => [
                    'Standard' => 'Standard',
                    'Premium'  => 'Premium',
                ],
                'attr'    => ['class' => 'form-select'],
            ])
        ;
    }
}
