<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\spl;

class observer implements \SplObserver
{
    /** @var array 增加 */
    const event_action_add = 'add';
    /** @var array 编辑 */
    const event_action_edit = 'edit';
    /** @var array 获得 */
    const event_action_get = 'get';
    /** @var array 设置 */
    const event_action_set = 'set';
    /** @var array 删除 */
    const event_action_delete = 'delete';
    /** @var array 移动 */
    const event_action_move = 'move';
    /** @var array 发布公开 */
    const event_action_publish = 'publish';
    /** @var array 私有非公开 */
    const event_action_unpublish = 'unpublish';
    /** @var array 恢复；还原 */
    const event_action_restore = 'restore';
    /** @var array 动作 */
    const event_actions = [
        self::event_action_add,
        self::event_action_edit,
        self::event_action_get,
        self::event_action_set,
        self::event_action_delete,
        self::event_action_move,
        self::event_action_publish,
        self::event_action_unpublish,
        self::event_action_restore,
    ];

    /** @var string 动作之后 */
    const event_status_after = 'after';
    /** @var string 动作之前 */
    const event_status_before = 'before';
    /** @var string 状态 */
    const event_status_state = 'state';
    /** @var string 评论状态 */
    const event_status_review = 'review';
    /** @var string 通知状态 */
    const event_status_notice = 'notice';
    /** @var string 图片状态 */
    const event_status_picture = 'picture';
    /** @var array 状态变化 */
    const event_status_single = [
//        self::event_status_after,
//        self::event_status_before,
        self::event_status_state,
        self::event_status_review,
        self::event_status_notice,
        self::event_status_picture,
    ];
    /** @var array 状态变化(加动作) */
    const event_status = [
        self::event_status_after,
        self::event_status_before,
    ];

    /** @var array 配制 */
    protected array $_config = [];

    /** @var array 插件数据 */
    protected static array $_plugin = [];

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
        list($event,$paras) = $subject->event_paras;
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
