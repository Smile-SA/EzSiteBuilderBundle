# EdgarEzSiteBuilderBundle

[![Latest Stable Version](https://poser.pugx.org/edgarez/sitebuilderbundle/v/stable)](https://packagist.org/packages/edgarez/sitebuilderbundle) 
[![Total Downloads](https://poser.pugx.org/edgarez/sitebuilderbundle/downloads)](https://packagist.org/packages/edgarez/sitebuilderbundle)
[![License](https://poser.pugx.org/edgarez/sitebuilderbundle/license)](https://packagist.org/packages/edgarez/sitebuilderbundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6f66ce27-9b99-411c-a52b-d3fcc715684e/mini.png)](https://insight.sensiolabs.com/projects/6f66ce27-9b99-411c-a52b-d3fcc715684e)

This bundle aims to provide a webFactory tool in eZ Platform context.

> This bundle is a pre-release, continue to evolve
> Your help is welcom to fix, evolve or customize this project

## Documentation

French : [documentation](Resources/doc/fr/README.md)

English : coming soon
 
## Screencast (in french)

[![Screencast](/Resources/doc/images/screencast.png)](https://youtu.be/dh_zID7Lcss "Screencast")

## Changelog

> During pre-release (1.0.x), you can't just update the bundle
>
> Content architecture, Content Field Type, Roles and Policies will change
>
> we will not provide update script until stable release

### 1.0.9 -> 1.0.10

* it's no more possible to choose language when installing sitebuilder : we use main language
* you should now parameter all site settings for all languages when generating new site

### 1.0.8 -> 1.0.9

* you can now chosse your language when installing sitebuilder
  * this language will be default language for all operations : model, customer, user generation ...
  * beware of using another language than eng-GB : you should enable this language for 'site_group' and translate all content/media/user groups
* add command cache:clear after generating new siteaccess : new siteaccess is now available for policy user/login manipulation

### 1.0.7 -> 1.0.8

* new user generate tab for customer creator users
  * you can now create new creator/editor users for your customer
* fix dashboard : adding logs column, label status
* can't generate new site if no model exists (or activate)
* check if user with same email exists when creating new customer and user

### 1.0.6 -> 1.0.7

* vendor/customer/model/site name can now have uppercase not only for first letter
* fix site generate form
* add model activate
* add site activate

