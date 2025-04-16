<?php

namespace App\Form;

use App\Entity\TransportTypes;
use App\Entity\Trips;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TripsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('departure')
            ->add('destination')
            ->add('departureTime', null, [
                'widget' => 'single_text',
            ])
            ->add('arrivalTime', null, [
                'widget' => 'single_text',
            ])
            ->add('price')
            ->add('transportName')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('distance')
            ->add('capacity')
            ->add('transport', EntityType::class, [
                'class' => TransportTypes::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trips::class,
        ]);
    }
}
