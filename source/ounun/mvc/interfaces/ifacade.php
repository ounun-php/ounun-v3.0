<?php
namespace ounun\mvc\interfaces;
/**
 * OununMVC Multicore Port to PHP
 */

/**
 * The interface definition for a OununMVC Facade.
 *
 * The <b>Facade</b> Pattern suggests providing a single
 * class to act as a central point of communication
 * for a subsystem.
 *
 * In OununMVC, the <b>Facade</b> acts as an interface between
 * the core MVC actors (Model, View, Controller) and
 * the rest of your application.
 *
 * @see imodel
 * @see iview
 * @see icontroller
 * @see icommand
 * @see inotification
 */
interface ifacade extends inotifier
{

    /**
     * Register Proxy
     *
     * Register an <b>iproxy</b> with the <b>Model</b>.
     *
     * @param iproxy $proxy The <b>iproxy</b> to be registered with the <b>Model</b>.
     * @return void
     */
    public function proxy_register(iproxy $proxy );

    /**
     * Retrieve Proxy
     *
     * Retrieve a previously registered <b>iproxy</b> from the <b>Model</b> by name.
     *
     * @param string $proxy_name Name of the <b>iproxy</b> instance to be retrieved.
     * @return iproxy The <b>iproxy</b> previously regisetered by <var>proxyName</var> with the <b>Model</b>.
     */
    public function proxy_retrieve(string $proxy_name );

    /**
     * Has Proxy
     *
     * Check if a Proxy is registered for the given <var>proxyName</var>.
     *
     * @param string $proxy_name Name of the <b>proxy</b> to check for.
     * @return bool Boolean: Whether a <b>proxy</b> is currently registered with the given <var>proxyName</var>.
     */
    public function proxy_has(string $proxy_name );

    /**
     * Remove Proxy
     *
     * Remove a previously registered <b>iproxy</b> instance from the <b>Model</b> by name.
     *
     * @param string $proxy_name Name of the <b>iproxy</b> to remove from the <b>Model</b>.
     * @return iproxy The <b>iproxy</b> that was removed from the <b>Model</b>.
     */
    public function proxy_remove(string $proxy_name );


    /**
     * Register Command
     *
     * Register an <b>icommand</b> with the <b>Controller</b>.
     *
     * @param string $notification_name Name of the <b>inotification</b>
     * @param string $command_class_name <b>icommand</b> object to register. Can be an object OR a class name.
     * @return void
     */
    public function command_register(string $notification_name, string $command_class_name );

    /**
     * Remove Command
     *
     * Remove a previously registered <b>icommand</b> to <b>inotification</b> mapping.
     *
     * @param string $notification_name Name of the <b>inotification</b> to remove the <b>icommand</b> mapping for.
     */
    public function command_remove(string $notification_name );

    /**
     * Has Command
     *
     * Check if a <b>command</b> is registered for a given <b>notification</b>
     *
     * @param string $notification_name Name of the <b>inotification</b> to check for.
     * @return bool Whether a <b>command</b> is currently registered for the given <var>notificationName</var>.
     */
    public function command_has(string $notification_name );

    /**
     * Notify <b>Observer</b>s.
     *
     * This method is left public mostly for backward
     * compatibility, and to allow you to send custom
     * notification classes using the facade.
     *
     * Usually you should just call sendNotification
     * and pass the parameters, never having to
     * construct the notification yourself.
     *
     * @param inotification $notification The <b>inotification</b> to have the <b>View</b> notify <b>Observers</b> of.
     * @return void
     */
    public function notify_observers(inotification $notification );

    /**
     * Register Mediator
     *
     * Register an <b>imediator</b> instance with the <b>View</b>.
     *
     * @param imediator $mediator Reference to the <b>imediator</b> instance.
     */
    public function mediator_register(imediator $mediator );

    /**
     * Retrieve Mediator
     *
     * Retrieve a previously registered <b>imediator</b> instance from the <b>View</b>.
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
