<?php
namespace ounun\mvc\patterns\observer;
use ounun\mvc\interfaces\ifacade;
use ounun\mvc\interfaces\inotifier;
use ounun\mvc\patterns\facade;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * A Base <code>INotifier</code> implementation.
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
 * <b>NOTE:</b><br>
 * In the MultiCore version of the framework, there is one caveat to
 * notifiers, they cannot send notifications or reach the facade until they
 * have a valid multitonKey.
 *
 * The multitonKey is set:
 *  - on a Command when it is executed by the Controller
 *  - on a Mediator is registered with the View
 *  - on a Proxy is registered with the Model.
 *
 * @see proxy
 * @see facade
 * @see mediator
 * @see macro_command
 * @see simple_command
 */
class notifier implements inotifier
{
    /**
     * Define the message content for the inexistant instance exception
     * @var string
     */
    const Multiton_Msg = "multitonKey for this Notifier not yet initialized!";

    /**
     * The Multiton Key for this Core
     * @var string
     */
    protected $_core_tag = '';


    /**
     * Send a <b>inotification</b>.
     *
     * Convenience method to prevent having to construct new
     * notification instances in our implementation code.
     *
     * @param string $notification_name The name of the notification to send.
     * @param mixed $body The body of the notification (optional).
     * @param string $type The type of the notification (optional).
     * @return void
     * @throws
     */
    public function send($notification_name, $body=null, $type= '' )
    {
        if ( !is_null( $this->facade() ) ) {
            $this->facade()->send( $notification_name, $body, $type );
        }
    }

    /**
     * Initialize this <b>INotifier</b> instance.
     *
     * This is how a Notifier gets its multitonKey.
     * Calls to sendNotification or to access the
     * facade will fail until after this method
     * has been called.
     *
     * Mediators, Commands or Proxies may override
     * this method in order to send notifications
     * or access the Multiton Facade instance as
     * soon as possible. They CANNOT access the facade
     * in their constructors, since this method will not
     * yet have been called.
     *
     * @param string $core_tag The multitonKey for this <b>inotifier</b> to use.
     * @return void
     */
    public function initialize(string $core_tag )
    {
        $this->_core_tag = $core_tag;
    }

    /**
     * Return the Multiton Facade instance
     *
     * @throws \Exception if multitonKey for this Notifier is not yet initialized.
     * @return ifacade The Facade instance for this Notifier multitonKey.
     * @throws
     */
    protected function facade()
    {
        if ( !isset( $this->_core_tag ) ) {
            throw new \Exception( self::Multiton_Msg );
        }
        return facade::i( $this->_core_tag );
    }

}
