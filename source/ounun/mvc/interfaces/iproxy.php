<?php
namespace ounun\mvc\interfaces;
/**
 * PureMVC Multicore Port to PHP
 */

/**
 * The interface definition for a PureMVC Proxy.
 *
 * In PureMVC, <b>iproxy</b> implementors assume these responsibilities:</P>
 *
 * - Implement a common method which returns the name of the Proxy.
 * - Provide methods for setting and getting the data object.
 *
 * Additionally, <b>iproxy</b>s typically:
 *
 * - Maintain references to one or more pieces of model data.
 * - Provide methods for manipulating that data.
 * - Generate <b>inotifications</b> when their model data changes.
 * - Expose their name as a <b>public static const</b> called <b>NAME</b>, if they are not instantiated multiple times.
 * - Encapsulate interaction with local or remote services used to fetch and persist model data.
 *
 * @package org.puremvc.php.multicore
 */
interface iproxy extends inotifier
{

    /**
     * Get the Proxy name
     * @return string The Proxy instance name
     */
    public function proxy_name_get();

    /**
     * Data setter
     * Set the data object
     *
     * @param mixed $data the data object
     * @return void
     */
    public function data_set( $data );

    /**
     * Data getter
     * Get the data object
     *
     * @return mixed The data Object. null if not set
     */
    public function data_get();

    /**
     * onRegister event
     * Called by the Model when the Proxy is registered
     *
     * @return void
     */
    public function register();

    /**
     * onRemove event
     * Called by the Model when the Proxy is removed
     *
     * @return void
     */
    public function remove();
}
