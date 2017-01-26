<?php

namespace Netosoft\DomainBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\CommandLoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * BaseCommandLogRepository.
 */
abstract class BaseCommandLogRepository extends EntityRepository implements CommandLogRepositoryInterface
{
    protected $uniqueId = null;

    /**
     * @return CommandLogInterface
     */
    public function newInstance()
    {
        $class = $this->getClassName();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity(CommandInterface $command, int $type, CommandLogInterface $previousCommandLog = null, \Throwable $exception = null)
    {
        $entity = $this->newInstance();

        $entity->setPreviousCommandLog($previousCommandLog);
        $entity->setType($type);

        $entity->setCommandData($this->getCommandLogger()->log($command));

        $entity->setCommandClass(get_class($command));
        $entity->setRequest($this->getRequestStack()->getMasterRequest());
        $entity->setCurrentUsername($this->getCurrentUsername());

        if ($exception !== null) {
            $entity->setException($exception);
        }

        if ($this->uniqueId === null) {
            $this->uniqueId = uniqid('request');
        }

        $entity->setRequestId($this->uniqueId);

        return $entity;
    }

    public function getChoicesForCommandClass()
    {
        $qb = $this->createQueryBuilder('command_log');
        $qb->select('command_log.commandClass')->distinct();
        $results = $qb->getQuery()->getScalarResult();

        return array_values(array_map(function ($row) {
            return $row['commandClass'];
        }, $results));
    }

    public function getChoicesForType(): array
    {
        return BaseCommandLog::getChoicesForType();
    }

    /**
     * @return null|string
     */
    protected function getCurrentUsername()
    {
        $token = $this->getTokenStorage()->getToken();
        if ($token !== null) {
            $user = $token->getUser();
            if ($user !== null) {
                if ($user instanceof UserInterface) {
                    return $user->getUsername();
                } else {
                    return (string) $user;
                }
            }
        }

        return null;
    }

    abstract public function getCommandLogger(): CommandLoggerInterface;

    abstract public function getRequestStack(): RequestStack;

    abstract public function getTokenStorage(): TokenStorageInterface;
}
