<?php
namespace ounun\mvc\interfaces;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * The interface definition for a PureMVC View.
 *
 * In PureMVC, <b>iview</b> implementors assume these responsibilities:
 *
 * In PureMVC, the <b>View</b> class assumes these responsibilities:
 *
 * - Maintain a cache of <b>imediator</b> instances.
 * - Provide methods for registering, retrieving, and removing <b>imediators</b>.
 * - Managing the observer lists for each <b>inotification</b> in the application.
 * - Providing a method for attaching <b>IObservers</b> to an <b>inotification</b>'s observer list.
 * - Providing a method for broadcasting an <b>inotification</b>.
 * - Notifying the <b>IObservers</b> of a given <b>inotification</b> when it broadcast.
 *
 * @see imediator
 * @see iobserver
 * @see inotification
 * @package org.puremvc.php.multicore
 *
 */
interface iview
{

    /**
     * Register Observer
     *
     * Register an <b>IObserver</b> to be notified
     * of <b>inotifications</b> with a given name.
     *
     * @param string $notification_name The name of the <b>inotifications</b> to notify this <b>IObserver</b> of.
     * @param iobserver $observer The <b>IObserver</b> to register.
     * @return void
     */
    public function observer_register(string $notification_name, iobserver $observer );

    /**
     * Remove Observer
     *
     * Remove a group of observers from the observer list for a given Notification name.
     *
     * @param string $notification_name Which observer list to remove from.
     * @param mixed $notify_context Remove the observers with this object as their notifyContext
     * @return void
     */
    public function observer_remove(string $notification_name, $notify_context );

    /**
     * Notify Observers
     *
     * Notify the <b>IObservers</b> for a particular <b>inotification</b>.
     *
     * All previously attached <b>IObservers</b> for this <b>inotification</b>'s
     * list are notified and are passed a reference to the <b>inotification</b> in
     * the order in which they were registered.
     *
     * @param inotification $note The <b>inotification</b> to notify <b>IObservers</b> of.
     * @return void
     */
    public function notify_observers(inotification $notification );

    /**
     * Register Mediator
     *
     * Register an <b>imediator</b> instance with the <b>View</b>.
     *
     * Registers the <b>imediator</b> so that it can be retrieved by name,
     * and further interrogates the <b>imediator</b> for its
     * <b>inotification</b> interests.
     *
     * If the <b>imediator</b> returns any <b>inotification</b>
     * names to be notified about, an <b>Observer</b> is created encapsulating
     * the <b>imediator</b> instance's <b>handleNotification</b> method
     * and registering it as an <b>Observer</b> for all <b>inotifications</b> the
     * <b>imediator</b> is interested in.
     *
     * @param imediator $mediator Reference to the <b>imediator</b> instance.
     * @return void
     */
    public function mediator_register(imediator $mediator );

    /**
     * Retreive Mediator
     *
     * Retrieve a previously  registered <b>imediator</b> instance from the <b>View</b>.
     *
     * @param string $mediator_name Name of the <b>imediator</b> instance to retrieve.
     * @return imediator The <b>imediator</b> previously registered with the given <var>mediatorName</var>.
     */
    public function mediator_retrieve(string $mediator_name );

    /**
     * Remove Mediator
     *
     * Remove a previously registered <b>imediator</b> instance from the <b>View</b>.
     *
     * @param string $mediator_name Name of the <b>imediator</b> instance to be removed.
     * @return imediator The <b>imediator</b> instance previously registered with the given <var>mediatorName</var>.
     */
    public function mediator_remove(string $mediator_name );

    /**
     * Has Mediator
     *
     * Check if a <b>imediator</b> is registered or not.
     *
     * @param string $mediator_name The name of the <b>imediator</b> to check for.
     * @return bool Boolean: Whether a <b>imediator</b> is registered with the given <var>mediatorName</var>.
     */
    public function mediator_has(string $mediator_name );

}
