<?php

namespace Netosoft\DomainBundle\Entity;

use Symfony\Component\HttpFoundation\Request;

interface CommandLogInterface
{
    const TYPE_BEFORE_HANDLER = 0;
    const TYPE_AFTER_HANDLER = 1;
    const TYPE_EXCEPTION = 2;

    public static function getLabelForType(int $type): string;

    public static function getChoicesForType(): array;

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return CommandLogInterface|null
     */
    public function getPreviousCommandLog();

    /**
     * @param CommandLogInterface|null $previousCommandLog
     *
     * @return $this
     */
    public function setPreviousCommandLog(CommandLogInterface $previousCommandLog = null);

    /**
     * @return null|string
     */
    public function getSessionId();

    /**
     * @return int|null
     */
    public function getType();

    /**
     * @param int|null $type
     *
     * @return $this
     */
    public function setType($type);

    /**
     * @return null|string
     */
    public function getMessage();

    /**
     * @param null|string $message
     *
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return array|null
     */
    public function getCommandData();

    /**
     * @param array|null $commandData
     *
     * @return $this
     */
    public function setCommandData($commandData);

    /**
     * @return null|string
     */
    public function getCommandClass();

    /**
     * @param null|string $commandClass
     *
     * @return $this
     */
    public function setCommandClass($commandClass);

    /**
     * @return null|string
     */
    public function getCurrentUsername();

    /**
     * @param null|string $currentUsername
     *
     * @return $this
     */
    public function setCurrentUsername($currentUsername);

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable;

    /**
     * @return array|null
     */
    public function getRequest();

    /**
     * @param Request|null $request
     *
     * @return $this
     */
    public function setRequest(Request $request = null);

    /**
     * @param \Throwable $exception
     *
     * @return $this
     */
    public function setException(\Throwable $exception = null);

    /**
     * @return null|string
     */
    public function getRequestId();

    /**
     * @param null|string $requestId
     *
     * @return $this
     */
    public function setRequestId($requestId);

    /**
     * @return null|string
     */
    public function getClientIp();

    /**
     * @param null|string $clientIp
     *
     * @return $this
     */
    public function setClientIp($clientIp);

    /**
     * @return null|string
     */
    public function getPathInfo();

    /**
     * @param null|string $pathInfo
     *
     * @return $this
     */
    public function setPathInfo($pathInfo);

    /**
     * @return null|string
     */
    public function getUri();

    /**
     * @param null|string $uri
     *
     * @return $this
     */
    public function setUri($uri);

    /**
     * @return null|string
     */
    public function getExceptionMessage();

    /**
     * @param null|string $exceptionMessage
     *
     * @return $this
     */
    public function setExceptionMessage($exceptionMessage);

    /**
     * @return null|string
     */
    public function getExceptionFullMessage();

    /**
     * @param null|string $exceptionFullMessage
     *
     * @return $this
     */
    public function setExceptionFullMessage($exceptionFullMessage);

    /**
     * @return null|string
     */
    public function getExceptionClass();

    /**
     * @param null|string $exceptionClass
     *
     * @return $this
     */
    public function setExceptionClass($exceptionClass);

    /**
     * @return null|string
     */
    public function getExceptionData();

    /**
     * @param null|string $exceptionData
     *
     * @return $this
     */
    public function setExceptionData($exceptionData);
}
