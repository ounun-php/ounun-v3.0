<?php
namespace ounun\mvc\interfaces;
/**
 * PureMVC Multicore Port to PHP
 */

/**
 * The interface definition for a PureMVC Notifier.
 *
 * <b>MacroCommand, SimpleCommand, Mediator</b> and <b>proxy</b>
 * all have a need to send <b>Notifications</b>.
 *
 * The <b>INotifier</b> interface provides a common method called
 * <b>sendNotification</b> that relieves implementation code of
 * the necessity to actually construct <b>Notifications</b>.
 *
 * The <b>Notifier</b> class, which all of the above mentioned classes
 * extend, also provides an initialized reference to the <b>Facade</b>
 * Singleton, which is required for the convienience method
 * for sending <b>Notifications</b>, but also eases implementation as these
 * classes have frequent <b>Facade</b> interactions and usually require
 * access to the facade anyway.
 *
 * @see ifacade
 * @see inotification
 * @package org.puremvc.php.multicore
 *
 */
interface inotifier
{
    /**
     * Send a <b>inotifier</b>.
     *
     * Convenience method to prevent having to construct new
     * notification instances in our implementation code.
     *
     * @param string $notification_name The name of the notification to send.
     * @param mixed  $body The body of the notification (optional).
     * @param string $type The type of the notification (optional).
     * @return void
     */
    public function send(string $notification_name, $body=null, string $type= '' );

    /**
     * Initialize this <b>inotifier</b> instance.
     *
     * This is how a Notifier gets its multitonKey.
     * Calls to sendNotification or to access the
     * facade will fail until after this method
     * has been called.
     *
     * @param string $core_tag The multitonKey for this <b>inotifier</b> to use.
     */
    public function initialize(string $core_tag );

}
