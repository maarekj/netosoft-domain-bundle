<?php

namespace Netosoft\DomainBundle\Domain\Handler;

use Doctrine\ORM\EntityManager;
use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\Utils\SecurityUtils;
use Netosoft\DomainBundle\Domain\Utils\ValidatorUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;

class HandlerHelper
{
    /** @var EntityManager */
    private $manager;

    /** @var ValidatorUtils */
    private $validatorUtils;

    /** @var SecurityUtils */
    private $securityUtils;

    public function __construct(EntityManager $manager, ValidatorUtils $validatorUtils, SecurityUtils $securityUtils)
    {
        $this->manager = $manager;
        $this->validatorUtils = $validatorUtils;
        $this->securityUtils = $securityUtils;
    }

    public function handleCreateOrEdit(CommandInterface $command, $securityAttributes, $validationGroups, $getCallable)
    {
        if ($securityAttributes !== null) {
            $this->securityUtils->isGrantedOrThrow($securityAttributes, ['command' => $command]);
        }
        $this->validatorUtils->validateOrThrow($command);

        $entity = $getCallable($command);

        $this->validatorUtils->validateOrThrow($entity, null, $validationGroups);

        $this->manager->persist($entity);
        $this->manager->flush();

        $command->setReturnValue($entity);
    }

    public function handleDelete(CommandInterface $command, $securityAttributes, callable $getCallable, callable $preRemove = null, callable $postRemove = null)
    {
        $entity = $getCallable($command);
        if ($securityAttributes !== null) {
            $this->securityUtils->isGrantedOrThrow($securityAttributes, ['command' => $command, 'entity' => $entity]);
        }
        $this->validatorUtils->validateOrThrow($command);

        $this->manager->beginTransaction();
        try {
            if ($preRemove !== null) {
                $preRemove($command, $entity);
            }
            $this->manager->remove($entity);
            $this->manager->commit();

            $command->setReturnValue($entity);

            if ($postRemove != null) {
                $postRemove($command, $entity);
            }
        } catch (\Exception $e) {
            $this->manager->rollback();
            throw $e;
        }
    }

    public function createGetter($path): callable
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        return function ($object) use ($path, $accessor) {
            return $accessor->getValue($object, $path);
        };
    }
}
