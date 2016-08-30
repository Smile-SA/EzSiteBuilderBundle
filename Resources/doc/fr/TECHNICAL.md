# Informations techniques

## Introduction

Au moyen de l'interface ou par tâche symfony, vous pouvez demander l'installation des pré-requis de l'outil, la définition de modèles, clients et sites.
Ces actions provoquent la création de bundles, manipule les contenus eZ Platform, joue avec les politiques de sécurité ...

LEs chapitres suivant vous indique les manipulations automatiquement mises en oeuvre.

## SiteBuilder projet

### Bundle

Après initialisation de l'outil, en ligne de commande 
(edgarez:sitebuilder:install) ou par l'interface Back-Office, votre architecture
code projet mise en place dans le dossier source :
 
* initialisation d'un dossier vendeur au nom du vendeur que vous avez renseigné
* dans le dossier vendeur
  * initialisation d'un dossier Customers destiné à recevoir l'espace applicatif des clients
  * initialisation d'un dossier Models destiné à recevoir l'espace applicatif des modèles de site
  * création du bundle ProjectBundle avec définition des paramètres spécifique aux projet

![Initialize bundle](/Resources/doc/images/technical/initialize_bundle.png)

### Type de contenu

Dans le même temps, l'initialisation de l'outil provoque la création d'un
group de type de contenu et de types de contenu spécifique à l'organisation
des données projet.

![Initialize content type](/Resources/doc/images/technical/initialize_contenttype.png)

### Contenu, Media, Utilisateur

L'initialisation de l'outil crée les espaces de contenu/média/utilisateur 
spécifique au projet afin de proposer les racines de contenu pour les 
phases d'initialisation client/modèle/site suivantes.

![Initialize content](/Resources/doc/images/technical/initialize_content.png)

### Rôle

L'outil met à disposition un rôle "SiteBuilder" global pour gestion initial 
d'accès aux contenus des futurs utilisateurs.

![Initialize role](/Resources/doc/images/technical/initialize_role.png)

## Modèles

### Bundle

Après initialisation d'un modèle par la commande edgarez:sitebuilder:model:generate ou en Back-Office, 
l'outil met en place, dans les sources applicatives, au niveau du dossier Models, un 
bundle de modèle de site par modèle généré.

Chaque bundle renferme :

* les settings par défaut du modèle
* la vue par défaut layout
* la vue par défaut de la page d'accueil du modèle

![Models bundle](/Resources/doc/images/models/models_bundle.png)

Une fois le bundle de modèle de site initialisé, il est à disposition des concepteurs
pour définir les controlleurs, vues, extensions twig ... spécifique au modèle.

### Contenu, Media

La génération d'un modèle de site initialise les espaces de contenu et média
pour le modèle.

![Models content](/Resources/doc/images/models/models_content.png)
 
Les concepteurs peuvent maintenant définir l'arborescence de contenu de ce modèle en
disposant une arborescence de contenu, des contenus de type Lorem ipsum ... un maximum
de contenu permettant aux futurs clients de disposer d'un modèle de base
le plus complet possible.
 
### Politique de sécurité

## Clients

### Bundle

Après initialisation d'un client par la commande edgarez:sitebuilder:customer:generate ou en Back-Office, 
l'outil met en place, dans les sources applicatives, au niveau du dossier Customers, un  
dossier au nom du client contenant :

* un dossier Sites destiné à recevoir les futurs bundle de site
* un bundle client SitesBundle renfermant les settings de l'espace client

![Customers bundle](/Resources/doc/images/customers/customers_bundle.png)

### Contenu, Media

La génération d'un client initialise les espaces de contenu et média pour les futurs 
sites de ce client

![Customers content](/Resources/doc/images/customers/customers_content.png)

### Groupe utilsiateur

Chaque client aura un accès au Back-Office soit en tant que créateur, soit en tant qu'éditeur.
A la génération du client, un premier utilisateur type créateur est mis en place afin 
de permettre un accès au client à l'interface de l'outil dès l'initialisa de son espace.

![Customers users](/Resources/doc/images/customers/customers_users.png)

### Rôle

Les comptes utilisateur client, créateur ou éditeur sont associés à des rôles
spécifique au type de compte.
Les politiques de sécurité sont initialisées afin de permettre un accès restreint
au contenu : un client ne pourra pas avoir accès au contenu d'un autre client.

![Customers roles](/Resources/doc/images/customers/customers_roles.png)

Les concepteurs pourront par la suite adapter ces différents rôles pour customiser en fonction
des sites clients, des modèles de site utilisés, des restrictions d'accès supplémentaires aux
différents types de contenu, niveau d'arborescence ...

## Sites

### Bundle

Après initialisation d'un site par la commande edgarez:sitebuilder:site:generate ou en Back-Office, 
l'outil met en place, dans les sources applicatives, au niveau du dossier Customers, sous-dossier 
correspondant au client pour lequel le site est généré, sous-dossier sites, un bundle de site 
par défaut sans controlleur, sans vue ...

Au sein du bundle ProjectBundle, est initialisé la configuration ezplatform de définition
du nouveau siteaccess

![Sites bundle](/Resources/doc/images/sites/sites_bundle.png)

Le bundle de site généré hérite du modèle de modèle de site sélectionné à l'initialisation
du site.

![Sites bundle inheritance](/Resources/doc/images/sites/sites_bundle2.png)

Les concepteurs peuvent alors surcharger l'ensemble des composants technique du bundle de modèle
au sein du bundle de site : controlleur, vues ...

### Contenu, Media

Après génération du nouveau site, dans les arborescences de contenu et média, sous 
le niveau d'arborescence du client pour lequel le site a été initialisé, est 
dupliqué l'arborescence du modèle de site sélectionné pour ce nouveau site

![Sites content](/Resources/doc/images/sites/sites_content.png)

Les clients, créateur et/ou éditeur peuvent désormais modifier, ajouter et compléter 
le contenu de leur nouveau site.

### Politique de sécurité

Après initialisation du nouveau site, la politique de sécurité user/login des 
rôles créateur/éditeur du client de ce site évolue pour autoriser l'accès
en front-office à leur nouveau site.

![Sites role](/Resources/doc/images/sites/sites_role.png)
