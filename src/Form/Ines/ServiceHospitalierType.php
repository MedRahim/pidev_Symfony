<?php

namespace App\Form\Ines;

use App\Entity\Ines\ServiceHospitalier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ServiceHospitalierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomService', TextType::class, [
                'required' => true,
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'empty_data' => '',
            ])
            ->add('nombreLitsDisponibles', IntegerType::class, [
                'required' => true,
                'empty_data' => '', // pour déclencher la validation NotNull
                'invalid_message' => 'Veuillez saisir un nombre valide.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServiceHospitalier::class,
        ]);
    }
}
