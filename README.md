# 🏙️ MdinTech


**MdinTech** est une application Smart City pensée pour moderniser l'interaction entre les citoyens et les services de la ville. Elle permet aux utilisateurs d'accéder de manière numérique à divers services essentiels comme la santé, le transport, le commerce, les réclamations et les actualités à travers une plateforme centralisée et intuitive.

---

## 🧭 Table des matières

- [🎯 Fonctionnalités](#-fonctionnalités)
- [✅ Roadmap](#-roadmap)
- [💻 Prérequis](#-prérequis)
- [🚀 Installation](#-installation)
- [☕ Utilisation](#-utilisation)
- [🔗 Technologies et APIs](#-technologies-et-apis)
- [👥 Membres du Projet](#-membres-du-projet)
- [😄 Contribution](#-contribution)
- [📄 Licence](#-licence)

---

## 🎯 Fonctionnalités

- Authentification sécurisée avec vérification par mail
- Gestion intelligente des utilisateurs et profils
- Module Hôpital : prise de rendez-vous, agenda médical, mailing, uploader de fichiers
- Module Transport : météo en temps réel, carte interactive, gestion de trajets
- Module Market : paiement via Stripe, factures PDF (Dompdf)
- Blog : détection automatique des propos inappropriés
- Réclamations : enregistrement, suivi, traitement
- Fonctionnalités globales : 
  - Recherche avancée
  - Filtres dynamiques
  - Statistiques visuelles
  - Notifications en temps réel (Mercure)
  - Interfaces modernes avec Stimulus.js

---

## ✅ Roadmap

- [x] Authentification avec vérification par e-mail
- [x] Paiement sécurisé via Stripe
- [x] Intégration météo et carte pour le transport
- [x] Génération de documents PDF (factures, ordonnances)
- [x] Upload de fichiers médicaux
- [x] Notifications en temps réel avec Mercure
- [x] Pagination sur tous les modules
- [x] Système de points de fidélité pour le transport
- [ ] Chat citoyen / administration (à venir)
- [ ] Système de points de fidélité pour le market (à venir)

---

## 💻 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- [PHP ≥ 8.1](https://www.php.net/downloads)
- [Symfony CLI](https://symfony.com/download)
- [Composer](https://getcomposer.org/)
- [MySQL ≥ 5.7](https://dev.mysql.com/downloads/mysql/)
- [Node.js ≥ 16](https://nodejs.org/)

---

## 🚀 Installation

```bash
git clone https://github.com/MdinTech/smartcity-app.git
cd mdintech
composer install
npm install
cp .env .env.local
# Ajoutez vos clés API : STRIPE, Mercure, etc.
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
```

---

## ☕ Utilisation

```bash
php bin/console messenger:consume async -vv
```

Utilisation typique :

- S'inscrire et valider son compte via e-mail
- Réserver un transport via carte
- Acheter un produit et payer avec Stripe
- Lire des articles de blog filtrés
- Faire une réclamation ou consulter son historique médical

---

## 🔗 APIs utilisées

| Technologie / Bundle                 | Description                                                                                              |  Liens
| ----------------------                               | ------------------------------------------------ ------------- |----------------------
| DomPDF                                                   | Génération de PDF pour factures & documents                   |(https://github.com/dompdf/dompdf)   
| Stripe API                                        | Paiement en ligne sécurisé                                                             |(https://stripe.com/docs/api)       
| Mercure                                              | Notifications en temps réel                                                           |(https://mercure.rocks/)   
| EasyAdminBundle                          | Interface d'administration                                                        |(https://symfony.com/doc/current/bundles/EasyAdminBundle/index.html) 
| Stimulus.js                                     | Front-end dynamique et interactif                                           |(https://stimulus.hotwired.dev/)  
| API météo                                          | Affichage de la météo en temps réel                                          |(https://openweathermap.org/api)      
| UploaderBundle                            | Upload sécurisé de fichiers                                                         |(https://github.com/dustin10/VichUploaderBundle) 
| Mailing Symfony Mailer     | Envoi d'e-mails (validation compte, infos)                   |(https://symfony.com/doc/current/mailer.html)    
| Détection des propos                | Filtrage automatique de mots inappropriés (blog)  |https://www.npmjs.com/package/bad-words) 
| HuggingFace API                                                         | Analyse de sentiment et classification des réclamations    |(https://huggingface.co/docs/api-inference/index)
| KnpSnappyBundle                            | Génération de PDF à partir de HTML via wkhtmltopdf       |(https://github.com/KnpLabs/KnpSnappyBundle)  

---

## 👥 Membres du Projet
| Nom                     | Modules & Contributions                                                       |
| -----------            | ----------------------------------------------------------------------------- |
| **Tasnim**  | Module Market : Stripe API, génération PDF (Dompdf), Mercure, pagination      |
| **Mariem**  | Module Transport : carte interactive, météo, EasyAdmin, Stimulus, pagination  |
| **Ines**    | Module Hôpital : calendrier médical, mailing, upload de documents, pagination |
| **Rahim**   | Module Blog : API de détection automatique des gros mots                      |
| **Amine**   | Module Utilisateur : Google OAuth2, bundles de sécurité, 2FA, mot de passe oublié, vérification e-mail          |
| **Mohamed** | Module Réclamations : Soumission et traitement (Attribution de priorité via HuggingFace BART-MNLI), Filtrage, Statistiques et génération de PDF (KnpSnappy),        |
| **Tous**    | Recherche, filtrage et statistiques dans le front & back                      |

---

## 😄 Contribution
1. Fork le projet
2. git checkout -b feature/ma-feature
3. git commit -m "Ajout de feature"
4. git push origin feature/ma-feature
5. Ouvre une Pull Request

📘 [Guide Contribution GitHub](https://docs.github.com/fr/get-started/quickstart/contributing-to-projects)

---

## 📄 Licence

Projet académique réalisé dans le cadre des études à ESPRIT (École Supérieure Privée d'Ingénierie et de Technologies), promotion 2025.  
Usage strictement pédagogique.  
Voir [LICENSE.md](LICENSE.md) pour plus d'informations