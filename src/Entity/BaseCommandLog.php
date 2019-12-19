<?php

namespace Netosoft\DomainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use function Netosoft\Utils\strictImmutableDate;
use Symfony\Component\HttpFoundation\Request;

/**
 * BaseCommandLog.
 *
 * @ORM\MappedSuperclass()
 */
abstract class BaseCommandLog implements CommandLogInterface
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CommandLogInterface|null
     * @ORM\OneToOne(targetEntity="Netosoft\DomainBundle\Entity\CommandLogInterface")
     */
    protected $previousCommandLog;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $sessionId;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $requestId;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $type;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    protected $message;

    /**
     * @var array|null
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $commandData;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=false)
     */
    protected $commandClass;

    /**
     * @var array|null
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $request;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $clientIp;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=400, nullable=true)
     */
    protected $pathInfo;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=400, nullable=true)
     */
    protected $uri;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    protected $currentUsername;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $exceptionMessage;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $exceptionFullMessage;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $exceptionClass;

    /**
     * @var array|null
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $exceptionData;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    //------------------------------------------------------------------------
    // region Static
    //------------------------------------------------------------------------

    public static function getLabelForType(int $type): string
    {
        switch ($type) {
            case self::TYPE_EXCEPTION:
                return 'exception';
            case self::TYPE_BEFORE_HANDLER:
                return 'before_handler';
            case self::TYPE_AFTER_HANDLER:
                return 'after_handler';
            default:
                throw new \InvalidArgumentException();
        }
    }

    public static function getChoicesForType(): array
    {
        return [
            self::getLabelForType(self::TYPE_EXCEPTION) => self::TYPE_EXCEPTION,
            self::getLabelForType(self::TYPE_BEFORE_HANDLER) => self::TYPE_BEFORE_HANDLER,
            self::getLabelForType(self::TYPE_AFTER_HANDLER) => self::TYPE_AFTER_HANDLER,
        ];
    }

    // endregion

    //------------------------------------------------------------------------
    // region Getters & Setters
    //------------------------------------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getPreviousCommandLog(): ?CommandLogInterface
    {
        return $this->previousCommandLog;
    }

    public function setPreviousCommandLog(?CommandLogInterface $previousCommandLog = null): void
    {
        $this->previousCommandLog = $previousCommandLog;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $message = \substr(null !== $message ? $message : '', 0, 1000);
        if (false !== $message) {
            $this->message = $message;
        }
    }

    public function getCommandData(): ?array
    {
        return $this->commandData;
    }

    public function setCommandData(?array $commandData): void
    {
        $this->commandData = $commandData;

        $message = isset($this->commandData['__command_message__']) ? $this->commandData['__command_message__'] : null;
        $this->setMessage($message);
    }

    public function getCommandClass(): ?string
    {
        return $this->commandClass;
    }

    public function setCommandClass(?string $commandClass): void
    {
        $this->commandClass = $commandClass;
    }

    public function getCurrentUsername(): ?string
    {
        return $this->currentUsername;
    }

    public function setCurrentUsername(?string $currentUsername): void
    {
        $this->currentUsername = $currentUsername;
    }

    public function getDate(): \DateTimeImmutable
    {
        return strictImmutableDate($this->date);
    }

    public function getRequest(): ?array
    {
        return $this->request;
    }

    public function setRequest(?Request $request = null): void
    {
        if (null === $request) {
            $this->request = null;
            $this->pathInfo = null;
            $this->uri = null;
            $this->clientIp = null;
            $this->sessionId = null;
        } else {
            $this->request = [
                'pathInfo' => $request->getPathInfo(),
                'uri' => $request->getUri(),
                'clientIp' => $request->getClientIp(),
                'clientIps' => $request->getClientIps(),
                'basePath' => $request->getBasePath(),
                'host' => $request->getHost(),
                'languages' => $request->getLanguages(),
                'charsets' => $request->getCharsets(),
                'schemeAndHttpHost' => $request->getSchemeAndHttpHost(),
                'requestUri' => $request->getRequestUri(),
                'realMethod' => $request->getRealMethod(),
                'queryString' => $request->getQueryString(),
                'port' => $request->getPort(),
                'method' => $request->getMethod(),
                'locale' => $request->getLocale(),
                'baseUrl' => $request->getBaseUrl(),
                'query' => $request->query->all(),
                'request' => $request->request->all(),
                'server' => $request->server->all(),
                'files' => $request->files->all(),
            ];
            $this->pathInfo = $request->getPathInfo();
            $this->uri = $request->getUri();
            $this->clientIp = $request->getClientIp();
            if (null !== $request->getSession()) {
                $this->sessionId = $request->getSession()->getId();
            }
        }
    }

    public function setException(\Throwable $exception = null): void
    {
        if (null === $exception) {
            $this->exceptionMessage = null;
            $this->exceptionFullMessage = null;
            $this->exceptionClass = null;
            $this->exceptionData = null;
        } else {
            $this->exceptionMessage = $exception->getMessage();
            $this->exceptionFullMessage = self::exceptionFullMessage($exception);
            $this->exceptionClass = \get_class($exception);
            $this->exceptionData = self::exceptionToArray($exception);
        }
    }

    protected static function exceptionFullMessage(\Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (null !== $exception->getPrevious()) {
            $message .= ' '.self::exceptionFullMessage($exception->getPrevious());
        }

        return $message;
    }

    protected static function exceptionToArray(\Throwable $exception): array
    {
        $array = [
            'exception_class' => \get_class($exception),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'traceAsString' => $exception->getTraceAsString(),
        ];

        if (null !== $exception->getPrevious()) {
            $array['previous'] = self::exceptionToArray($exception->getPrevious());
        }

        return $array;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    public function getPathInfo(): ?string
    {
        return $this->pathInfo;
    }

    public function setPathInfo(?string $pathInfo): void
    {
        $this->pathInfo = $pathInfo;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
    }

    public function getExceptionMessage(): ?string
    {
        return $this->exceptionMessage;
    }

    public function setExceptionMessage(?string $exceptionMessage): void
    {
        $this->exceptionMessage = $exceptionMessage;
    }

    public function getExceptionFullMessage(): ?string
    {
        return $this->exceptionFullMessage;
    }

    public function setExceptionFullMessage(?string $exceptionFullMessage): void
    {
        $this->exceptionFullMessage = $exceptionFullMessage;
    }

    public function getExceptionClass(): ?string
    {
        return $this->exceptionClass;
    }

    public function setExceptionClass(?string $exceptionClass): void
    {
        $this->exceptionClass = $exceptionClass;
    }

    public function getExceptionData(): ?array
    {
        return $this->exceptionData;
    }

    public function setExceptionData(?array $exceptionData): void
    {
        $this->exceptionData = $exceptionData;
    }

    // endregion
}
