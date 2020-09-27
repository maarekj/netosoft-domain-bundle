<?php

namespace Netosoft\DomainBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\CommandLoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template T as CommandLogInterface
 * @extends ServiceEntityRepository<T>
 */
class BaseCommandLogRepository extends ServiceEntityRepository implements CommandLogRepositoryInterface
{
    /** @var string|null */
    protected $uniqueId;

    /** @var CommandLoggerInterface */
    protected $commandLogger;

    /** @var RequestStack */
    protected $requestStack;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(ManagerRegistry $registry, string $entityClass, CommandLoggerInterface $commandLogger, RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($registry, $entityClass);
        $this->commandLogger = $commandLogger;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;

        $this->uniqueId = null;
    }

    public function newInstance(): CommandLogInterface
    {
        $class = $this->getClassName();

        return new $class();
    }

    public function createEntity(CommandInterface $command, int $type, ?CommandLogInterface $previousCommandLog = null, ?\Throwable $exception = null): CommandLogInterface
    {
        $entity = $this->newInstance();

        $entity->setPreviousCommandLog($previousCommandLog);
        $entity->setType($type);

        $entity->setCommandData($this->commandLogger->log($command));

        $entity->setCommandClass(\get_class($command));
        $entity->setRequest($this->requestStack->getMasterRequest());
        $entity->setCurrentUsername($this->getCurrentUsername());

        if (null !== $exception) {
            $entity->setException($exception);
        }

        if (null === $this->uniqueId) {
            $this->uniqueId = \uniqid('request');
        }

        $entity->setRequestId($this->uniqueId);

        return $entity;
    }

    public function getChoicesForCommandClass(): array
    {
        $qb = $this->createQueryBuilder('command_log');
        $qb->select('command_log.commandClass')->distinct();
        $results = $qb->getQuery()->getScalarResult();

        return \array_values(\array_map(function ($row) {
            return $row['commandClass'];
        }, $results));
    }

    public function getChoicesForType(): array
    {
        return BaseCommandLog::getChoicesForType();
    }

    protected function getCurrentUsername(): ?string
    {
        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            $user = $token->getUser();
            if (null !== $user) {
                if ($user instanceof UserInterface) {
                    return $user->getUsername();
                } else {
                    return (string) $user;
                }
            }
        }

        return null;
    }
}
