<?php

namespace Netosoft\DomainBundle\Action;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResolverHelper
{
    /** @var ActionHelper */
    private $helper;

    public function __construct(ActionHelper $helper)
    {
        $this->helper = $helper;
    }

    public function defineCommandFormOptions(OptionsResolver $resolver, string $key)
    {
        $resolver
            ->setDefault($key, [])
            ->setAllowedTypes($key, ['array', 'callable'])
            ->setNormalizer($key, function (Options $options, $value) {
                return \is_array($value) ? $this->createIdentity($value) : $value;
            });
    }

    public function defineCommandForm(OptionsResolver $resolver, string $key)
    {
        $resolver
            ->setRequired($key)
            ->setAllowedTypes($key, ['string', 'callable'])
            ->setNormalizer($key, function (Options $options, $value) {
                return \is_string($value) ? $this->createIdentity($value) : $value;
            });
    }

    public function defineCommand(OptionsResolver $resolver, string $key)
    {
        $resolver
            ->setRequired($key)
            ->setAllowedTypes($key, ['string', 'array', 'callable'])
            ->setNormalizer($key, function (Options $options, $value) {
                return $this->createCommand($value);
            });
    }

    /**
     * @param string|array|callable $value
     *
     * @return callable
     */
    public function createCommand($value)
    {
        if (\is_string($value) && \class_exists($value)) {
            return function () use ($value) {
                return new $value();
            };
        }

        if (\is_array($value) && 'from_object' === $value['strategy']) {
            return function ($options, $args) use ($value) {
                $object = $args['object'];

                return new $value['class']($object);
            };
        }

        if (\is_array($value) && 'from_parent_admin' === $value['strategy']) {
            return function ($options, $args) use ($value) {
                /** @var AdminInterface $admin */
                $admin = $options['admin'];
                $parentAdmin = $admin->getParent();
                $parentEntity = null !== $parentAdmin ? $parentAdmin->getSubject() : null;

                return new $value['class']($parentEntity);
            };
        }

        if (\is_callable($value)) {
            return $value;
        }

        throw new InvalidArgumentException();
    }

    public function defineSuccessResponse(OptionsResolver $resolver, string $key, $default)
    {
        $resolver
            ->setDefault($key, $default)
            ->setAllowedTypes($key, ['string', 'callable'])
            ->setNormalizer($key, function (Options $options, $value) {
                return $this->createSuccessResponse($value);
            });
    }

    /**
     * @param string|callable $value
     *
     * @return callable
     */
    public function createSuccessResponse($value)
    {
        if ('redirect_list' === $value) {
            return function ($options, $args) {
                /** @var AdminInterface $admin */
                $admin = $options['admin'];

                return new RedirectResponse($admin->generateUrl('list'));
            };
        } elseif ('redirect_edit' === $value) {
            return function ($options, $args) {
                /** @var AdminInterface $admin */
                $admin = $options['admin'];
                $returned = $args['returned'];

                return new RedirectResponse($admin->generateObjectUrl('edit', $returned));
            };
        }

        if (\is_callable($value)) {
            return $value;
        }

        throw new InvalidArgumentException('value must be "redirect_list", "redirect_edit" or callable.');
    }

    public function defineGetObject(OptionsResolver $resolver, string $key, $default = null)
    {
        $resolver
            ->setDefault($key, $default)
            ->setAllowedTypes($key, \array_filter([null === $default ? 'null' : null, 'string', 'callable']))
            ->setNormalizer($key, function (Options $options, $value) {
                return $this->createGetObject($value);
            });
    }

    /**
     * @param string|callable $value
     *
     * @return callable
     */
    public function createGetObject($value)
    {
        if ('from_request' === $value) {
            return function ($options) {
                return $this->helper->getAdminObjectOrNotFound($options['request'], $options['admin']);
            };
        }

        if (\is_callable($value)) {
            return $value;
        }

        throw new InvalidArgumentException('value must be "from_request" or callable.');
    }

    private function createIdentity($value): callable
    {
        return function () use ($value) {
            return $value;
        };
    }
}
