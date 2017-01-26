<?php

namespace Netosoft\DomainBundle\Domain;

trait HandlerAwareTrait
{
    /** @var HandlerInterface */
    protected $domainHandler;

    public function setDomainHandler(HandlerInterface $domainHandler)
    {
        $this->domainHandler = $domainHandler;
    }
}
