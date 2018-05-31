<?php

namespace Netosoft\DomainBundle\Domain\Handler;

use Doctrine\ORM\EntityManager;
use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\Utils\SecurityUtils;
use Netosoft\DomainBundle\Domain\Utils\ValidatorUtils;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class HandlerHelper
{
    /** @var RegistryInterface */
    private $doctrine;

    /** @var ValidatorUtils */
    private $validatorUtils;

    /** @var SecurityUtils */
    private $securityUtils;

    public function __construct(RegistryInterface $doctrine, ValidatorUtils $validatorUtils, SecurityUtils $securityUtils)
    {
        $this->doctrine = $doctrine;
        $this->validatorUtils = $validatorUtils;
        $this->securityUtils = $securityUtils;
    }

    public function handleCreateOrEdit(CommandInterface $command, $securityAttributes, $validationGroups, $getCallable)
    {
        if (null !== $securityAttributes) {
            $this->securityUtils->isGrantedOrThrow($securityAttributes, ['command' => $command]);
        }
        $this->validatorUtils->validateOrThrow($command);

        $entity = $getCallable($command);

        if ($validationGroups instanceof \Closure) {
            $validationGroups = $validationGroups($entity);
        }
        $this->validatorUtils->validateOrThrow($entity, null, $validationGroups);

        $manager = $this->getManager();
        $manager->persist($entity);
        $manager->flush();

        $command->setReturnValue($entity);
    }

    public function handleDelete(CommandInterface $command, $securityAttributes, callable $getCallable, callable $preRemove = null, callable $postRemove = null)
    {
        $entity = $getCallable($command);
        if (null !== $securityAttributes) {
            $this->securityUtils->isGrantedOrThrow($securityAttributes, ['command' => $command, 'entity' => $entity]);
        }
        $this->validatorUtils->validateOrThrow($command);

        $manager = $this->getManager();

        if (null !== $preRemove) {
            $preRemove($command, $entity);
        }
        $manager->remove($entity);
        $manager->flush();

        $command->setReturnValue($entity);

        if (null != $postRemove) {
            $postRemove($command, $entity);
        }
    }

    /**
     * @param string $name
     *
     * @return EntityManager
     */
    public function getManager(string $name = null)
    {
        /** @var EntityManager $manager */
        $manager = $this->doctrine->getManager($name);

        return $manager;
    }

    public function createGetter($path): callable
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        return function ($object) use ($path, $accessor) {
            return $accessor->getValue($object, $path);
        };
    }
}
