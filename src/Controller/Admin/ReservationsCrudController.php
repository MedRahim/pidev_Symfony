<?php
namespace App\Controller\Admin;

use App\Entity\Reservations;
use App\Form\ReservationsType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Actions, Action};
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    DateTimeField,
    ChoiceField,
    IntegerField,
    MoneyField
};
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReservationsCrudController extends AbstractCrudController
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
        return Reservations::class;
    }

    public function configureActions(Actions $actions): Actions
{
    return $actions
        ->add(Crud::PAGE_INDEX, Action::new('export', 'Exporter CSV')
            ->linkToRoute('export_reservations_csv')
            ->setIcon('fa fa-download')
            ->addCssClass('btn btn-primary mb-2')) // Ajout de marge basse
        
        ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            return $action
                ->linkToCrudAction('customEdit')
                ->setLabel(' Éditer') // Espace avant pour l'icône
                ->setIcon('fa fa-edit')
                ->addCssClass('btn btn-primary me-2'); // Marge à droite
        })
        
        ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
            return $action
                ->setLabel(' Détails')
                ->setIcon('fa fa-eye')
                ->addCssClass('btn btn-info me-2'); // Marge à droite
        })
        
        ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
            return $action
                ->setLabel(' Supprimer') // Texte ajouté
                ->setIcon('fa fa-trash')
                ->addCssClass('btn btn-danger'); 
        });
}

    public function customEdit(AdminContext $context, Request $request): Response
    {
        $reservation = $context->getEntity()->getInstance();
        $form = $this->createForm(ReservationsType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $reservation->getReservationTime()) {
                $reservation->setReservationTime(new \DateTime());
            }

            $this->em->flush();
            $this->addFlash('success', 'Réservation mise à jour avec succès.');

            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl());
        }

        return $this->render('admin/reservations/custom_edit.html.twig', [
            'form' => $form->createView(),
            'entity' => $reservation,
            'returnUrl' => $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl(),
        ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('trip', 'Trajet');
        yield DateTimeField::new('reservationTime', 'Date réservation')
            ->setFormat('dd/MM/Y HH:mm');
        yield ChoiceField::new('seatType', 'Type de siège')
            ->setChoices([
                'Standard' => Reservations::SEAT_STANDARD,
                'Premium' => Reservations::SEAT_PREMIUM,
                'Économique' => Reservations::SEAT_ECONOMIQUE,
            ]);
        yield IntegerField::new('seatNumber', 'Nombre de sièges');
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'En attente' => Reservations::STATUS_PENDING,
                'Confirmée' => Reservations::STATUS_CONFIRMED,
                'Annulée' => Reservations::STATUS_CANCELED,
            ]);
        yield ChoiceField::new('paymentStatus', 'Paiement')
            ->setChoices([
                'En attente' => Reservations::PAYMENT_PENDING,
                'Payé' => Reservations::PAYMENT_PAID,
                'Annulé' => Reservations::PAYMENT_FAILED,
            ]);

        if (Crud::PAGE_INDEX === $pageName) {
            yield MoneyField::new('amount', 'Montant total')
                ->setCurrency('TND')
                ->formatValue(fn($v, $e) => $e->getTrip()
                    ? number_format($e->getTrip()->getPrice() * $e->getSeatNumber(), 2) . ' TND'
                    : 'N/A');
        }
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Réservations')
            ->setPaginatorPageSize(30)
            ->showEntityActionsInlined()
            ->setDefaultSort(['reservationTime' => 'DESC']);
    }
}