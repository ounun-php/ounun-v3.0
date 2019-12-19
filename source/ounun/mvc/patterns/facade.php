<?php
namespace ounun\mvc\patterns;


use ounun\mvc\core\controller;
use ounun\mvc\core\model;
use ounun\mvc\core\view;
use ounun\mvc\interfaces\icontroller;
use ounun\mvc\interfaces\ifacade;
use ounun\mvc\interfaces\imediator;
use ounun\mvc\interfaces\inotification;
use ounun\mvc\interfaces\iproxy;
use ounun\mvc\patterns\observer\notification;

class facade implements ifacade
{
    /**
     * Define the message content for the duplicate instance exception
     * @var string
     */
    const Multiton_Msg = "Facade instance for this Multiton key already constructed!";

    /**
     * The controller instance for this Core
     * @var icontroller
     */
    protected $_controller;

    /**
     * The model instance for this Core
     * @var model
     */
    protected $_model;

    /**
     * The view instance for this Core
     * @var view
     */
    protected $_view;

    /**
     * The Multiton Key for this Core
     * @var string
     */
    protected $_multiton_key;

    /**
     * The Multiton Facade instances stack.
     * @var array
     */
    protected static $_instance_map = [];

    /**
     * Instance constructor
     *
     * This <b>IFacade</b> implementation is a Multiton,
     * so you should not call the constructor
     * directly, but instead call the static Factory method,
     * passing the unique key for this instance
     *
     * <code>
     * Facade::i( 'multitonKey' )
     * </code>
     *
     * @param string $key Unique key for this instance.
     * @throws \Exception if instance for this key has already been constructed.
     */
    protected function __construct( $key )
    {
        if (isset(self::$_instance_map[ $key ])) {
            throw new \Exception(static::Multiton_Msg);
        }
        $this->initialize( $key );
        self::$_instance_map[ $this->_multiton_key ] = $this;
        $this->initializeFacade();
    }

    /**
     * Initialize the <b>Facade</b> instance.
     *
     * Called automatically by the constructor. Override in your
     * subclass to do any subclass specific initializations. Be
     * sure to call <samp>parent::initializeFacade()</samp>, though.
     *
     * @return void
     */
    protected function initializeFacade(  )
    {
        $this->initialize_model();
        $this->initialize_controller();
        $this->initialize_view();
    }

    /**
     * <b>Facade</b> Multiton Factory method
     *
     * This <b>IFacade</b> implementation is a Multiton,
     * so you MUST not call the constructor
     * directly, but instead call this static Factory method,
     * passing the unique key for this instance
     *
     * @param string $key Unique key for this instance.
     * @return ifacade Instance for this key
     * @throws
     */
    public static function i($key )
    {
        if (!isset( self::$_instance_map[ $key ] ) ) {
            self::$_instance_map[ $key ] = new Facade( $key );
        }
        return self::$_instance_map[ $key ];
    }

    /**
     * Initialize the <b>Controller</b>.
     *
     * Called by the <b>initializeFacade()</b> method.
     *
     * Override this method in your subclass of <b>Facade</b> if
     * one or both of the following are true:
     *
     * - You wish to initialize a different <b>Controller</b>.
     * - You have <b>Commands</b> to register with the <b>Controller</b> at startup.
     *
     * If you don't want to initialize a different <b>Controller</b>,
     * call <samp>parent::initializeController()</samp> at the beginning of your
     * method, then register Commands.
     *
     * @return void
     */
    protected function initialize_controller( )
    {
        if ( isset( $this->_controller ) ) {
            return;
        }
        $this->_controller = controller::i( $this->_multiton_key );
    }

