<?php
namespace ounun\mvc\interfaces;
/**
 * OununMVC Multicore Port to PHP
 */

/**
 * The interface definition for a PureMVC Observer.
 *
 * In PureMVC, <b>IObserver</b> implementors assume these responsibilities:
 *
 * - Encapsulate the notification (callback) method of the interested object.
 * - Encapsulate the notification context (this) of the interested object.
 * - Provide methods for setting the interested object' notification method and context.
 * - Provide a method for notifying the interested object.
 *
 * PureMVC does not rely upon underlying event
 * models and PHP does not have an inherent
 * event model.
 *
 * The Observer Pattern as implemented within
 * PureMVC exists to support event driven communication
 * between the application and the actors of the
 * MVC triad.
 *
 * An Observer is an object that encapsulates information
 * about an interested object with a notification method that
 * should be called when an </b>inotification</b> is broadcast.
 * The Observer then acts as a proxy for notifying the interested object.
 *
 * Observers can receive <b>notification</b>s by having their
 * <b>notifyObserver</b> method invoked, passing
 * in an object implementing the <b>inotification</b> interface, such
 * as a subclass of <b>notification</b>.
 *
 * @see iview
 * @see inotification
 * @package org.puremvc.php.multicore
 *
 */
interface iobserver
{
    /**
     * Set the notification method.
     * The notification method should take one parameter of type <b>inotification</b>.
     *
     * @param string $notify_method The notification (callback) method name of the interested object.
     * @return void
     */
    public function notify_method_set(string $notify_method );

    /**
     * Set the notification context.
     *
     * @param mixed $notify_context The notification context ($this) of the interested object.
     * @return void
     */
    public function notify_context_set($notify_context );

    /**
     * Compare the given object to the notificaiton context object.
     *
     * @param mixed $object the object to compare.
     * @return bool Boolean indicating if the notification context and the object are the same.
     */
    public function notify_context_compare($object );

    /**
     * Notify the interested object.
     *
     * @param inotification $notification the <b>inotification</b> to pass to the interested object's notification method
     * @return void
     */
    public function notify_observer(inotification $notification );
}
