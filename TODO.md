# TODO

## Global

* manage content/contentType language
* throw exception for question validator methods
* catch exceptions
* manage questions and content actions because no rollback (question/validation first)
* manage _dev.yml, _prod.yml ... settings
* finalize user guide
* translate user guide
* add command to handle sitebuilder_task actions

## Installation

## Customer bundle generation 

* send email to first customer creator user

## Model generator

* update user/login for each customer user group

## Site generator

* ask host for siteaccess
* add new siteaccess to user/login policies for creator/editor roles for this customer
* manage siteaccess matching
* manage multilanguage site
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



