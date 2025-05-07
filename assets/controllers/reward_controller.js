// assets/controllers/reward_controller.js
import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
    static values = {
        reward: String
    }

    connect() {
        if (this.rewardValue) {
            try {
                const rewardData = JSON.parse(this.rewardValue);
                this.showReward(rewardData);
            } catch (e) {
                console.error('Error parsing reward data', e);
            }
        }
    }

    showReward(rewardData) {
        // Déterminez le message en fonction du type de récompense
        let message, icon;
        switch(rewardData.type) {
            case 'small_discount':
                message = `Félicitations ! Vous avez atteint ${rewardData.threshold} trajets et gagné un bon de réduction de 5% !`;
                icon = '🎁';
                break;
            case 'medium_discount':
                message = `Incroyable ! ${rewardData.threshold} trajets vous ont valu une réduction de 10% !`;
                icon = '🏆';
                break;
            case 'large_discount':
                message = `Exceptionnel ! Pour ${rewardData.threshold} trajets, profitez de 15% de réduction !`;
                icon = '✨';
                break;
            case 'premium_gift':
                message = `Vous êtes un héros écologique ! ${rewardData.threshold} trajets = un cadeau premium !`;
                icon = '🌟';
                break;
            default:
                message = `Vous avez gagné une récompense pour ${rewardData.threshold} trajets !`;
                icon = '🎉';
        }

        // Créez une modal ou toast avec option pour appliquer la réduction
        const modalHtml = `
            <div class="reward-modal animate__animated animate__bounceIn">
                <div class="reward-content">
                    <div class="reward-icon">${icon}</div>
                    <h3>Récompense Mystère Débloquée !</h3>
                    <p>${message}</p>
                    <div class="d-flex gap-3 justify-content-center mt-4">
                        <button class="btn btn-outline-secondary btn-sm" data-action="reward#decline">
                            Plus tard
                        </button>
                        <button class="btn btn-primary btn-sm" data-action="reward#claim">
                            Découvrir ma récompense
                        </button>
                    </div>
                </div>
            </div>
        `;

        const modal = document.createElement('div');
        modal.innerHTML = modalHtml;
        document.body.appendChild(modal);

        // Animation de fond
        const confetti = document.createElement('div');
        confetti.className = 'reward-confetti';
        document.body.appendChild(confetti);
    }

    claim() {
        // Logique pour appliquer la récompense
        fetch('/apply-reward', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            if (response.ok) {
                showToast('Récompense appliquée avec succès !', 'success');
            } else {
                showToast('Erreur lors de l\'application de la récompense', 'error');
            }
        });

        this.closeModal();
    }

    decline() {
        this.closeModal();
    }

    closeModal() {
        const modal = document.querySelector('.reward-modal');
        const confetti = document.querySelector('.reward-confetti');
        
        if (modal) {
            modal.classList.add('animate__bounceOut');
            modal.addEventListener('animationend', () => modal.remove());
        }
        
        if (confetti) confetti.remove();
    }
}