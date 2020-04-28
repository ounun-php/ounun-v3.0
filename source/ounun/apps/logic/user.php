<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\apps\logic;


class user extends \ounun\apps\logic
{
    /** @var int 用户Uid */
    public $uid = 0;
    /** @var string 用户昵称 */
    public $uname = '';
    /** @var string 用户所在组group_id */
    public $group_id = 0;
    /** @var string 用户应用角色role_id(多角色游戏) */
    public $role_id = 0;
    /** @var string 微信unique_id */
    public $open_id = '';
    /** @var string 微信unique_id */
    public $unique_id = '';
    
}
