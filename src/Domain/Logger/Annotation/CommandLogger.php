<?php

namespace Netosoft\DomainBundle\Domain\Logger\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class CommandLogger
{
    /** @var string */
    public $service;
}
