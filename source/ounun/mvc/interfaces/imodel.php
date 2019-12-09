<?php
namespace ounun\mvc\interfaces;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * The interface definition for a PureMVC Model.
 *
 * In PureMVC, <b>IModel</b> implementors provide
 * access to <b>iproxy</b> objects by named lookup.
 *
 * An <b>IModel</b> assumes these responsibilities:
 *
 * - Maintain a cache of <b>iproxy</b> instances.
 * - Provide methods for registering, retrieving, and removing <b>iproxy</b> instances.
 *
 *
 * @package org.puremvc.php.multicore
 */
interface imodel
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
     * Retreive Proxy
     *
     * Retrieve a previously registered <b>iproxy</b> from the <b>Model</b> by name.
     *
     * @param string $proxy_name Name of the <b>iproxy</b> instance to be retrieved.
     * @return iproxy The <b>iproxy</b> previously regisetered by <var>proxyName</var> with the <b>Model</b>.
     */
    public function proxy_retrieve(string $proxy_name );

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
     * Has Proxy
     *
     * Check if a Proxy is registered for the given <var>proxyName</var>.
     *
     * @param string $proxy_name Name of the <b>proxy</b> to check for.
     * @return bool Boolean: Whether a <b>proxy</b> is currently registered with the given <var>proxyName</var>.
     */
    public function proxy_has(string $proxy_name );

}
