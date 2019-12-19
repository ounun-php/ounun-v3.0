<?php
namespace ounun\mvc\core;

use ounun\mvc\interfaces\icommand;
use ounun\mvc\interfaces\icontroller;
use ounun\mvc\interfaces\inotification;
use ounun\mvc\interfaces\iview;
use ounun\mvc\patterns\observer\observer;

/**
 * OununMVC Multicore Port to PHP
 */


/**
 * A Multiton <b>icontroller</b> implementation.
 *
 * In PureMVC, the <b>Controller</b> class follows the
 * 'Command and Controller' strategy, and assumes these
 * responsibilities:
 *
 * - Remembering which <b>icommand</b>s  are intended to
 *   handle which <b>inotifications</b>.
 * - Registering itself as an <b>IObserver</b> with
 *   the <b>View</b> for each <b>inotification</b>
 *   that it has an <b>icommand</b> mapping for.
 * - Creating a new instance of the proper <b>icommand</b>
 *   to handle a given <b>inotification</b> when notified
 *   by the <b>View</b>.
 * - Calling the <b>icommand</b>'s <b>execute</b> method,
 *   passing in the <b>inotification</b>.
 *
 * Your application must register <b>icommands</b> with the
 * Controller.
 *
 * The simplest way is to subclass <b>Facade</b>,
 * and use its <b>initializeController</b> method to add your
 * registrations.
 *
 * @see macro_command
 * @see simple_command
 * @see notification
 * @see observer
 * @see view
 */
class controller implements icontroller
{
    /**
     * Define the message content for the duplicate instance exception
     * @var string
     */
    const Multiton_Msg = "Controller instance for this Multiton key already constructed!";

    /**
     * The view instance for this Core
     * @var iview
     */
    protected $_view;

    /**
     * Mapping of Notification names to Command Class references
     * @var array
     */
    protected $_command_map;

    /**
     * The Multiton Key for this Core
     * @var string
     */
    protected $_multiton_key;

    /**
     * The Multiton instances stack
     * @var array
     */
    protected static $_instance_map = [];

    /**
     * Instance constructor
     *
     * This <b>icontroller</b> implementation is a Multiton,
     * so you should not call the constructor
     * directly, but instead call the static <b>i() Factory</b> method,
     * passing the unique key for this instance.
     *
     * ex:
     * <code>
     * $myController = MyController::i( 'myMultitonKey' );
     * </code>
     *
     * @param string $key Unique key for this instance.
     * @throws \Exception if instance for this key has already been constructed.
     */
    protected function __construct( $key )
    {
        if ( isset( self::$_instance_map[ $key ] ) )
        {
            throw new \Exception(self::Multiton_Msg);
        }
        $this->_multiton_key = $key;
        $this->_command_map  = [];
        self::$_instance_map[ $this->_multiton_key ] = $this;
        $this->initialize();
    }

    /**
     * Initialize the instance.
     *
     * Called automatically by the constructor.
     *
     * Note that if you are using a subclass of <b>View</b> in your application,
     * you should <i>also</i> subclass <b>Controller</b> and override the <i>initializeController()</i>
     * method in the following way:
     *
     * <code>
     * // ensure that the Controller is talking to my IView implementation
     * public function initializeController( )
     * {
     *     $this->view = MyView::i('myViewName');
     * }
     * </code>
     *
     * @return void
     */
    protected function initialize(  )
    {
        $this->_view = view::i( $this->_multiton_key );
    }

    /**
     * Controller Factory method.
     *
     * This <b>icontroller</b> implementation is a Multiton so
     * this method MUST be used to get acces, or create, <b>icontroller</b>s.
     *
     * @param string $key Unique key for this instance.
     * @return icontroller The instance for this Multiton key.
     * @throws
     */
    public static function i($key )
    {
        if ( !isset( self::$_instance_map[ $key ] ) )
        {
            self::$_instance_map[ $key ] = new Controller( $key );
        }

        return self::$_instance_map[ $key ];
    }

    /**
     * Execute Command
     *
     * Execute the <b>icommand</b> previously registered as the
     * handler for <b>inotification</b>s with the given notification name.
     *
     * @param inotification $notification The <b>inotification</b> to execute the associated <b>Command</b> for.
     * @return void
     */
    public function execute(inotification $notification )
    {
        // if the Command is registered...
        if( $this->has( $notification->name_get() ) )
        {
            $command_class_name = $this->_command_map[ $notification->name_get() ];
            /** @var icommand $command_class_ref */
            $command_class_ref    = new $command_class_name();
            $command_class_ref->initialize($this->_multiton_key);
            $command_class_ref->execute( $notification );
        }
    }

    /**
     * Register Command
     *
     * Register a particular <b>icommand</b> class as the handler
     * for a particular <b>inotification</b>.
     *
     * If an <b>icommand</b> has already been registered to
     * handle <b>inotification</b>s with this name, it is no longer
     * used, the new <b>icommand</b> is used instead.
     *
     * The <b>IObserver</b> for the new <b>icommand</b> is only created if this the
     * first time an <b>icommand</b>has been regisered for this <b>inotification</b> name.
     *
     * @param string $notification_name Name of the <b>inotification</b>.
     * @param string $command_class_name Class name of the <b>icommand</b> implementation to register.
     * @return void
     */
    public function register($notification_name, $command_class_name )
    {
        if ( !$this->has( $notification_name ) ) {
            $this->_view->observer_register( $notification_name, new observer( "executeCommand", $this ) );
        }
        $this->_command_map[ $notification_name ] = $command_class_name;
    }

    /**
     * Has Command
     *
     * Check if a <b>icommand</b> is <b>registerCommand() registered</b> for a given <b>inotification</b>
     *
     * @param string $notification_name Name of the <b>inotification</b> to check for.
     * @return bool Whether a <b>icommand</b> is currently registered for the given <var>notificationName</var>.
     */
    public function has($notification_name )
    {
        return isset( $this->_command_map[ $notification_name ] );
    }

    /**
     * Remove Command
     *
     * Remove a previously <b>registerCommand() registered</b> <b>icommand</b> to <b>inotification</b> mapping.
     *
     * @param string $notification_name Name of the <b>inotification</b> to remove the <b>icommand</b> mapping for.
     * @return void
     */
    public function remove($notification_name )
    {
        // if the Command is registered...
        if ( $this->has( $notification_name ) )
        {
            // remove the observer
            $this->_view->observer_remove( $notification_name, $this );

            // remove the command
            unset( $this->_command_map[ $notification_name ] );
        }
    }

    /**
     * Remove controller
     *
     * Remove an <b>icontroller</b> instance identified by it's <b>key</b>
     *
     * @param string $key multitonKey of <b>icontroller</b> instance to remove
     * @return void
     */
    public static function controller_remove($key )
    {
        unset( self::$_instance_map[ $key ] );
    }

}
