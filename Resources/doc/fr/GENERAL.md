# Principes généraux

## Introduction

Un certain nombre de concepts simple ont été mis en oeuvre dans le cadre de cette usine à site.
Quelques explications sont nécessaire afin de maitriser à la fois l'organisation du code ainsi que la structuration des données.

## Acteurs

### Concepteurs

Les concepteurs ont en charge l'initialisation de l'usine à sites et ont les capacités de gérer les modèles de site, tant au niveau des aspects contenu que fonctionnel :

* un concepteur est administrateur Back-Office eZ Platform afin de pouvoir définir les modèles de site
* un concepteur doit pouvoir concevoir les actions/vues des différents modèles qu'il initialise

### Contributeur

Les contributeurs ont en charge l'initialisation/configuration/contribution de leurs sites.
Ils n'ont aucunement la main sur les modèles de site ou sur les aspects fonctionnels de leurs sites basés sur ces modèles.

Ils sont néammoins en charge d'émettre leurs besoins pour que les concepteurs puissent :

* mettre en oeuvre un modèle de site
* customiser les fonctionnalités d'un site en surchargeant les principes fonctionnels du modèle sur lequel le site est basé.

## Concepts

### Client (Customer)

Un client a accès à une partie spécifique de l'arborescence de contenu et média de la solution eZ Platform.
Un client est soit un utilisateur créateur ou éditeur des sites le concernant. 

Chacun de ces type d'utilisateur possèdera des rôles spécifique par client et type d'utilisateur.
Des arborescences utilisateur spécifiques pour chaque client seront également à disposition afin de pouvoir ajouter/retirer des utilisateurs créateur et/ou éditeur.

#### Créateur de site

Un client créateur de site est l'utilisateur pouvant :

* créer un nouveau site à partir d'un modèle de site existant
* configurer un site de son espace client
* contribuer le contenu d'un site de son espace client
* activer/désactiver l'accès à un site

Il aura accès à une interface spécifique lui permettant de créer/configurer/activer/désactiver un site

#### Editeur de site

Un client éditeur de site est l'utilisateur pouvant uniquement :

* contribuer le contenu d'un site de son espace client

Il n'aura accès à aucune interface spécifique, il pourra simplement contribuer aux contenus des arborescences des sites auxquels il aura accès.

### Modèle de site

Un modèle de site est une partie de l'arborescence de contenu et média spécifique au modèle.
La racine du modèle est positionnée directement sous une racine de modèles de sites : Models
La structure de contenu du modèle définit l'organisation complète d'un site représenté par ce modèle avec comme contenu quelques exemples "Lorem ipsum" couvrant l'ensemble des possibilités offertes par ce modèle pour construire un site.

La définition des modèles de site est à la charge des concepteurs.

A ce modèle de site, les concepteurs doivent mettre en oeuvre, au travers d'un bundle spécifique au modèle, les vues, controlleurs spécifique ... permettant de fournir un "thême" d'affichage et les fonctionnalités par défaut d'un site basé sur ce modèle de site.

### Site

Un site est une partie de l'arborescence de contenu et média spécifique au site.
La racine du site est positionnée directement sous une racine client, elle même positionnée directement sous la racine des clients : Customers

A l'initialisation du site, la structure contenu et média de ce site correspond à une copie exacte de la structure de contenu du modèle sélectionné pour construire le site.
L'initialisation/configuration/contribution des sites est à la charge des contributeurs (créateurs et/ou éditeurs).

A l'initialisation d'un site, un bundle est automatiquement généré.
Ce bundle de site hérite du bundle de modèle de site correspondant au modèle sélectionné pour l'initialisation de ce site.

A la charge des concepteurs, selon le besoin des contributeurs, de proposer des surcharges des différentes vues, controlleurs ... pour fournir un affichage et/ou des aspects fonctionnels différent du modèle initial.

