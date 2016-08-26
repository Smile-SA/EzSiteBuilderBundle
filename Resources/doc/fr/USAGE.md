# Guide 'utilisation

## Introduction

## Initialisation modèle

### mode console

```console
php app/console edgarez:sitebuilder:model:generate
```

Le mode interactif de cette installation vous demande les informations suivantes :

* nom du modèle
  * uniquement des lettres, première lettre en majuscule
  
A l'issue de ces étapes, il vous est demandé si vous souhaitez mettre automatiquement à jour le fichier de Kernel (app/AppKernel.php)
Si vous refusez cette mise à jour automatique, modifiez le fichier app/AppKernel.php comme suit :

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new <VendorName>\Models\<ModelName>Bundle\<VendorName>Models<ModelName>Bundle(),
```

où :

* VendorName : nom du vendeur
* ModelName : nom du modèle

Notes :

* l'utilisateur exécutant cette commande doit avoir les droits d'écriture dans le dossier d'installation des bundles (src)
* l'utilisateur exécutant cette commande doit avoir les droits d'écriture sur le fichier app/AppKernel.php
* cet utilisateur doit également pouvoir vider les cache et écrire dans les logs

Pour que le nouveau model soit accessible aux utilisateurs dont le rôle est creator de site, vous devez exécuter la commande suivante :
 
```console
php app/console edgarez:sitebuilder:model:policy
```
 
Le mode interactif de cette installation vous demande les informations suivantes :
 
* nom du modèle
  * uniquement des lettres, première lettre en majuscule

### interface Back-Office

![Model Form](/Resources/doc/images/model.png)

Le formulaire d'initialisation d'un modèle est limité en accès par les politiques de sécurité.
Les champs du formulaire à renseigner sont :

* Model name : nom du modèle

Après validation du formulaire, vous êtes redirigé sur l'onglet Dashboard résumant les tâches effectuées, en cours ou programmées, dont celle que vous venez de soumettre.

## Initialisation client

### mode console

```console
php app/console edgarez:sitebuilder:customer:generate
```

Le mode interactif de cette installation vous demande les informations suivantes :

* nom du client
  * uniquement des lettres, première lettre en majuscule
* Prénom du premier utilisateur créateur
* Nom du premier utilisateur créateur
* Adresse mail du premier utilisateur créateur
  
A l'issue de ces étapes, il vous est demandé si vous souhaitez mettre automatiquement à jour le fichier de Kernel (app/AppKernel.php)
Si vous refusez cette mise à jour automatique, modifiez le fichier app/AppKernel.php comme suit :

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new <VendorName>\Customers\<CustomerName>\SitesBundle\<VendorName>Customers<CustomerName>SitesBundle(),
```

où :

* VendorName : nom du vendeur
* CustomerName : nom du modèle

Notes :

* l'identifiant et le mot de passe du premier utilisateur créateur de ce client est indiqué dans la console : ces informations permettront à l'utilisateur de se connecter au Back-OFfice eZ Platform pour accéder au contenu et à l'interface de gestion de ses sites
* l'utilisateur exécutant cette commande doit avoir les droits d'écriture dans le dossier d'installation des bundles (src)
* l'utilisateur exécutant cette commande doit avoir les droits d'écriture sur le fichier app/AppKernel.php
* cet utilisateur doit également pouvoir vider les cache et écrire dans les logs

### interface Back-Office

![Customer Form](/Resources/doc/images/customer.png)

Le formulaire d'initialisation d'un client est limité en accès par les politiques de sécurité.
Les champs du formulaire à renseigner sont :

* Customer name : nom du client
* User first name : prénom du premier utilisateur client
* User last name : nom du premier utilisateur client
* User email : addresse mail du premier utilisateur client

Après validation du formulaire, vous êtes redirigé sur l'onglet Dashboard résumant les tâches effectuées, en cours ou programmées, dont celle que vous venez de soumettre.

## Initialisation site'

### mode console

```console
php app/console edgarez:sitebuilder:site:generate
```

Le mode interactif de cette installation vous demande les informations suivantes :

* nom du site
  * uniquement des lettres, première lettre en majuscule
* location id de la racine de contenu client où devra se créer le site
* location id de la racine de contenu du modèle de site
* location id de la racine de média client où devra se créer le site
* location id de la racine de média du modèle de site
* host pour le siteaccess
* voulez-vous une configuration map/uri
  * suffix pour le map/uri
  
A l'issue de ces étapes, il ne vous sera pas demandé si vous souhaitez mettre automatiquement à jour le fichier de Kernel (app/AppKernel.php)
Lors de l'[installation et configuartion des pré-requis](INSTALL.md), vous avez modifié le fichier app/AppKernel.php pour prendre en charge une variable d'environnement : SITEBUILDER_ENV
 
De ce fait, après initialisation de votre site, dans la configuration de votre serveur web, vous devez définir cette variable d'environnement, exemple pour une configuration Aapache :

```apache
<VirtualHost *:80>
    ServerName domain.tld
    DocumentRoot ...
    DirectoryIndex app.php

    ... 
    
    SetEnvIf Request_URI ".*" SYMFONY_ENV=dev

    # Injection de la variable d'environnement pour votre site
    SetEnvIf Request_URI ".*" SITEBUILDER_ENV=<VendorName>_<CustomerName>_<SiteName>
    ...
```

où :

* VendorName : nom du vendeur
* CustomerName : nom du modèle
* SiteName : nom du site

Notes :

* l'utilisateur exécutant cette commande doit avoir les droits d'écriture dans le dossier d'installation des bundles (src)
* l'utilisateur exécutant cette commande doit avoir les droits d'écriture sur le fichier app/AppKernel.php
* cet utilisateur doit également pouvoir vider les cache et écrire dans les logs

Pour que le nouveau siteaccess soit accessible aux utilisateurs dont le rôle est creator ou éditeur du site auquel appartien le client, vous devez exécuter la commande suivante :
 
```console
php app/console edgarez:sitebuilder:site:policy
```
 
Le mode interactif de cette installation vous demande les informations suivantes :
 
* nom du client
  * uniquement des lettres, première lettre en majuscule
* nom du site
  * uniquement des lettres, première lettre en majuscule

### interface Back-Office

![Site Form](/Resources/doc/images/site.png)

Le formulaire d'initialisation d'un site est limité en accès par les politiques de sécurité.
Les champs du formulaire à renseigner sont :

* Site name : nom du site
* Model : choix du modèle à utiliser pour créer le site
* Host : domaine d'accès à votre site
* Mapuri : sélectionner si vous souhaitez ajouter un suffix d'accès pour votre site
* Suffix : suffix d'accès à votre site

Après validation du formulaire, vous êtes redirigé sur l'onglet Dashboard résumant les tâches effectuées, en cours ou programmées, dont celle que vous venez de soumettre.


