<?php
namespace ounun\spl;

class observer implements \SplObserver
{
    /** @var array 配制 */
    protected $_config = [];

    /** @var array 插件数据 */
    protected static $_plugin = [];

    public function __construct(string $config_filename)
    {
        if (is_file($config_filename)) {
            $config = require($config_filename);
            foreach ($config as $class_name => $events) {
                foreach ($events as $event) {
                    $this->_config[$event][] = $class_name;
                }
            }
        }
    }

    /**
     * @param \SplSubject $subject
     * @return bool|void
     */
    public function update(\SplSubject $subject)
    {
        list($event,$paras) = $subject->event;
        if (empty($this->_config[$event])) {
            return false;
        }

        foreach ($this->_config[$event] as $class_name) {
            if (!isset(static::$_plugin[$class_name]) && $class_name) {
                static::$_plugin[$class_name] = new $class_name($subject);
            }
            $class_obj = static::$_plugin[$class_name];
            if($class_obj){
                call_user_func_array([$class_obj,$event],$paras);
            }
        }
    }
}
