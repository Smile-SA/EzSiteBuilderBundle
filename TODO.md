# TODO

## Global

* manage language
* catch and throw exceptions
* finalize user guide
* translate user guide
* finalize command and services to handle sitebuilder_task actions

## Installation

## Customer bundle generation 

## Model generator

## Site generator

* send system admin a mail to configure virtualhost with new siteaccess

## eZPlatform  SiteBuilder interface

* interface for system admin 
  * pre-activate site after generation before activation
* interface for creator : create site
  * accessible only for ez admin and sitebuilder user creator
  * register site request
  * cronjob which execute site:generate task if one request exists
* interface for creator : list sites
  * accessible only for ez admin and sitebuilder user creator
  * for sitebuilder user creator, display only own customer sites
  * activate site (only accessible when pre-activated)



