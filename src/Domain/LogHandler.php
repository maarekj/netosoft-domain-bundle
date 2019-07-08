<?php

namespace Netosoft\DomainBundle\Domain;

use Netosoft\DomainBundle\Domain\Logger\CommandLogger;
use Netosoft\DomainBundle\Entity\CommandLogInterface;
use Netosoft\DomainBundle\Entity\CommandLogRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class LogHandler.
 */
class LogHandler implements HandlerInterface
{
    /** @var HandlerInterface */
    private $decorated;

    /** @var EntityManagerInterface */
    private $manager;

    /** @var CommandLogRepositoryInterface */
    private $commandLogRepo;

    /** @var LoggerInterface */
    private $logger;

    /** @var CommandLogger */
    private $commandLogger;

    /**
     * LogHandler constructor.
     *
     * @param EntityManagerInterface        $manager
     * @param CommandLogRepositoryInterface $commandLogRepo
     * @param LoggerInterface               $logger
     */
    public function __construct(EntityManagerInterface $manager, LoggerInterface $logger, CommandLogRepositoryInterface $commandLogRepo, CommandLogger $commandLogger)
    {
        $this->manager = $manager;
        $this->commandLogRepo = $commandLogRepo;
        $this->logger = $logger;
        $this->commandLogger = $commandLogger;
    }

    public function setDecoratedHandler(HandlerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /** {@inheritdoc} */
    public function acceptCommand(CommandInterface $command): bool
    {
        return $this->decorated->acceptCommand($command);
    }

    /** {@inheritdoc} */
    public function handle(CommandInterface $command)
    {
        if (false === $this->commandLogger->mustLog($command)) {
            return $this->decorated->handle($command);
        } else {
            $commandLog = $this->logCommand($command, CommandLogInterface::TYPE_BEFORE_HANDLER);
            try {
                $return = $this->decorated->handle($command);
                $this->logCommand($command, CommandLogInterface::TYPE_AFTER_HANDLER, $commandLog);

                return $return;
            } catch (\Throwable $e) {
                $this->logCommand($command, CommandLogInterface::TYPE_EXCEPTION, $commandLog, $e);
                throw ($e instanceof \Exception ? $e : new \RuntimeException($e));
            }
        }
    }

    protected function logCommand(CommandInterface $command, int $type, CommandLogInterface $previousCommandLog = null, \Throwable $exception = null)
    {
        $entity = $this->commandLogRepo->createEntity($command, $type, $previousCommandLog, $exception);

        $message = $entity->getMessage();
        $message = null === $message ? '' : $message;

        if (null !== $exception) {
            $this->logger->error($message, ['command' => $entity->getCommandData(), 'type' => $type, 'exception' => $exception]);
        } else {
            $this->logger->info($message, ['command' => $entity->getCommandData(), 'type' => $type]);
        }

        $this->manager->persist($entity);
        $this->manager->flush();

        return $entity;
    }
}
