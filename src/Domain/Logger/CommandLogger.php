<?php

namespace Netosoft\DomainBundle\Domain\Logger;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\CommandLoggerInterface;
use Netosoft\DomainBundle\Domain\Logger\Annotation\CommandLogger as CommandLoggerAnnotation;
use Netosoft\DomainBundle\Domain\Logger\Annotation\NotLog;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CommandLogger implements CommandLoggerInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var Reader */
    private $annotationReader;

    /** @var CommandLoggerInterface */
    private $loggerFallback;

    public function __construct(ContainerInterface $container, Reader $annotationReader, CommandLoggerInterface $loggerFallback)
    {
        $this->container = $container;
        $this->annotationReader = $annotationReader;
        $this->loggerFallback = $loggerFallback;
    }

    public function mustLog(CommandInterface $command): bool
    {
        $refClass = new \ReflectionClass($command);

        $annotation = $this->annotationReader->getClassAnnotation($refClass, NotLog::class);

        return null === $annotation;
    }

    /** {@inheritdoc} */
    public function log(CommandInterface $command): array
    {
        $refClass = new \ReflectionClass($command);

        /** @var CommandLoggerAnnotation|null $annotation */
        $annotation = $this->annotationReader->getClassAnnotation($refClass, CommandLoggerAnnotation::class);
        if (null == $annotation || null === $annotation->service) {
            $logger = $this->loggerFallback;
        } else {
            $logger = $this->container->get($annotation->service);
            if (!($logger instanceof CommandLoggerInterface)) {
                throw new \InvalidArgumentException(\sprintf('"%s" must be implement %s', $annotation->service, CommandLoggerInterface::class));
            }
        }

        return $logger->log($command);
    }
}
