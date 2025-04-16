<?php

namespace App\Form;

use App\Entity\TransportTypes;
use App\Entity\Trips;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TripsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('departure', TextType::class, [
                'label' => 'Lieu de départ',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Paris'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le lieu de départ est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le lieu de départ doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le lieu de départ ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-\']+$/u',
                        'message' => 'Le lieu ne doit contenir que des lettres, espaces et tirets'
                    ])
                ]
            ])
            ->add('destination', TextType::class, [
                'label' => 'Destination',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Lyon'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La destination est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'La destination doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'La destination ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-\']+$/u',
                        'message' => 'La destination ne doit contenir que des lettres, espaces et tirets'
                    ])
                ]
            ])
            // Dans App\Form\TripsType.php

->add('departureTime', DateTimeType::class, [
    'label' => 'Heure de départ',
    'widget' => 'single_text',
    'attr' => ['class' => 'form-control datetimepicker'],
    'empty_data' => null, // Ajoutez cette ligne
    'required' => true,   // S'assure que le champ est requis
    'constraints' => [
        new Assert\NotBlank(['message' => 'L\'heure de départ est obligatoire']),
        new Assert\GreaterThan([
            'value' => 'now',
            'message' => 'La date de départ doit être dans le futur'
        ])
    ]
])
->add('arrivalTime', DateTimeType::class, [
    'label' => 'Heure d\'arrivée',
    'widget' => 'single_text',
    'attr' => ['class' => 'form-control datetimepicker'],
    'empty_data' => null, // Ajoutez cette ligne
    'required' => true,   // S'assure que le champ est requis
    'constraints' => [
        new Assert\NotBlank(['message' => 'L\'heure d\'arrivée est obligatoire']),
        new Assert\GreaterThan([
            'propertyPath' => 'parent.all[departureTime].data',
            'message' => 'L\'heure d\'arrivée doit être après l\'heure de départ'
        ])
    ]
])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'placeholder' => 'Ex: 29.99'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix est obligatoire']),
                    new Assert\Positive(['message' => 'Le prix doit être positif']),
                    new Assert\LessThanOrEqual([
                        'value' => 10000,
                        'message' => 'Le prix ne peut pas dépasser {{ compared_value }}€'
                    ])
                ]
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 50'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La capacité est obligatoire']),
                    new Assert\Positive(['message' => 'La capacité doit être positive']),
                    new Assert\LessThanOrEqual([
                        'value' => 200,
                        'message' => 'La capacité ne peut pas dépasser {{ compared_value }} places'
                    ])
                ]
            ])
            ->add('transport', EntityType::class, [
                'class' => TransportTypes::class,
                'choice_label' => 'name',
                'label' => 'Type de transport',
                'attr' => ['class' => 'form-control']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    { 
        $resolver->setDefaults([
            'data_class' => Trips::class,
            'validation_groups' => ['Default']
        ]);
    }
}