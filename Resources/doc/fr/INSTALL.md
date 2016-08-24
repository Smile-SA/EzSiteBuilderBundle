# Guide d'installation

## Introduction

## Installation

### Bundle et pré-requis

```console
composer require edgarez/sitebuilderbundle
```

### Ajout dépendance bundles

Ajouter dans le fichier AppKernel.php

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new EdgarEz\ToolsBundle\EdgarEzToolsBundle(),
            new EdgarEz\SiteBuilderBundle\EdgarEzSiteBuilderBundle()
```

### Ajout support Doctrine ORM

Ajouter dans la configuration globale app/config/ezplatform.yml

```yaml
doctrine:
    orm:
        auto_mapping: true
```

### Mise à jour des données

```console
php app/console doctrine:schema:update --force
```

### Ajout routing

Ajouter dans la configuration globale app/config/routing.yml

```yaml
edgarezsb_dashboard:
    resource: '@EdgarEzSiteBuilderBundle/Resources/config/routing.yml'
```

### Ajout paramètres par défaut

Ajouter dans le fichier app/parameters.yml

```yml
parameters:
    ...
    edgar_ez_tools.adminid: 14
    edgar_ez_site_builder.host: ezplatform.lxc
    edgar_ez_site_builder.sysadminemail: emdro@smile.fr
```

### Redéfinir les assets

```console
php app/console assets:install --symlink web
php app/console assets:dump web
```

### Installation SiteBuilder

#### Mode console

```console
php app/console edgarez:sitebuilder:install
```

Le mode interactif de cette installation vous demande les informations suivantes :

* nom du vendeur (vendorName) [Acme]
* dossier d'installation des bundles [votre dopssier d'installation ezplatform/src]
* location id de la racine des contenus où seront initialisés les structures clients/modèles [2]
* location id de la racine des médias où seront initialisés les structures clients/modèles [43]
* location id de la racine des utilisateurs où seront initialisés les structures créateur/éditeur [5]

A l'issue de ces étapes, il vous est demandé si vous souhaitez mettre automatiquement à jour le fichier de Kernel (app/AppKernel.php)
Si vous refusez cette mise à jour automatique, modifiez le fichier app/AppKernel.php comme suit :

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new <VendorName>\ProjectBundle\<VendorName>ProjectBundle(),
```

où :

* VendorName : nom du vendeur

Notes :

* l'utilisateur exécutant cette commande doit avoir les droits d'écriture dans le dossier d'installation des bundles (src)
* l'utilisateur exécutant cette commande doit avoir les droits d'écriture sur le fichier app/AppKernel.php
* cet utilisateur doit également pouvoir vider les cache et écrire dans les logs

#### Mode Back-office

en développement

### Adaptation app/AppKernel.php

Les futurs bundles de site hériteront des bundles de modèles.
Comme il est impossible que plusieurs bundles héritent d'un même bundle (plusieurs sites seront basés sur le même modèle), nous devons gérer le chargement des bundles de site en fonction d'une variable d'environnement qui sera injectée par votre configuration Apache (Nginx).

Modifier votre fichier app/AppKernel.php comme décrit dans les commentaires :

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new <VendorName>\ProjectBundle\<VendorName>ProjectBundle(),
        );

        switch ($this->getEnvironment()) {
            ...
        }

        // ### Ajouter l'appel à cette méthode ###
        $bundles = $this->siteBuilderBundles($bundles);

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    // ### Surcharge de la méthode de définition du dossier de cache
    public function getCacheDir()
    {
        return $this->rootDir.'/cache/'.$this->environment . ((getenv('SITEBUILDER_ENV')) ? '/' . getenv('SITEBUILDER_ENV') : '');
    }
    
    // ### Ajouter cette méthode ###
    public function siteBuilderBundles($bundles)
    {
        if ($value = getenv('SITEBUILDER_ENV')) {
            $value = explode('_', $value);
            $value = $value[0] . '\\Customers\\' . $value[1] . '\\Sites\\' . $value[2] . 'Bundle\\' . $value[0] . 'Customers' . $value[1] . 'Sites' . $value[2] . 'Bundle';
            $bundles[] = new $value();
        }

        return $bundles;
    }
}
```

