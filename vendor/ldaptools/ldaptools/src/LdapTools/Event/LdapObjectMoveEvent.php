<?php
/**
 * This file is part of the LdapTools package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LdapTools\Event;

use LdapTools\Object\LdapObject;

/**
 * An event for when a LDAP object is moved.
 *
 * @author Chad Sikorra <Chad.Sikorra@gmail.com>
 */
class LdapObjectMoveEvent extends LdapObjectEvent
{
    use MoveEventTrait;

    /**
     * @param string $eventName
     * @param LdapObject $object
     * @param string $container
     */
    public function __construct($eventName, LdapObject $object, $container)
    {
        $this->container = $container;
        parent::__construct($eventName, $object);
    }
}
