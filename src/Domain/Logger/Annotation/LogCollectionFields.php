<?php

namespace Netosoft\DomainBundle\Domain\Logger\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class LogCollectionFields
{
    /** @var string[] */
    public $fields;
}
