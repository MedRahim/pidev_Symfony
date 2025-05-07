<?php
// src/Form/TripsType.php

namespace App\Form;

use App\Entity\Trips;
use App\Entity\TransportTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    DateTimeType,
    FileType,
    IntegerType,
    NumberType,
    SubmitType,
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

class TripsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('departure', TextType::class, [
                'label'       => 'Lieu de départ',
                'attr'        => ['placeholder' => 'Ex: Paris'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le lieu de départ est obligatoire']),
                    new Assert\Length(['min'=>2,'max'=>100]),
                    new Assert\Regex([
                        'pattern' => '/^[\pL\s\-\'À-ÿ]+$/u',
                        'message' => 'Caractères non autorisés'
                    ]),
                ],
            ])
            ->add('destination', TextType::class, [
                'label'       => 'Destination',
                'attr'        => ['placeholder' => 'Ex: Lyon'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La destination est obligatoire']),
                    new Assert\Length(['min'=>2,'max'=>100]),
                    new Assert\Regex([
                        'pattern' => '/^[\pL\s\-\'À-ÿ]+$/u',
                        'message' => 'Caractères non autorisés'
                    ]),
                ],
            ])
            ->add('departureTime', DateTimeType::class, [
                'label'       => 'Heure de départ',
                'widget'      => 'single_text',
                'attr'        => ['class' => 'datetimepicker'],
                'empty_data'  => null,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L’heure de départ est obligatoire']),
                    new Assert\GreaterThan(['value'=>'now','message'=>'Doit être dans le futur']),
                ],
            ])
            ->add('arrivalTime', DateTimeType::class, [
                'label'       => 'Heure d’arrivée',
                'widget'      => 'single_text',
                'attr'        => ['class' => 'datetimepicker'],
                'empty_data'  => null,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L’heure d’arrivée est obligatoire']),
                    new Assert\GreaterThan([
                        'propertyPath'=>'parent.all[departureTime].data',
                        'message'=>'Doit être après le départ'
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label'    => 'Illustration (JPEG/PNG/GIF, max 1 Mo)',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'         => '1024k',
                        'mimeTypes'       => ['image/jpeg','image/png','image/gif'],
                        'mimeTypesMessage'=> 'Format invalide',
                    ]),
                ],
            ])
            ->add('price', NumberType::class, [
                'label'       => 'Prix (DT)',
                'scale'       => 2,
                'attr'        => ['step'=>'0.01','placeholder'=>'Ex: 29.99'],
                'constraints' => [
                    new Assert\NotBlank(['message'=>'Le prix est obligatoire']),
                    new Assert\Positive(['message'=>'Doit être positif']),
                    new Assert\LessThanOrEqual(['value'=>10000,'message'=>'Max {{ compared_value }}€']),
                ],
            ])
            ->add('capacity', IntegerType::class, [
                'label'       => 'Nombre de places',
                'attr'        => ['placeholder'=>'Ex: 50'],
                'constraints' => [
                    new Assert\NotBlank(['message'=>'La capacité est obligatoire']),
                    new Assert\Positive(['message'=>'Doit être positif']),
                    new Assert\LessThanOrEqual(['value'=>200,'message'=>'Max {{ compared_value }} places']),
                ],
            ])
            ->add('transport', EntityType::class, [
                'class'        => TransportTypes::class,
                'choice_label' => 'name',
                'label'        => 'Type de transport',
                'placeholder'  => '— Sélectionnez —',
                'query_builder'=> fn($er)=> $er->createQueryBuilder('t')->orderBy('t.name','ASC'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr'  => ['class'=>'btn btn-primary mt-3']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => Trips::class,
            'validation_groups' => ['Default'],
        ]);
    }
}
