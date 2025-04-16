<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('reference')
            ->add('price')
            ->add('stockLimit')
            ->add('stock')
            ->add('imagePath', FileType::class, [
                'required' => false, // Allow the field to be optional
                'label' => 'Choose File',
                'mapped' => false, // Prevent mapping to the entity directly
            ])
            ->add('sold')
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
