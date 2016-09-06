<?php

namespace EdgarEz\SiteBuilderBundle\Data\User;

use EdgarEz\SiteBuilderBundle\Values\Content\User;

trait UserDataTrait
{
    /** @var User $customer */
    protected $user;

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
