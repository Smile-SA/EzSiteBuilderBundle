<?php

namespace EdgarEz\SiteBuilderBundle\Data\Customer;

use EdgarEz\SiteBuilderBundle\Values\Content\Customer;

trait CustomerDataTrait
{
    /** @var Customer $customer */
    protected $customer;

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }
}
