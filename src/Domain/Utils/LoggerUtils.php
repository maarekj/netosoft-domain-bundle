<?php

namespace Netosoft\DomainBundle\Domain\Utils;

use Netosoft\DomainBundle\Domain\Logger\Annotation\LogFields;
use Netosoft\DomainBundle\Domain\Logger\Annotation\LogMessage;
use Netosoft\DomainBundle\Domain\Logger\ExpressionLanguageProvider;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class LoggerUtils
{
    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /** @var Reader */
    private $annotationReader;

    /** @var ExpressionLanguage */
    private $expressionLanguage;

    public function __construct(AdapterInterface $cacheAdapter, Reader $annotationReader, ExpressionLanguageProvider $languageProvider)
    {
        $this->annotationReader = $annotationReader;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->expressionLanguage = new ExpressionLanguage($cacheAdapter, [$languageProvider]);
    }

    public function logCommand($command): array
    {
        $class = new \ReflectionClass($command);

        $array = [];

        foreach ($class->getProperties() as $property) {
            /** @var LogFields|null $logFieldsAnnot */
            $logFieldsAnnot = $this->annotationReader->getPropertyAnnotation($property, LogFields::class);
            if ($logFieldsAnnot !== null) {
                $object = $this->getValue($command, $property);
                if ($object === null) {
                    $array[$property->getName()] = null;
                } else {
                    $array[$property->getName()] = $this->logFields($object, $logFieldsAnnot);
                }
            } else {
                $array[$property->getName()] = $this->getValue($command, $property);
            }
        }

        /** @var LogMessage $logMessageAnnot */
        $logMessageAnnot = $this->annotationReader->getClassAnnotation($class, LogMessage::class);
        if ($logMessageAnnot !== null) {
            try {
                $array['__command_message__'] = $this->expressionLanguage->evaluate($logMessageAnnot->expression, [
                    'o' => $command,
                ]);
            } catch (\Exception $e) {
            }
        }

        return $array;
    }

    protected function logFields($object, LogFields $annot): array
    {
        $array = [];

        foreach ($annot->fields as $field) {
            $array[$field] = $this->getValue($object, $field);
        }

        return $array;
    }

    /**
     * @param mixed               $object
     * @param \ReflectionProperty $property
     *
     * @return mixed
     */
    protected function getValue($object, $property)
    {
        $property = is_string($property) ? $property : $property->getName();

        try {
            return $this->propertyAccessor->getValue($object, $property);
        } catch (\Exception $e) {
            return null;
        }
    }
}
