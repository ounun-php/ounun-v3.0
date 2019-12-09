<?php
namespace ounun\mvc\patterns;
use ounun\mvc\interfaces\imediator;
use ounun\mvc\interfaces\inotification;
use ounun\mvc\patterns\observer\notifier;

/**
 * PureMVC Multicore Port to PHP
 */

/**
 * A base <b>imediator</b> implementation.
 *
  * In PureMVC, <b>imediator</b> implementors assume these responsibilities:
 *
 * - Implement a common method which returns a list of all <b>inotification</b>s
 *   the <b>imediator</b> has interest in.
 * - Implement a notification callback method.
 * - Implement methods that are called when the imediator is registered or removed from the View.
 *
 * Additionally, <b>imediator</b>s typically:
 *
 * - Act as an intermediary between one or more view components such as text boxes or
 *   list controls, maintaining references and coordinating their behavior.
 * - This is often the place where event listeners are added to view
 *   components, and their handlers implemented.
 * - Respond to and generate <b>inotifications</b>, interacting with of
 *   the rest of the PureMVC app.
 *
 * When an <b>imediator</b> is registered with the <b>iview</b>,
 * the <b>iview</b> will call the <b>imediator</b>'s
 * <b>listNotificationInterests</b> method. The <b>imediator</b> will
 * return an <b>Array</b> of <b>inotification</b> names which
 * it wishes to be notified about.
 *
 * The <b>iview</b> will then create an <b>Observer</b> object encapsulating
 * that <b>imediator</b>'s (<b>handleNotification</b>) method and
 * register it as an Observer for each <b>inotification</b> name
 * returned by <b>listNotificationInterests</b>.
 *
 * @package org.puremvc.php.multicore
 * @see View
        org.puremvc.php.multicore.core.View.php
 */
class mediator extends notifier implements imediator
{

    /**
     * The default name of the <b>Mediator</b>.
     *
     * Typically, a <b>Mediator</b> will be written to serve
     * one specific control or group controls and so,
     * will not have a need to be dynamically named.
     */
    const Name = 'mediator';

    /**
     * the mediator name
     * @var string
     */
    protected $_mediator_name;

    /**
     * The view component
     * @var mixed
     */
    protected $_view_component;

    /**
     * Constructor.
     * mediator constructor.
     * @param string $mediator_name
     * @param mixed  $view_component
     */
    public function __construct(string $mediator_name= '', $view_component = null )
    {
        $this->_mediator_name =  !empty($mediator_name) ? $mediator_name : mediator::Name;
        $this->view_component_set( $view_component  );
    }

    /**
     * Get Mediator Name
     *
     * Get the <b>imediator</b> instance name
     *
     * @return string The <b>imediator</b> instance name.
     */
    public function mediator_name_get()
    {
        return $this->_mediator_name;
    }

    /**
     * Get View Component
     *
     * Get the <b>imediator</b>'s view component.
     *
     * @return mixed The view component
     */
    public function view_component_get()
    {
        return $this->_view_component;
    }

    /**
     * Set View Component
     *
     * Set the <b>imediator</b>'s view component.
     *
     * @param mixed $view_component The view component.
     * @return void
     */
    public function view_component_set($view_component )
    {
        $this->_view_component = $view_component;
    }

    /**
     * List Notifications Interests.
     *
     * List <b>inotification</b> interests.
     *
     * @return array An <b>Array</b> of the <b>inotification</b> names this <b>imediator</b> has an interest in.
     */
    public function notification_list_interests()
    {
        return [];
    }

    /**
     * Handle Notification
     *
     * Handle an <b>inotification</b>.
     *
     * @param inotification $notification The <b>inotification</b> to be handled.
     * @return void
     */
    public function notification_handle(inotification $notification )
    {
    }

    /**
     * onRegister event
     *
     * Called by the <b>View</b> when the <b>Mediator</b> is registered.
     *
     * @return void
     */
    public function register( )
    {
    }

    /**
     * onRemove event
     *
     * Called by the <b>View</b> when the <b>Mediator</b> is removed.
     *
     * @return void;
     */
    public function remove( )
    {
    }

}
