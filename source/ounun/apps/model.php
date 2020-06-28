<?php


namespace ounun\apps;

use \ounun\db\pdo;

abstract class model
{
    /** @var array 数据 */
    protected array $_data = [];

    /** @var pdo */
    public pdo $db;

    /** @var string */
    public string $table = '';

    /** @var array 数据表结构 */
    public array $table_options = [
        'fields'          => [],
        'primary'         => '',
        'readonly'        => [],
        'create_autofill' => [],
        'update_autofill' => [],
        'filters_input'   => [],
        'filters_output'  => [],
        'validators'      => [],
        'options'         => []
    ];



}
