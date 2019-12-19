<?php
namespace ounun\mvc\core;
use ounun\mvc\interfaces\imediator;
use ounun\mvc\interfaces\inotification;
use ounun\mvc\interfaces\iobserver;
use ounun\mvc\interfaces\iview;
use ounun\mvc\patterns\observer\Observer;

/**
 * OununMVC Multicore Port to PHP
 */


/**
 * A Multiton <b>iview</b> implementation.
 *
 * In OununMVC, the <b>View</b> class assumes these responsibilities:
 *
 * - Maintain a cache of <b>imediator</b> instances.
 * - Provide methods for registering, retrieving, and removing <b>imediators</b>.
 * - Notifiying <b>imediators</b> when they are registered or removed.
 * - Managing the observer lists for each <b>inotification<b> in the application.
 * - Providing a method for attaching <b>IObservers</b> to an <b>inotification</b>'s observer list.
 * - Providing a method for broadcasting an <b>inotification</b>.
 * - Notifying the <b>IObservers</b> of a given <b>inotification</b> when it broadcast.
 *
 * @see mediator
 * @see observer
 * @see notification
 *
 * @package org.puremvc.php.multicore
 */
class view implements iview
{
    /**
     * Define the message content for the duplicate instance exception
     * @var string
     */
    const Multiton_Msg = "View instance for this Multiton key already constructed!";

    /**
     * Mapping of mediatorNames to imediator references
     * @var array
     */
    protected $_mediator_map = [];

    /**
     * Mapping of Notification names to Observer lists
     * @var array
     */
    protected $_observer_map  = [];

    /**
     * The Multiton Key for this Core
     * @var string
     */
    protected $_multiton_key = NULL;

    /**
     * The Multiton instances stack
     * @var array
     */
    protected static $_instance_map = [];

    /**
     * Constructor.
     *
     * This <b>iview</b> implementation is a Multiton,
     * so you should not call the constructor
     * directly, but instead call the static Multiton
     * Factory method.
     *
     * <code>
     * View::i( 'multitonKey' );
     * </code>
     *
     * @param string $key Unique key for this instance.
     * @throws \Exception if instance for this key has already been constructed.
     */
    protected function __construct( string $key )
    {
        if ( isset( self::$_instance_map[ $key ] ) )
        {
            throw new \Exception(self::Multiton_Msg);
        }
        $this->_multiton_key = $key;

        $this->_mediator_map = [];
        $this->_observer_map = [];

        self::$_instance_map[ $this->_multiton_key ] = $this;
        $this->initialize();
    }

    /**
     * Initialize the Singleton View instance.
     *
     * Called automatically by the constructor, this
     * is your opportunity to initialize the Multiton
     * instance in your subclass without overriding the
     * constructor.
     *
     * @return void
     */
    protected function initialize(  )
    {
    }

    /**
     * View Factory method.
     *
     * This <b>iview</b> implementation is a Multiton so
     * this method MUST be used to get acces, or create, <b>iview</b>s.
     *
     * @param string $key Unique key for this instance.
     * @return iview The instance for this Multiton key.
     * @throws
     */
    public static function i(string $key )
    {
        if ( !isset( self::$_instance_map[ $key ] ) )
        {
            self::$_instance_map[ $key ] = new view( $key );
        }
        return self::$_instance_map[ $key ];
    }

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
    public function observer_register (string $notification_name, iobserver $observer)
    {
        if( isset( $this->_observer_map[ $notification_name ] ) )
        {
            array_push($this->_observer_map[ $notification_name ], $observer );
        }
        else
        {
            $this->_observer_map[ $notification_name ] = array( $observer );
        }
    }