    /**
     * Initialize the <b>Model</b>.
     *
     * Called by the <b>initializeFacade()</b> method.
     *
     * Override this method in your subclass of <b>Facade</b> if
     * one or both of the following are true:
     *
     * - You wish to initialize a different <b>Model</b>.
     * - You have <b>Proxys</b> to register with the <b>Model</b> that do not<br>
     *   retrieve a reference to the <b>Facade</b> at construction time.
     *
     * If you don't want to initialize a different <b>Model</b>,
     * call <b>parent::initializeModel()</b> at the beginning of your
     * method, then register <b>Proxys</b>.
     *
     * <b>Note:</b><br>
     * This method is <i>rarely</i> overridden; in practice you are more
     * likely to use a <b>Command</b> to create and register <b>Proxys</b>
     * with the <b>Model</b>, since <b>Proxys</b> with mutable data will likely
     * need to send <b>Notifications</b> and thus will likely want to fetch a reference to
     * the <b>Facade</b> during their construction.
     *
     * @return void
     */
    protected function initialize_model( )
    {
        if ( isset( $this->_model ) ) {
            return;
        }

        $this->_model = model::i( $this->_multiton_key );
    }


    /**
     * Initialize the <b>View</b>.
     *
     * Called by the <b>initializeFacade()</b> method.
     *
     * Override this method in your subclass of <b>Facade</b>
     * if one or both of the following are true:
     *
     * - You wish to initialize a different <b>iview</b> implementation.
     * - You have <b>Observer</b>s to register with the <b>View</b>
     *
     * If you don't want to initialize a different <code>IView</code>,
     * call <code>super.initializeView()</code> at the beginning of your
     * method, then register <code>imediator</code> instances.
     *
     * <b>Note:</b><br>
     * This method is <i>rarely</i> overridden; in practice you are more
     * likely to use a Command to create and register Mediators
     * with the View, since imediator instances will need to send
     * inotifications and thus will likely want to fetch a reference
     * to the Facade during their construction.
     *
     * @return void
     */
    protected function initialize_view( )
    {
        if ( isset( $this->_view ) ) {
            return;
        }

        $this->_view = view::i( $this->_multiton_key );
    }

    /**
     * Register Proxy
     *
     * Register an <b>iproxy</b> with the <b>Model</b>.
     *
     * @param iproxy $proxy The <b>iproxy</b> to be registered with the <b>Model</b>.
     * @return void
     */
    public function proxy_register (iproxy $proxy )
    {
        if( isset( $this->_model ) ) {
            $this->_model->proxy_register( $proxy );
        }
    }

    /**
     * Retreive Proxy
     *
     * Retrieve a previously registered <b>iproxy</b> from the <b>Model</b> by name.
     *
     * @param string $proxy_name Name of the <b>iproxy</b> instance to be retrieved.
     * @return iproxy The <b>iproxy</b> previously regisetered by <var>proxyName</var> with the <b>Model</b>.
     */
    public function proxy_retrieve ($proxy_name )
    {
        return ( isset( $this->_model ) ? $this->_model->proxy_retrieve( $proxy_name ) : null );
    }

    /**
     * Remove Proxy
     *
     * Remove a previously registered <b>iproxy</b> instance from the <b>Model</b> by name.
     *
     * @param string $proxy_name Name of the <b>iproxy</b> to remove from the <b>Model</b>.
     * @return iproxy The <b>iproxy</b> that was removed from the <b>Model</b>.
     */
    public function proxy_remove ($proxy_name )
    {
        return ( isset( $this->_model ) ? $this->_model->proxy_remove( $proxy_name ): null);
    }

    /**
     * Has Proxy
     *
     * Check if a Proxy is registered for the given <var>proxyName</var>.
     *
     * @param string $proxy_name Name of the <b>proxy</b> to check for.
     * @return bool Boolean: Whether a <b>proxy</b> is currently registered with the given <var>proxyName</var>.
     */
    public function proxy_has($proxy_name )
    {
        return ( isset( $this->_model ) ? $this->_model->proxy_has( $proxy_name ) : false );
    }

    /**
     * Register Command
     *
     * Register an <b>icommand</b> with the <b>Controller</b>.
     *
     * @param string $noteName Name of the <b>inotification</b>
     * @param object|string $command_class_name <b>icommand</b> object to register. Can be an object OR a class name.
     * @return void
     */
    public function command_register($notification_name, $command_class_name)
    {
        if( isset( $this->_controller ) )
        {
            $this->_controller->register( $notification_name, $command_class_name );
        }
    }
    /**
     * Remove Command
     *
     * Remove a previously registered <b>icommand</b> to <b>inotification</b> mapping.
     *
     * @param string $notification_name Name of the <b>inotification</b> to remove the <b>icommand</b> mapping for.
     */
    public function command_remove($notification_name )
    {
        if( isset( $this->_controller ) ) {
            $this->_controller->remove( $notification_name );
        }
    }

