<?php

namespace EdgarEz\SiteBuilderBundle\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SecurityExtension extends \Twig_Extension
{
    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var AuthorizationChecker $authorizationChecker */
    protected $authorizationChecker;

    public function __construct(TokenStorage $tokenStorage, AuthorizationChecker $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'edgarezsb_security_twig_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'sb_can',
                array($this, 'checkAuthorization')
            ),
        );
    }

    public function checkAuthorization($func)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$this->authorizationChecker->isGranted(new Attribute('sitebuilder', $func))) {
            return false;
        }

        if ($func == 'sitegenerate' || $func == 'siteactivate') {

        }

        return true;
    }
}
