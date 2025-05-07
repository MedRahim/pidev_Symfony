<?php

namespace App\Form\Ines;

use App\Entity\Ines\Medecin;
use App\Entity\Ines\ServiceHospitalier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MedecinBackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomM', TextType::class, ['label' => 'Nom'])
            ->add('prenomM', TextType::class, ['label' => 'Prénom'])
            ->add('specialite', TextType::class, ['label' => 'Spécialité'])
            ->add('contact', NumberType::class, ['label' => 'Contact'])
            ->add('service', EntityType::class, [
                'class' => ServiceHospitalier::class,
                'choice_label' => 'nomService',
                'label' => 'Service hospitalier'
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Photo du médecin',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Medecin::class,
        ]);
    }
}

