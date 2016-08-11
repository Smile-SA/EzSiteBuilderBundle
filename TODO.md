# TODO

## Global

* manage content/contentType language
* throw exception for question validator methods
* catch exceptions
* <s>move $vendorName, $dir ... to BaseContainerAwareCommand</s>
* manage questions and content actions because no rollback (question/validation first)
* <s>standardize questions</s>
* manage _dev.yml, _prod.yml ... settings
* <s>remove sitebuilder user group administrator</s>
* user guide

## Installation

* <s>add user creator/editor role</s>
* <s>create media content structure</s>
* override default_settings adminID
* override default_settings host
* override default_settings system admin mail

## Customer bundle generation 

* <s>generate customers in Customers folder from</s>
* <s>create sitebuilder user creator customer group</s>
* <s>create sitebuilder user editor customer group</s>
* <s>create customer media content structure</s>
* <s>create user creator/editor role</s>
* <s>assign role to user groups with subtree limitation</s>
* create one sitebuilder user creator for this customer
* create one sitebuilder user editor for this customer

## Site generator

* <s>copy model to customer subtree</s>
* <s>create customer site bundle inherited model</s>
* <s>copy model media content structure to customer media content subtree</s>
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

## Tools

* export/import contentType and contentTypeGroup
* export/import content