    /**
     * Has Command
     *
     * Check if a <b>Command</b> is registered for a given <b>notification</b>
     *
     * @param string $notification_name Name of the <b>inotification</b> to check for.
     * @return bool Whether a <b>Command</b> is currently registered for the given <var>notificationName</var>.
     */
    public function command_has(string $notification_name )
    {
        return ( isset( $this->_controller ) ? $this->_controller->has($notification_name) : false );
    }

    /**
     * Register Mediator
     *
     * Register an <b>imediator</b> instance with the <b>View</b>.
     *
     * @param imediator $mediator Reference to the <b>imediator</b> instance.
     */
    public function mediator_register(imediator $mediator )
    {
        if ( isset( $this->_view ) )
        {
            $this->_view->mediator_register( $mediator );
        }
    }

    /**
     * Retreive Mediator
     *
     * Retrieve a previously registered <b>imediator</b> instance from the <b>View</b>.
     *
     * @param string $mediator_name Name of the <b>imediator</b> instance to retrieve.
     * @return imediator The <b>imediator</b> previously registered with the given <var>mediatorName</var>.
     */
    public function mediator_retrieve(string $mediator_name )
    {
        return ( isset( $this->_view ) ? $this->_view->mediator_retrieve( $mediator_name ) : null );
    }

    /**
     * Remove Mediator
     *
     * Remove a previously registered <b>imediator</b> instance from the <b>View</b>.
     *
     * @param string $mediator_name Name of the <b>imediator</b> instance to be removed.
     * @return imediator The <b>imediator</b> instance previously registered with the given <var>mediatorName</var>.
     */
    public function mediator_remove(string $mediator_name )
    {
        return ( isset( $this->_view ) ? $this->_view->mediator_remove( $mediator_name ): null);
    }

    /**
     * Has Mediator
     *
     * Check if a <b>imediator</b> is registered or not.
     *
     * @param string $mediator_name The name of the <b>imediator</b> to check for.
     * @return bool Boolean: Whether a <b>imediator</b> is registered with the given <var>mediatorName</var>.
     */
    public function mediator_has(string $mediator_name )
    {
        return ( isset( $this->_view ) ? $this->_view->mediator_has( $mediator_name ) : false );
    }

    /**
     * Create and send an <b>inotification</b>.
     *
     * Keeps us from having to construct new notification
     * instances in our implementation code.
     *
     * @param string $notification_name The name of the notification to send.
     * @param mixed $body The body of the notification (optional).
     * @param string $type The type of the notification (optional).
     * @return void
     * @throws
     */
    public function send(string $notification_name , $body =null, $type=null )
    {
        $this->notify_observers( new notification( $notification_name, $body, $type ) );
    }

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
    public function notify_observers (inotification $notification )
    {
        if ( isset( $this->_view ) )
        {
            $this->_view->notify_observers( $notification );
        }
    }

    /**
     * Set the Multiton key for this facade instance.
     *
     * Not called directly, but instead from the
     * constructor when i is invoked.
     * It is necessary to be public in order to
     * implement INotifier.
     *
     * @param string $key Unique key for this instance.
     * @return void
     */
    public function initialize(string $key )
    {
        $this->_multiton_key = $key;
    }

    /**
     * Check if a Core is registered or not
     *
     * @param string $key the multiton key for the Core in question
     * @return bool Whether a Core is registered with the given <code>key</code>.
     */
    public static function core_has(string $key )
    {
        return ( isset( self::$_instance_map[ $key ] ) );
    }

    /**
     * Remove a Core.
     *
     * Remove the Model, View, Controller and Facade
     * instances for the given key.
     *
     * @param string $key MultitonKey of the Core to remove
     * @return void
     */
    public static function core_remove(string $key )
    {
        if ( !self::core_has( $key ) ) {
            return;
        }
        model::model_remove( $key );
        view::view_remove( $key );
        controller::controller_remove( $key );
        self::$_instance_map[ $key ] = null;
    }
}
