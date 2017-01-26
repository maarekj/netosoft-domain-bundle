<?php

namespace Netosoft\DomainBundle\Domain\Handler;

use Netosoft\DomainBundle\Domain\AbstractHandler;
use Netosoft\DomainBundle\Domain\Command\AbstractEditField;
use Netosoft\DomainBundle\Domain\Utils\SecurityUtils;
use Netosoft\DomainBundle\Domain\Utils\ValidatorUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EditFieldHandler extends AbstractHandler
{
    /** @var ValidatorUtils */
    private $validatorUtils;

    /** @var EntityManagerInterface */
    private $manager;

    /** @var SecurityUtils */
    private $securityUtils;

    public function __construct(EntityManagerInterface $manager, ValidatorUtils $validatorUtils, SecurityUtils $securityUtils)
    {
        parent::__construct(AbstractEditField::class);

        $this->validatorUtils = $validatorUtils;
        $this->manager = $manager;
        $this->securityUtils = $securityUtils;
    }

    public function handleCommand(AbstractEditField $command)
    {
        $entity = $command->getEntity();
        $this->securityUtils->isGrantedOrThrow($command->getSecurityAttributes(), ['entity' => $entity, 'command' => $command]);
        $this->validatorUtils->validateOrThrow($command);

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($entity, $command->getField(), $command->getValue());

        $this->validatorUtils->validateOrThrow($entity, null, $command->getValidationGroups());

        $this->manager->persist($entity);
        $this->manager->flush();

        $command->setReturnValue($entity);
    }
}
