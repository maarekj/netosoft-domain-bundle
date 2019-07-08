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

    public function getId(): ?int;

    public function setId(?int $id): void;

    public function getPreviousCommandLog(): ?self;

    public function setPreviousCommandLog(?self $previousCommandLog = null): void;

    public function getSessionId(): ?string;

    public function getType(): ?int;

    public function setType(?int $type): void;

    public function getMessage(): ?string;

    public function setMessage(?string $message): void;

    public function getCommandData(): ?array;

    public function setCommandData(?array $commandData): void;

    public function getCommandClass(): ?string;

    public function setCommandClass(?string $commandClass): void;

    public function getCurrentUsername(): ?string;

    public function setCurrentUsername(?string $currentUsername): void;

    public function getDate(): \DateTimeImmutable;

    public function getRequest(): ?array;

    public function setRequest(?Request $request = null): void;

    public function setException(?\Throwable $exception = null): void;

    public function getRequestId(): ?string;

    public function setRequestId(?string $requestId): void;

    public function getClientIp(): ?string;

    public function setClientIp(?string $clientIp): void;

    public function getPathInfo(): ?string;

    public function setPathInfo(?string $pathInfo): void;

    public function getUri(): ?string;

    public function setUri(?string $uri): void;

    public function getExceptionMessage(): ?string;

    public function setExceptionMessage(?string $exceptionMessage): void;

    public function getExceptionFullMessage(): ?string;

    public function setExceptionFullMessage(?string $exceptionFullMessage): void;

    public function getExceptionClass(): ?string;

    public function setExceptionClass(?string $exceptionClass): void;

    public function getExceptionData(): ?array;

    public function setExceptionData(?array $exceptionData): void;
}
