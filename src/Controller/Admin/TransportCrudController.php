<?php
// src/Controller/Admin/TransportCrudController.php
namespace App\Controller\Admin;

use App\Entity\Transport;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TransportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Transport::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // Configurez vos champs ici
            IdField::new('id'),
            TextField::new('name'),
            // ... autres champs
        ];
    }
}