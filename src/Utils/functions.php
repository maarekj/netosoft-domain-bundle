<?php

namespace Netosoft\DomainBundle\Utils;

/**
 * @param \DateTimeInterface|null $date
 *
 * @return \DateTimeImmutable|null
 */
function immutableDate(\DateTimeInterface $date = null)
{
    if ($date === null) {
        return null;
    } else {
        $return = \DateTimeImmutable::createFromFormat(\DateTime::RFC2822, $date->format(\DateTime::RFC2822));

        return $return === false ? null : $return;
    }
}
