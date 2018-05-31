<?php

namespace Netosoft\DomainBundle\Entity;

use function Netosoft\DomainBundle\Utils\immutableDate;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;

/**
 * BaseCommandLog.
 *
 * @ORM\MappedSuperclass()
 */
abstract class BaseCommandLog implements CommandLogInterface
{
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
     * @var string|null
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

    /** {@inheritdoc} */
    public function getPreviousCommandLog()
    {
        return $this->previousCommandLog;
    }

    /** {@inheritdoc} */
    public function setPreviousCommandLog(CommandLogInterface $previousCommandLog = null)
    {
        $this->previousCommandLog = $previousCommandLog;

        return $this;
    }

    /** {@inheritdoc} */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /** {@inheritdoc} */
    public function getType()
    {
        return $this->type;
    }

    /** {@inheritdoc} */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /** {@inheritdoc} */
    public function getMessage()
    {
        return $this->message;
    }

    /** {@inheritdoc} */
    public function setMessage($message)
    {
        $this->message = \substr($message, 0, 1000);

        return $this;
    }

    /** {@inheritdoc} */
    public function getCommandData()
    {
        return $this->commandData;
    }

    /** {@inheritdoc} */
    public function setCommandData($commandData)
    {
        $this->commandData = $commandData;

        $message = isset($this->commandData['__command_message__']) ? $this->commandData['__command_message__'] : null;
        $this->setMessage($message);

        return $this;
    }

    /** {@inheritdoc} */
    public function getCommandClass()
    {
        return $this->commandClass;
    }

    /** {@inheritdoc} */
    public function setCommandClass($commandClass)
    {
        $this->commandClass = $commandClass;

        return $this;
    }

    /** {@inheritdoc} */
    public function getCurrentUsername()
    {
        return $this->currentUsername;
    }

    /** {@inheritdoc} */
    public function setCurrentUsername($currentUsername)
    {
        $this->currentUsername = $currentUsername;

        return $this;
    }

    /** {@inheritdoc} */
    public function getDate(): \DateTimeImmutable
    {
        return immutableDate($this->date);
    }

    /** {@inheritdoc} */
    public function getRequest()
    {
        return $this->request;
    }

    /** {@inheritdoc} */
    public function setRequest(Request $request = null)
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

        return $this;
    }

    /** {@inheritdoc} */
    public function setException(\Throwable $exception = null)
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

        return $this;
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
            'trace' => $exception->getTrace(),
            'traceAsString' => $exception->getTraceAsString(),
        ];

        if (null !== $exception->getPrevious()) {
            $array['previous'] = self::exceptionToArray($exception->getPrevious());
        }

        return $array;
    }

    /** {@inheritdoc} */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /** {@inheritdoc} */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }

    /** {@inheritdoc} */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /** {@inheritdoc} */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /** {@inheritdoc} */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /** {@inheritdoc} */
    public function setPathInfo($pathInfo)
    {
        $this->pathInfo = $pathInfo;

        return $this;
    }

    /** {@inheritdoc} */
    public function getUri()
    {
        return $this->uri;
    }

    /** {@inheritdoc} */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /** {@inheritdoc} */
    public function getExceptionMessage()
    {
        return $this->exceptionMessage;
    }

    /** {@inheritdoc} */
    public function setExceptionMessage($exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;

        return $this;
    }

    /** {@inheritdoc} */
    public function getExceptionFullMessage()
    {
        return $this->exceptionFullMessage;
    }

    /** {@inheritdoc} */
    public function setExceptionFullMessage($exceptionFullMessage)
    {
        $this->exceptionFullMessage = $exceptionFullMessage;

        return $this;
    }

    /** {@inheritdoc} */
    public function getExceptionClass()
    {
        return $this->exceptionClass;
    }

    /** {@inheritdoc} */
    public function setExceptionClass($exceptionClass)
    {
        $this->exceptionClass = $exceptionClass;

        return $this;
    }

    /** {@inheritdoc} */
    public function getExceptionData()
    {
        return $this->exceptionData;
    }

    /** {@inheritdoc} */
    public function setExceptionData($exceptionData)
    {
        $this->exceptionData = $exceptionData;

        return $this;
    }

    // endregion
}
