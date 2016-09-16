<?php

namespace Smile\EzSiteBuilderBundle\Data\User;

use Smile\EzSiteBuilderBundle\Values\Content\User;

trait UserDataTrait
{
    /** @var User $customer */
    protected $user;

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
