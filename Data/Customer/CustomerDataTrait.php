<?php

namespace Smile\EzSiteBuilderBundle\Data\Customer;

use Smile\EzSiteBuilderBundle\Values\Content\Customer;

trait CustomerDataTrait
{
    /** @var Customer $customer */
    protected $customer;

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }
}
