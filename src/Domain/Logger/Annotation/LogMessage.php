<?php

namespace Netosoft\DomainBundle\Domain\Logger\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class LogMessage
{
    /** @var string */
    public $expression;
}
