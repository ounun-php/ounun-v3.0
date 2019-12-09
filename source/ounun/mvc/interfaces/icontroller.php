<?php
namespace ounun\mvc\interfaces;
/**
 * OununMVC Multicore Port to PHP
 */


/**
 * The interface definition for a OununMVC Controller.
 *
 * In OununMVC, an <b>icontroller</b> implementor
 * follows the 'Command and Controller' strategy, and
 * assumes these responsibilities:
 *
 * - Remembering which <b>icommand</b>s are intended to handle
 *   which <b>inotifications</b>.
 * - Registering itself as an <b>IObserver</b> with the <b>iview</b>
 *   for each <b>inotification</b> that it has an <b>icommand</b>
 *   mapping for.
 * - Creating a new instance of the proper <b>icommand</b> to handle
 *   a given <b>inotification</b> when notified by the <b>iview</b>.
 * - Calling the <b>icommand</b>'s <b>execute</b> method, passing
 *   in the <b>inotification</b>.
 *
 * @see inotification
 * @see icommand
 *
 */
interface icontroller
{
    /**
     * Register Command
     *
     * Register a particular <b>icommand</b> class as the handler
     * for a particular <b>inotification</b>.
     *
     * @param string $notification_name Name of the <b>inotification</b>.
     * @param string $command_class_name Class name of the <b>icommand</b> implementation to register.
     * @return void
     */
    public function register(string $notification_name, string $command_class_name);

    /**
     * Execute Command
     *
     * Execute the <b>icommand</b> previously registered as the
     * handler for <b>inotification</b>s with the given notification name.
     *
     * @param inotification $notification The <b>inotification</b> to execute the associated <b>icommand</b> for.
     * @return void
     */
    public function execute(inotification $notification );

    /**
     * Remove Command
     *
     * Remove a previously registered <b>icommand</b> to <b>inotification</b> mapping.
     *
     * @param string $notification_name Name of the <b>inotification</b> to remove the <b>icommand</b> mapping for.
     * @return void
     */
    public function remove(string $notification_name );

    /**
     * Has Command
     *
     * Check if a <b>command</b> is registered for a given <b>inotification</b>
     *
     * @param string $notification_name Name of the <b>inotification</b> to check for.
     * @return bool Boolean: Whether a <b>command</b> is currently registered for the given <var>notificationName</var>.
     */
    public function has(string $notification_name );

}
