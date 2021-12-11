<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\spl;

class observer implements \SplObserver
{
    /** @var array 增加 */
    const Event_Action_Add = 'add';
    /** @var array 编辑 */
    const Event_Action_Edit = 'edit';
    /** @var array 获得 */
    const Event_Action_Get = 'get';
    /** @var array 设置 */
    const Event_Action_Set = 'set';
    /** @var array 删除 */
    const Event_Action_Delete = 'delete';
    /** @var array 移动 */
    const Event_Action_Move = 'move';
    /** @var array 发布公开 */
    const Event_Action_Publish = 'publish';
    /** @var array 私有非公开 */
    const Event_Action_UnPublish = 'unpublish';
    /** @var array 恢复；还原 */
    const Event_Action_Restore = 'restore';
    /** @var array 动作 */
    const Event_Actions = [
        self::Event_Action_Add,
        self::Event_Action_Edit,
        self::Event_Action_Get,
        self::Event_Action_Set,
        self::Event_Action_Delete,
        self::Event_Action_Move,
        self::Event_Action_Publish,
        self::Event_Action_UnPublish,
        self::Event_Action_Restore,
    ];

    /** @var string 动作之后 */
    const Event_Status_After = 'after';
    /** @var string 动作之前 */
    const Event_Status_Before = 'before';
    /** @var string 状态 */
    const Event_Status_State = 'state';
    /** @var string 评论状态 */
    const Event_Status_Review = 'review';
    /** @var string 通知状态 */
    const Event_Status_Notice = 'notice';
    /** @var string 图片状态 */
    const Event_Status_Picture = 'picture';
    /** @var array 状态变化 */
    const Event_Status_Single = [
//        self::event_status_after,
//        self::event_status_before,
        self::Event_Status_State,
        self::Event_Status_Review,
        self::Event_Status_Notice,
        self::Event_Status_Picture,
    ];
    /** @var array 状态变化(加动作) */
    const Event_Status = [
        self::Event_Status_After,
        self::Event_Status_Before,
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
