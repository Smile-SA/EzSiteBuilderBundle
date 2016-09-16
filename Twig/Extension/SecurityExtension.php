<?php

namespace Smile\EzSiteBuilderBundle\Twig\Extension;

use Smile\EzSiteBuilderBundle\Service\SecurityService;

class SecurityExtension extends \Twig_Extension
{
    /** @var SecurityService $securityService */
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'smileezsb_security_twig_extension';
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
        return $this->securityService->checkAuthorization($func);
    }
}