    /**
     * Notify Observers
     *
     * Notify the <b>IObservers</b> for a particular <b>inotification</b>.
     *
     * All previously attached <b>IObservers</b> for this <b>inotification</b>'s
     * list are notified and are passed a reference to the <b>inotification</b> in
     * the order in which they were registered.
     *
     * @param inotification $notification The <b>inotification</b> to notify <b>IObservers</b> of.
     * @return void
     */
    public function notify_observers(inotification $notification )
    {
        if( isset( $this->_observer_map[ $notification->name_get() ] ) )
        {
            // Get a reference to the observers list for this notification name
            $observers_ref = $this->_observer_map[ $notification->name_get() ];

            // Copy observers from reference array to working array,
            // since the reference array may change during the notification loop
            $observers = [];
            foreach($observers_ref as $observer)
            {
                array_push( $observers, $observer );
            }

            // Notify Observers from the working array
            foreach($observers as $observer)
            {
                $observer->notifyObserver( $notification );
            }
        }
    }

    /**
     * Remove Observer
     *
     * Remove a group of observers from the observer list for a given Notification name.
     *
     * @param string $notification_name Which observer list to remove from.
     * @param mixed $notify_context Remove the observers with this object as their notifyContext
     * @return void
     */
    public function observer_remove(string $notification_name, $notify_context )
    {
        //Is there registered Observers for the notification under inspection
        if( !isset( $this->_observer_map[ $notification_name ] )) return;

        // the observer list for the notification under inspection
        $observers = $this->_observer_map[ $notification_name ];

        // find the observer for the notifyContext
        for ( $i = 0; $i < count( $observers ); $i++ )
        {
            if ( $observers[$i]->compareNotifyContext( $notify_context ) == true )
            {
                // there can only be one Observer for a given notifyContext
                // in any given Observer list, so remove it and break
                array_splice($observers,$i,1);
                break;
            }
        }

        // Also, when a Notification's Observer list length falls to
        // zero, delete the notification key from the observer map
        if ( count( $observers ) == 0 )
        {
            unset($this->_observer_map[ $notification_name ]);
        }
    }

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
    public function mediator_register(imediator $mediator )
    {

        // do not allow re-registration (you must to removeMediator fist)
        if ( $this->mediator_has( $mediator->mediator_name_get() ) ) return;

        $mediator->initialize( $this->_multiton_key );

        // Register the Mediator for retrieval by name
        $this->_mediator_map[ $mediator->mediator_name_get() ] = $mediator;

        // Get Notification interests, if any.
        $interests = [];
        $interests = $mediator->notification_list_interests();

        // Register Mediator as an observer for each notification of interests
        if ( count( $interests ) > 0 )
        {
            // Create Observer referencing this mediator's handlNotification method
            $observer = new observer( "handleNotification", $mediator );

            // Register Mediator as Observer for its list of Notification interests
            for ( $i = 0;  $i < count( $interests ); $i++ )
            {
                $this->observer_register( $interests[$i],  $observer );
            }
        }

        // alert the mediator that it has been registered
        $mediator->register();

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
        return ( $this->mediator_has( $mediator_name ) ? $this->_mediator_map[ $mediator_name ] : null );
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
        // Retrieve the named mediator
        $mediator = $this->mediator_retrieve( $mediator_name );

        if ( $mediator )
        {
            // for every notification this mediator is interested in...
            $interests = $mediator->notification_list_interests();
            for ( $i = 0; $i < count( $interests ); $i++ )
            {
                // remove the observer linking the mediator
                // to the notification interest
                $this->observer_remove( $interests[$i], $mediator );
            }

            // remove the mediator from the map
            unset($this->_mediator_map[ $mediator_name ]);

            // alert the mediator that it has been removed
            $mediator->remove();
        }

        return $mediator;
    }

    /**
     * Has Mediator
     *
     * Check if a <b>IMediator</b> is registered or not.
     *
     * @param string $mediator_name The name of the <b>IMediator</b> to check for.
     * @return bool Boolean: Whether a <b>IMediator</b> is registered with the given <var>mediatorName</var>.
     */
    public function mediator_has(string $mediator_name )
    {
        return isset( $this->_mediator_map[ $mediator_name ] );
    }

    /**
     * Remove View
     *
     * Remove an <b>iview</b> instance by key.
     *
     * @param string $key The multitonKey of IView instance to remove
     * @return void
     */
    public static function view_remove(string $key )
    {
        unset( self::$_instance_map[ $key ] );
    }

}
