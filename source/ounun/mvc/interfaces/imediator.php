<?php
namespace ounun\mvc\interfaces;
/**
 * OununMVC Multicore Port to PHP
 */

/**
 * The interface definition for a OununMVC Mediator.
 *
 * In OununMVC, <b>imediator</b> implementors assume these responsibilities:
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
 *   the rest of the OununMVC app.
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
 * A concrete imediator implementor usually looks something like this:
 *
 * <code>
 * <?php
 * require_once 'org/OununMVC/php/multicore/interfaces/imediator.php';
 * require_once 'org/OununMVC/php/multicore/patterns/mediator/Mediator.php';
 *
 * class MyMediator extends Mediator implements imediator
 * {
 *     const NAME = 'MyMediator';
 *
 *     public function __construct( $mediatorName, $viewComponent = null )
 *     {
 *         parent::__construct( MyMediator::NAME, $viewComponent );
 *     }
 *
 *     public function listNotificationInterests()
 *     {
 *         return array( 'Hello', 'Bye' );
 *     }
 *
 *     public function handleNotification( inotification $notification )
 *     {
 *         switch( $notification->getName() )
 *         {
 *             case 'hello':
 *             case 'bye':
 *                 $this->outputNotificationBody( $notification );
 *                 break;
 *         }
 *     }
 *
 *     public function outputNotificationBody( $note )
 *     {
 *         print $note->body;
 *     }
 * }
 * </code>
 *
 * @see inotification
        org\OununMVC\php\multicore\interfaces\inotification.php
 * @package org.OununMVC.php.multicore
 *
 */
interface imediator extends inotifier
{

    /**
     * Get Mediator Name
     *
     * Get the <b>imediator</b> instance name
     *
     * @return string The <b>imediator</b> instance name.
     */
    public function mediator_name_get();

    /**
     * Get View Component
     *
     * Get the <b>imediator</b>'s view component.
     *
     * @return mixed The view component
     */
    public function view_component_get();

    /**
     * Set View Component
     *
     * Set the <b>imediator</b>'s view component.
     *
     * @param mixed $view_component The view component.
     * @return void
     */
    public function view_component_set($view_component );

    /**
     * List Notifications Interests.
     *
     * List <b>inotification</b> interests.
     *
     * @return array An <b>Array</b> of the <b>inotification</b> names this <b>imediator</b> has an interest in.
     */
    public function notification_list_interests( );

    /**
     * Handle Notification
     *
     * Handle an <b>inotification</b>.
     *
     * @param inotification $notification The <b>inotification</b> to be handled.
     * @return void
     */
    public function notification_handle(inotification $notification );

    /**
     * onRegister event
     *
     * Called by the <b>View</b> when the <b>Mediator</b> is registered.
     *
     * @return void
     */
    public function register();

    /**
     * onRemove event
     *
     * Called by the <b>View</b> when the <b>Mediator</b> is removed.
     *
     * @return void;
     */
    public function remove();

}
