<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */

declare (strict_types=1);

namespace ounun\addons\command;

use ounun\addons\command;
use ounun\addons\console;
use ounun\db\db;
use ounun\db\pdo;

class test extends command
{
    /** @var self logic */
    public static $logic;

    /** @inheritDoc */
    protected function _initialize()
    {
    }

    /** @inheritDoc */
    public function configure()
    {
        // 命令的名字（"./ounun" 后面的部分）
        $this->name = 'test';
        // 运行 "php think list" 时的简短描述
        $this->description = '单元测试 Test phpunit';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "Test phpunit instructions";
        // parent
        parent::configure();
    }

    /** @inheritDoc */
    public function _execute_inside(array $argc_input): int
    {
        $f = [
            'part_id'        => ['default' => 0, 'type' => db::Int],       // 分部id
            'data_id'        => ['default' => 0, 'type' => db::Int],       // 内容ID(同表system_content的content_id)
            'views'          => ['default' => 0, 'type' => db::Int],       // 点击次数
            'scores'         => ['default' => 0, 'type' => db::Float],       // 评分
            'expend_gold'    => ['default' => 0, 'type' => db::Int],       // 需要消耗金币
            'expend_score'   => ['default' => 0, 'type' => db::String],       // 消耗积分
            'discuss_good'   => ['default' => 0, 'type' => db::Int],       // 好评
            'discuss_bad'    => ['default' => 0, 'type' => db::Int],       // 差评
            'share'          => ['default' => 0, 'type' => db::Bool],       // 分享数
            'comment'        => ['default' => 0, 'type' => db::Int],       // 评论数
            'dynamic_extend' => ['default' => null, 'type' => db::Json],   // 扩展数据json
            'created_at'     => ['default' => 0, 'type' => db::Int],       // 添加时间
            'updated_at'     => ['default' => 0, 'type' => db::Int],       // 更新时间
        ];
        $v = [
            'part_id' => 2,
            'data_id' => 555,
            'views'   => 9,
            'scores'  => 3.5
        ];
        // echo
        console::echo("---> " . date("Y-m-d H:i:s ") . ' ' . __CLASS__ . ' \$argc_input:' . json_encode_unescaped($argc_input), command_c::Color_Red);
        $db = pdo::i('command');
        $db->table('v2_system_dynamic')->field_type($f)->test();
        return 0;
    }
}
