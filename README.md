# ECF PHP-SYMFONY, Gael Cantet DWWM2021-1
## Informations générales
### Base de données
La base de données est composées de 7 tables:
* Une table catégorie
* Une tables compétences, chacune reliée à une catégorie
* Une table entreprise
* Une table user
* Une table expériences, chacune reliée à un user et une entreprise
* Une table document, chacun relié à un user
* Une table user_competence, faisant la relation entre un user et une compétence

### Fonctions de base
Il est possible de s'inscrire afin d'avoir accès à l'application en définissant un email, mot de passe, nom, prénom, adresse, téléphone, disponibilité et visibilité.

### Comptes de test
* Candidat: candidat@candidat.local / MDP: test
* Collaborateur: collaborateur@collaborateur.local / MDP: test
* Commercial: commercial@commercial.local / MDP: test
* Administrateur: admin@admin.local / MDP: test
* Super-admin: sadmin@sadmin.local / MDP: test

## Candidats
A l'inscription, un nouvel utilisateur se voit attribuer un ROLE_USER, faisant office de candidat.
Une fois connecté il peut:
* Avoir accès à un résumé de son profil
* Modifier/Supprimer son profil
* Ajouter/Modifier/Supprimer des compétences
* Ajouter/Modifier/Supprimer des expériences. Il peut enregistrer une entreprise en base de données pour compléter ses expériences
* Ajouter/Modifier/Supprimer des documents
* La visibilité de sa fiche est automatiquement enregistrée comme visible

## Collaborateurs
Un collaborateur possède les mêmes droits qu'un candidat à l'exception de:
* Il peut visualiser les profils candidats et collaborateurs enregistrés comme visibles
* Il ne peut s'enregistrer des expériences qu'avec les entrerprises déjà présentes en base

## Commerciaux
Un commercial ne peut pas gérer son profil mais:
* Il peut rechercher des profils par nom/disponibilité/statut (candidat ou collaborateur)/compétences OU par compétence spécifique/niveau/appétance
* Il peut visualiser et modifier les profils des candidats et collaborateurs
* Il peut changer le statut d'un candidat à collaborateur et inversement
* Il peut générer un CV nominatif

## Administrateurs
Un administrateur possède les mêmes droits qu'un commercial mais:
* Il peut ajouter/modifier/supprimer des entreprises
* Il peut ajouter/modifier/supprimer des catégories
* Il peut ajouter/modifier/supprimer des compétences
* Il peut créer un nouveau profil avec le statut de collaborateur ou de commercial
* Il peut supprimer un profil
