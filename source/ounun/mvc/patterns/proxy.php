<?php
namespace ounun\mvc\patterns;
use ounun\mvc\interfaces\iproxy;
use ounun\mvc\patterns\observer\notifier;

/**
 * OununMVC Multicore Port to PHP
 */

/**
 * A base <b>iproxy</b> implementation.
 *
 * In OununMVC, Proxy classes are used to manage parts of the
 * application's data model.
 *
 * A <b>proxy</b> might simply manage a reference to a local data object,
 * in which case interacting with it might involve setting and
 * getting of its data in synchronous fashion.
 *
 * <b>proxy</b> classes are also used to encapsulate the application's
 * interaction with remote services to save or retrieve data, in which case,
 * we adopt an asyncronous idiom; setting data (or calling a method) on the
 * <b>proxy</b> and listening for a <b>notification</b> to be sent
 * when the <b>proxy</b> has retrieved the data from the service.
 *
 * @see notifier
 * @see model
 */
class proxy extends notifier implements iproxy
{

    /**
     * Define the default name of the proxy
     * @var string
     */
    const Name = 'proxy';

    /**
     * The name of the proxy
     * @var string
     */
    protected $_proxy_name = '';

    /**
     * The data object managed by the proxy
     * @var mixed
     */
    protected $_data;

    /**
     * Constructor
     *
     * @param string $proxy_name [OPTIONAL] Name for the proxy instance will default to <samp>Proxy::NAME</samp> if not set.
     * @param mixed $data [OPTIONAL] Data object to be managed by the proxy may be set later with <samp>setData()</samp>.
     */
    public function __construct(string $proxy_name='', $data = [] )
    {
        $this->_proxy_name = !empty( $proxy_name ) ? $proxy_name : static::Name;
        $this->data_set($data);
    }

    /**
     * Get the Proxy name
     *
     * @return string The Proxy instance name
     */
    public function proxy_name_get()
    {
        return $this->_proxy_name;
    }

    /**
     * Data setter
     * Set the data object
     *
     * @param mixed $data the data object
     * @return void
     */
    public function data_set($data )
    {
        if ( !is_null( $data ) ) {
            $this->_data = $data;
        }
    }

    /**
     * Data getter
     * Get the data object
     *
     * @return mixed The data Object. null if not set.
     */
    public function data_get()
    {
        return ( isset($this->_data) ? $this->_data : null );
    }

    /**
     * onRegister event
     * Called by the Model when the Proxy is registered
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * onRemove event
     * Called by the Model when the Proxy is removed
     *
     * @return void
     */
    public function remove()
    {
    }
}
