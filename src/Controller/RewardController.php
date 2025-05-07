<?php
// src/Controller/RewardController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MysteryReward;

class RewardController extends AbstractController
{
    #[Route('/apply-reward/{id}', name: 'apply_reward', methods: ['POST'])]
    public function applyReward(MysteryReward $reward, EntityManagerInterface $em): Response
    {
        // Vérifiez que l'utilisateur est bien le propriétaire
        if ($reward->getUser() !== $this->getUser()) {
            return $this->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Logique pour appliquer la récompense
        // Cela dépend de votre système de réduction
        // Par exemple, vous pourriez créer un coupon de réduction
        
        // Marquez la récompense comme utilisée
        $reward->setUsedAt(new \DateTime());
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Récompense appliquée avec succès',
            'discountCode' => 'ECO' . mt_rand(1000, 9999) // Génère un code aléatoire
        ]);
    }
}