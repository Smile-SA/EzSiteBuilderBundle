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

### 1.0.6 -> 1.0.7

* vendor/customer/model/site name can now have uppercase not only for first letter
* fix site generate form
* add model activate
* add site activate

if you upgrade from 1.0.6 to 1.0.7, add to 'edgar_ez_sb_model'
content type new field :

* type : ezboolean (checkbox)
* name : activated
* identifier : activated
* not required
