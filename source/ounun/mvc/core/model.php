<?php
namespace ounun\mvc\core;
use ounun\mvc\interfaces\imodel;
use ounun\mvc\interfaces\iproxy;
use ounun\mvc\patterns\proxy;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * A Multiton <b>IModel</b> implementation.
 *
 * In PureMVC, the <b>Model</b> class provides
 * access to model objects (Proxies) by named lookup.
 *
 * The <b>Model</b> assumes these responsibilities:
 *
 * - Maintain a cache of <b>iproxy</b> instances.
 * - Provide methods for registering, retrieving, and removing
 *   <b>iproxy</b> instances.
 *
 * Your application must register <b>iproxy</b> instances
 * with the <b>Model</b>. Typically, you use an
 * <b>icommand</b> to create and register <b>iproxy</b>
 * instances once the <b>Facade</b> has initialized the Core
 * actors.

 * @see proxy
 * @see iproxy
 *
 * @package org.puremvc.php.multicore
 */
class model implements imodel
{
    /**
     * Define the message content for the duplicate instance exception
     * @var string
     */
    const Multiton_Msg = "Model instance for this Multiton key already constructed!";

    /**
     * Mapping of proxyNames to iproxy references
     * @var array
     */
    protected $_proxy_map = [];

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
     * This <b>IModel</b> implementation is a Multiton, so you should not
     * call the constructor directly, but instead call the static Multiton
     * Factory method.
     *
     * ex:
     * <code>
     * Model::getInstance( 'multitonKey' )
     * </code>
     *
     * @param string $key Unique key for this instance.
     * @throws \Exception if instance for this key has already been constructed
     */
    protected function __construct( $key )
    {
        if ( isset( self::$_instance_map[ $key ] ) ) {
            throw new \Exception(self::Multiton_Msg);
        }
        $this->_multiton_key = $key;
        $this->_proxy_map    = [];
        self::$_instance_map[ $this->_multiton_key ] = $this;
        $this->initializeModel();
    }

    /**
     * Initialize the Model instance.
     *
     * Called automatically by the constructor, this is your opportunity to
     * initialize the instance in your subclass without overriding
     * the constructor.
     *
     * @return void
     */
    protected function initializeModel(  )
    {
    }

    /**
     * Model Factory method.
     *
     * This <b>IModel</b> implementation is a Multiton so
     * this method MUST be used to get acces, or create, <b>IModel</b>s.
     *
     * @param string $key Unique key for this instance.
     * @return imodel The instance for this Multiton key
     * @throws
     */
    public static function i($key )
    {
        if ( !isset( self::$_instance_map[ $key ] ) ) {
            self::$_instance_map[$key] = new model( $key );
        }

        return self::$_instance_map[$key];
    }

    /**
     * Register Proxy
     *
     * Register an <b>iproxy</b> with the <b>Model</b>.
     *
     * @param iproxy $proxy The <b>iproxy</b> to be registered with the <b>Model</b>.
     * @return void
     */
    public function proxy_register(iproxy $proxy )
    {
        $proxy->initialize( $this->_multiton_key );
        $this->_proxy_map[ $proxy->proxy_name_get() ] = $proxy;
        $proxy->register();
    }

    /**
     * Retreive Proxy
     *
     * Retrieve a previously registered <b>iproxy</b> from the <b>Model</b> by name.
     *
     * @param string $proxy_name Name of the <b>iproxy</b> instance to be retrieved.
     * @return iproxy The <b>iproxy</b> previously regisetered by <var>proxyName</var> with the <b>Model</b>.
     */
    public function proxy_retrieve($proxy_name )
    {
        return ( $this->proxy_has( $proxy_name ) ? $this->_proxy_map[ $proxy_name ] : null);
    }

    /**
     * Has Proxy
     *
     * Check if a Proxy is registered for the given <var>proxyName</var>.
     *
     * @param string $proxy_name Name of the <b>proxy</b> to check for.
     * @return bool Whether a <b>proxy</b> is currently registered with the given <var>proxyName</var>.
     */
    public function proxy_has($proxy_name )
    {
        return isset( $this->_proxy_map[ $proxy_name ] );
    }

    /**
     * Remove Proxy
     *
     * Remove a previously registered <b>iproxy</b> instance from the <b>Model</b> by name.
     *
     * @param string $proxy_name Name of the <b>iproxy</b> to remove from the <b>Model</b>.
     * @return iproxy The <b>iproxy</b> that was removed from the <b>Model</b>.
     */
    public function proxy_remove($proxy_name )
    {
        $proxy = $this->proxy_retrieve( $proxy_name );
        if ($proxy )
        {
            unset( $this->_proxy_map[ $proxy_name ] );
            $proxy->remove();
        }
        return $proxy;
    }

    /**
     * Remove Model
     *
     * Remove an <b>IModel</b> instance by key.
     *
     * @param string $key The multitonKey of IModel instance to remove
     * @return void
     */
    public static function model_remove($key )
    {
        unset( self::$_instance_map[ $key ] );
    }

}
