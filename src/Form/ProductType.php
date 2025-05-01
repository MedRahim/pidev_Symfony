<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false, // Disable client-side validation
            ])
            ->add('reference', TextType::class, [
                'required' => false,
            ])
            ->add('price', NumberType::class, [
                'required' => false,
            ])
            ->add('stockLimit', NumberType::class, [
                'required' => false,
            ])
            ->add('stock', NumberType::class, [
                'required' => false,
            ])
            ->add('imagePath', FileType::class, [
                'label' => 'Product Image',
                'mapped' => false,              // <<— not a property setter on your entity
                'required' => false,
            ])
            ->add('sold', NumberType::class, [
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Drinks' => 'Drinks',
                    'Food' => 'Food',
                    'Household products' => 'Household products',
                    'Home Appliances' => 'Home Appliances'
                ],
                'required' => true,
                'placeholder' => 'Select a category',
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
