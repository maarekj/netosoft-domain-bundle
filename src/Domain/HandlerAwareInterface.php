<?php

namespace Netosoft\DomainBundle\Domain;

interface HandlerAwareInterface
{
    public function setDomainHandler(HandlerInterface $domainHandler);
}
