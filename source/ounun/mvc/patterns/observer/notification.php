<?php
namespace ounun\mvc\patterns\observer;
use ounun\mvc\interfaces\inotification;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * A base <b>inotification</b> implementation.
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
 * @see Observer
        org\OununMVC\php\multicore\patterns\observer\Observer.php
 * @package org.puremvc.php.multicore
 *
 */
class notification implements inotification
{

    /**
     * Name of the notification instance
     * @var string
     */
    protected $_name = '';

    /**
     * The type of the notification instance
     * @var string
     */
    protected $_type = '';

    /**
     * The body of the notification instance
     * @var mixed
     */
    protected $_body;

    /**
     * Constructor.
     *
     * @param string $name The name of the notification to send.
     * @param mixed $body The body of the notification (optional).
     * @param string $type The type of the notification (optional).
     */
    public function __construct(string $name, $body=null, string $type='' )
    {
        $this->_name = $name;
        $this->_body = $body;
        $this->_type = $type;
    }

    /**
     * Name getter
     * Get the name of the <b>notification</b> instance.
     * No setter, should be set by constructor only
     *
     * @return string Name of the <b>notification</b> instance.
     */
    public function name_get()
    {
        return $this->_name;
    }

    /**
     * Body setter
     * Set the body of the <b>notification</b> instance.
     *
     * @param object $body The body of the <b>notification</b> instance.
     * @return void
     */
    public function body_set($body )
    {
        $this->_body = $body;
    }

    /**
     * Body getter
     * Get the body of the <b>notification</b> instance.
     *
     * @return mixed The body of the <b>notification</b> instance.
     */
    public function body_get()
    {
        return $this->_body;
    }

    /**
     * Type setter
     * Set the type of the <b>notification</b> instance.
     *
     * @param string $type The type of the <b>notification</b> instance.
     * @return void
     */
    public function type_set(string $type )
    {
        $this->_type = $type;
    }

    /**
     * Type getter
     * Get the type of the <b>notification</b> instance.
     *
     * @return string The type of the <b>notification</b> instance.
     */
    public function type_get()
    {
        return $this->_type;
    }

    /**
     * String representation
     * Get the string representation of the <b>inotification</b> instance
     *
     * @return string
     */
    public function toString()
    {
        $msg = "Notification Name: " . $this->name_get();
        $msg .= "\nBody:".( is_null( $this->_body ) ? "null" : (is_array($this->_body)? 'Array': $this->_body) );
        $msg .= "\nType:".( is_null( $this->_type ) ? "null" : $this->_type );
        return $msg;
    }

}
