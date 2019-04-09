<?php

namespace ounun\cmd\task\system_base;

use ounun\cmd\task\libs\com_baidu;
use ounun\cmd\task\manage;
use ounun\cmd\task\struct;

abstract class baidu_pc extends _system
{
    public static $name = '提交新网址 [baidu_pc]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 10 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;
    /**
     * baidu_pc constructor.
     * @param struct $task_struct
     * @param string $tag
     * @param string $tag_sub
     */
    public function __construct(struct $task_struct, string $tag = '', string $tag_sub = '')
    {
        $this->_tag = 'baidu_pc';
        $this->_tag_sub = '';

        parent::__construct($task_struct, $tag, $tag_sub);
    }

    /**
     * 执行任务
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        try {
            $this->_logs_status = manage::Logs_Succeed;
            $this->url_push_baidu_pc_mip();
            manage::logs_msg("Successful push baidu_pc",$this->_logs_status);
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(),$this->_logs_status);
            manage::logs_msg("Fail push baidu_pc",$this->_logs_status);
        }
    }

    public function push_pc(array $urls)
    {
        $api = str_replace(['{$site}', '{$token}'], [$this->_url_root_pc, $this->_token_site], com_baidu::api_baidu_pc);
        return $this->_push($api, $urls);
    }

    /**
     * 定时  数据接口提交 pc
     * @param bool $is_today false :历史    true  :当天
     */
    public function do_push_pc($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_step;
        do {
            $do = $this->_do_push(com_baidu::type_baidu_pc, $is_today);
        } while ($do);
    }

    /**   */
    public function url_push_baidu_pc_mip()
    {
        $this->do_push_pc();
    }
}
