<?php
namespace App\Controller\Admin;

use App\Entity\Trips;
use App\Form\TripsType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Actions, Action};
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->linkToCrudAction('customEdit')
                    ->setLabel(' Éditer')
                    ->setIcon('fa fa-edit')
                    ->addCssClass('btn btn-primary me-2');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel(' Détails')
                    ->setIcon('fa fa-eye')
                    ->addCssClass('btn btn-info me-2');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setLabel(' Supprimer')
                    ->setIcon('fa fa-trash')
                    ->addCssClass('btn btn-danger');
            });
    }

    public function customEdit(AdminContext $context, Request $request): Response
    {
        /** @var Trips $trip */
        $trip = $context->getEntity()->getInstance();
        
        $form = $this->createForm(TripsType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('trips_images_directory'),
                        $newFilename
                    );
                    $trip->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Trajet mis à jour avec succès.');

            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl());
        }

        return $this->render('admin/trips/custom_edit.html.twig', [
            'form' => $form->createView(),
            'entity' => $trip,
            'returnUrl' => $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
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