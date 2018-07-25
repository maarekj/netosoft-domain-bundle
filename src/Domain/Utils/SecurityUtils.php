<?php

namespace Netosoft\DomainBundle\Domain\Utils;

use Netosoft\DomainBundle\Domain\Exception\NotLoggedException;
use Netosoft\DomainBundle\Domain\Exception\UngrantedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityUtils
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     * Throw exception if attributes not granted.
     *
     * @param mixed $attributes
     * @param mixed $object
     *
     * @throws UngrantedException
     */
    public function isGrantedOrThrow($attributes, $object = null)
    {
        if (false === $this->authorizationChecker->isGranted($attributes, $object)) {
            throw new UngrantedException();
        }
    }

    /**
     * @return UserInterface
     *
     * @throws NotLoggedException
     */
    public function getAppUserOrThrow()
    {
        $user = $this->getAppUser();
        if (null === $user) {
            throw new NotLoggedException();
        }

        return $user;
    }

    /**
     * @return UserInterface|null
     */
    public function getAppUser()
    {
        $token = $this->tokenStorage->getToken();
        $user = null === $token ? null : $token->getUser();

        if ($user instanceof UserInterface) {
            return $user;
        }

        return null;
    }
}
