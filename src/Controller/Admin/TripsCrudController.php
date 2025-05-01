<?php
// src/Controller/Admin/TripsCrudController.php

namespace App\Controller\Admin;

use App\Entity\Trips;
use App\Form\TripsType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TripsCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityManagerInterface $em;

    public function __construct(AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return Trips::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $editCustom = Action::new(Action::EDIT, 'Éditer')
            ->linkToCrudAction('customEdit')
            ->setCssClass('btn btn-primary');

        return $actions
            ->add(Crud::PAGE_INDEX, $editCustom)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_NEW,   Action::SAVE_AND_RETURN)
            ->add(Crud::PAGE_NEW,   Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_EDIT,  Action::SAVE_AND_RETURN)
            ->add(Crud::PAGE_EDIT,  Action::SAVE_AND_CONTINUE)
        ;
    }

    public function customEdit(AdminContext $context, Request $request): Response
    {
        /** @var Trips $trip */
        $trip = $context->getEntity()->getInstance();

        $form = $this->createForm(TripsType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload éventuel
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('trips_images_directory'),
                        $newFilename
                    );
                    $trip->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’upload de l’image.');
                }
            }

            // On utilise l'EntityManager injecté
            $this->em->flush();
            $this->addFlash('success', 'Trajet mis à jour avec succès.');

            $returnUrl = $this->adminUrlGenerator
                              ->setController(self::class)
                              ->setAction(Action::INDEX)
                              ->generateUrl();

            return $this->redirect($returnUrl);
        }

        $returnUrl = $this->adminUrlGenerator
                          ->setController(self::class)
                          ->setAction(Action::INDEX)
                          ->generateUrl();

        return $this->render('admin/trips/custom_edit.html.twig', [
            'form'      => $form->createView(),
            'entity'    => $trip,
            'returnUrl' => $returnUrl,
        ]);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Gestion des trajets')
            ->setDefaultSort(['departureTime' => 'DESC'])
            ->setPaginatorPageSize(30)
            ->showEntityActionsInlined();
    }
}
