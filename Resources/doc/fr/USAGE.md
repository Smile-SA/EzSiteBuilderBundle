# Guide 'utilisation

## Introduction

## Initialisation modèle

### mode console

```console
php app/console edgarez:sitebuilder:model:generate
```

Le mode interactif de cette installation vous demande les informations suivantes :

* nom du vendeur (vendorName) [Acme]
  * uniquement des lettres, première lettre en majuscule
  * renseignez le même nom de vendeur que celui lors de l'installation de SiteBuilder
* dossier d'installation des bundles [<votre dopssier d'installation ezplatform>/src]
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
* ModelsName : nom du modèle

Notes :

* l'utilisateur exécutant cette commande doit avoir les droits d'écriture dans le dossier d'installation des bundles (src)
* l'utilisateur exécutant cette commande doit avoir les droits d'écriture sur le fichier app/AppKernel.php

### interface Back-Office

en développement

## Initialisation client

### mode console

```console
php app/console edgarez:sitebuilder:customer:generate
```

Le mode interactif de cette installation vous demande les informations suivantes :

* nom du vendeur (vendorName) [Acme]
  * uniquement des lettres, première lettre en majuscule
  * renseignez le même nom de vendeur que celui lors de l'installation de SiteBuilder
* dossier d'installation des bundles [<votre dopssier d'installation ezplatform>/src]
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


### interface Back-Office

en développement

## Initialisation site'

### mode console

```console
php app/console edgarez:sitebuilder:site:generate
```

Le mode interactif de cette installation vous demande les informations suivantes :

* nom du vendeur (vendorName) [Acme]
  * uniquement des lettres, première lettre en majuscule
  * renseignez le même nom de vendeur que celui lors de l'installation de SiteBuilder
* dossier d'installation des bundles [<votre dopssier d'installation ezplatform>/src]
* nom du site
  * uniquement des lettres, première lettre en majuscule
* location id de la racine de contenu client où devra se créer le site
* location id de la racine de contenu du modèle de site
* location id de la racine de média client où devra se créer le site
* location id de la racine de média du modèle de site
  
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


### interface Back-Office

en développement


