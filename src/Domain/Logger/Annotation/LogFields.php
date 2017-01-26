<?php

namespace Netosoft\DomainBundle\Domain\Logger\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class LogFields
{
    /** @var string[] */
    public $fields;
}
