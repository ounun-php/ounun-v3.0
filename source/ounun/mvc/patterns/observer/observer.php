<?php
namespace ounun\mvc\patterns\observer;

use ounun\mvc\interfaces\inotification;
use ounun\mvc\interfaces\iobserver;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * A base <code>IObserver</code> implementation.
 *
 * An Observer is an object that encapsulates information
 * about an interested object with a notification method that
 * should be called when an </b>inotification</b> is broadcast.
 *
 * In OununMVC, <b>Observer</b> class assume these responsibilities:
 *
 * - Encapsulate the notification (callback) method of the interested object.
 * - Encapsulate the notification context (this) of the interested object.
 * - Provide methods for setting the interested object' notification method and context.
 * - Provide a method for notifying the interested object.
 *
 * @see view
 * @see notification
 */
class observer implements iobserver
{
    /**
     * The notification (callback) method name
     * @var string
     */
    protected $_notify;

    /**
     * @var mixed
     */
    protected $_context;

    /**
     * Constructor.
     *
     * The notification method on the interested object should take
     * one parameter of type <b>inotification</b>
     *
     * @param string $notify_method The notification (callback) method name of the interested object.
     * @param mixed $notify_context The notification context ($this) of the interested object.
     */
    public function __construct(string $notify_method, $notify_context )
    {
        $this->notify_method_set( $notify_method );
        $this->notify_context_set( $notify_context );
    }

    /**
     * Set the notification method.
     *
     * The notification method should take one parameter of type <b>inotification</b>.
     *
     * @param string $notify_method The notification (callback) method name of the interested object.
     * @return void
     */
    public function notify_method_set($notify_method )
    {
        $this->_notify = $notify_method;
    }

    /**
     * Set the notification context.
     *
     * @param mixed $notify_context The notification context ($this) of the interested object.
     * @return void
     */
    public function notify_context_set($notify_context )
    {
        $this->_context = $notify_context;
    }

    /**
     * Get the notification method.
     *
     * @return string The notification (callback) method name of the interested object.
     */
    private function notify_method_get()
    {
        return $this->_notify;
    }

    /**
     * Get the notification context.
     *
     * @return mixed The notification context ($this) of the interested object.
     */
    private function notify_context_get()
    {
        return $this->_context;
    }

    /**
     * Notify the interested object.
     *
     * @param inotification $notification the <b>inotification</b> to pass to the interested object's notification method
     * @return void
     */
    public function notify_observer(inotification $notification )
    {
        $interested = $this->notify_context_get();
        $method     = $this->notify_method_get();

        $interested->$method($notification);
    }

    /**
     * Compare the given object to the notificaiton context object.
     *
     * @param object $object the object to compare.
     * @return bool Boolean indicating if the notification context and the object are the same.
     */
     public function notify_context_compare($object )
     {
        return ($object === $this->_context);
     }
}
