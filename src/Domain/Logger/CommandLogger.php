<?php

namespace Netosoft\DomainBundle\Domain\Logger;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\CommandLoggerInterface;
use Netosoft\DomainBundle\Domain\Logger\Annotation\CommandLogger as CommandLoggerAnnotation;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CommandLogger implements CommandLoggerInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var Reader */
    private $annotationReader;

    /** @var DefaultCommandLogger */
    private $loggerFallback;

    public function __construct(ContainerInterface $container, Reader $annotationReader, CommandLoggerInterface $loggerFallback)
    {
        $this->container = $container;
        $this->annotationReader = $annotationReader;
        $this->loggerFallback = $loggerFallback;
    }

    /** {@inheritdoc} */
    public function log(CommandInterface $command): array
    {
        $refClass = new \ReflectionClass($command);

        /** @var CommandLoggerAnnotation|null $annotation */
        $annotation = $this->annotationReader->getClassAnnotation($refClass, CommandLoggerAnnotation::class);
        if ($annotation == null || $annotation->service === null) {
            $logger = $this->loggerFallback;
        } else {
            $logger = $this->container->get($annotation->service);
        }

        return $logger->log($command);
    }
}
