<?php
// src/Controller/Admin/TransportTypesCrudController.php

namespace App\Controller\Admin;

use App\Entity\TransportTypes;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    TextField,
    TextareaField,
    IntegerField
};

class TransportTypesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TransportTypes::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Types de transport')
            ->setSearchFields(['name'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom du transport'),
            TextareaField::new('description', 'Description')
                ->hideOnIndex(),
            IntegerField::new('capacity', 'Capacité')
                ->setHelp('Nombre maximum de passagers')
        ];
    }
}