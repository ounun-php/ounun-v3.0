<?php
namespace ounun\mvc\interfaces;
/**
 * OununMVC Multicore Port to PHP
 */

/**
 * The interface definition for a OununMVC Notification.
 *
 * OununMVC does not rely upon underlying event models such
 * as the one provided with others like Flash, and PHP does
 * not have an inherent event model.
 *
 * The Observer Pattern as implemented within OununMVC exists
 * to support event-driven communication between the
 * application and the actors of the MVC triad.
 *
 * OununMVC <b>notification</b>s follow a 'Publish/Subscribe'
 * pattern. OununMVC classes need not be related to each other in a
 * parent/child relationship in order to communicate with one another
 * using <b>notification</b>s.
 *
 * @see iview
 * @see iobserver
 * @package org.OununMVC.php.multicore
 *
 */
interface inotification
{
    /**
     * Name getter
     *
     * Get the name of the <b>inotification</b> instance.
     *
     * No setter, should be set by constructor only
     *
     * @return string Name of the <b>inotification</b> instance.
     */
    public function name_get();

    /**
     * Body setter
     *
     * Set the body of the <b>inotification</b> instance.
     *
     * @param object $body The body of the <b>inotification</b> instance.
     * @return void
     */
    public function body_set($body );

    /**
     * Body getter
     *
     * Get the body of the <b>inotification</b> instance.
     *
     * @return mixed The body of the <b>inotification</b> instance.
     */
    public function body_get();

    /**
     * Type setter
     * Set the type of the <b>inotification</b> instance.
     *
     * @param string $type The type of the <b>inotification</b> instance.
     * @return void
     */
    public function type_set(string $type );

    /**
     * Type getter
     *
     * Get the type of the <b>inotification</b> instance.
     *
     * @return string The type of the <b>inotification</b> instance.
     */
    public function type_get();

    /**
     * String representation
     * Get the string representation of the <b>inotification</b> instance
     *
     * @return string
     */
    public function toString();

}
