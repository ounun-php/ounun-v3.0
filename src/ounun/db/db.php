<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);

namespace ounun\db;

class db
{
    /** @var string 布尔类型 */
    const Bool = 'bool';
    /** @var string 整数类型 */
    const Int = 'int';
    /** @var string 浮点类型 */
    const Float = 'float';
    /** @var string 枚举 - 字段类型 */
    const Enum = 'enum';
    /** @var string 字符串类型 */
    const String = 'string';
    /** @var string JSON类型 */
    const Json = 'json';
    /** @var string 下线(子集) - 字段类型 */
    const Child = 'child';

    /**
     * Types 数据类型
     */
    const Types = [
        self::Bool,
        self::Int,
        self::Float,
        self::Enum,
        self::String,
        self::Json,
        self::Child,
    ];

    /** @var string 日期转秒数0点0分 */
    const Date2Time_00 = 'd2t00';
    /** @var string 日期转秒数23点59分59秒 */
    const Date2Time_24 = 'd2t24';
    /** @var string 日期时间转秒数 */
    const String2Time = 's2t';

    /**
     * 数据format
     * @param array $data 数据
     * @param array $field_info 字段信息
     * @param bool $is_update 是否更新/插入  true:更新(默认)  false:插入
     * @return array
     */
    static public function format(array $data, array $field_info, bool $is_update = false): array
    {
        $fn = function (&$rs, $value, string $field, string $type) {
            if (static::Bool === $type) {
                $rs[$field] = (bool)$value;
            } elseif (static::Int === $type) {
                $rs[$field] = (int)$value;
            } elseif (static::Float === $type) {
                $rs[$field] = (float)$value;
            } elseif (static::String === $type) {
                $rs[$field] = null === $value ? null : (string)$value;
            } elseif (static::Json === $type) {
                $rs[$field] = null === $value ? null : json_encode_unescaped($value);
            } elseif (static::Date2Time_00 === $type) {
                $rs[$field] = strtotime($value . ' 00:00:00');
            } elseif (static::Date2Time_24 === $type) {
                $rs[$field] = strtotime($value . ' 23:59:59') + 1;
            } elseif (static::String2Time === $type) {
                $rs[$field] = strtotime($value);
            } else {
                $rs[$field] = (string)$value;
            }
        };
        $rs = [];
        if ($is_update) {
            foreach ($data as $field => $value) {
                $info = $field_info[$field] ?? null;
                if ($info) {
                    $type = $info['type'] ?? static::String;
                    $fn($rs, $value, $field, $type);
                } // if($info){
            } // foreach
        } else {
            foreach ($field_info as $field => $info) {
                $type  = $info['type'] ?? static::String;
                $value = $data[$field] ?? $info['default'];
                $fn($rs, $value, $field, $type);
                if (null === $rs[$field]) {
                    unset($rs[$field]);
                }
            } // foreach
        }
        return $rs;
    }


    /**
     * 数据"插入"
     *
     * @param pdo $db 数据库句柄
     * @param array $data 字段数据(要包含$primary_data里字段数据)
     * @param array $field_info 默认字段数据
     * @param array|null $primary_data 主要数据，判断是否更新还是插入
     * @param string|null $table 表名
     * @param bool $is_auto_increment 数据插入时自增长
     *
     * @return array
     */
    static public function insert(pdo $db, array $data, array $field_info, ?array $primary_data = null, ?string $table = null, bool $is_auto_increment = true, ?array $where = null)
    {
        // 格式化
        $data_format = static::format($data, $field_info, false);

        // 插入数据
        if ($is_auto_increment) {
            $insert_value = $db->table($table)->insert($data_format);
            if ($insert_value) {
                $rs = ['_type_' => 'insert', '_insert_value_' => $insert_value];
                $rs = is_array($primary_data) ? array_merge($primary_data, $rs) : $rs;
                return succeed($rs);
            } else {
                return error("失败:插入数据库失败[" . __LINE__ . "][" . json_encode_unescaped($primary_data) . "]");
            }
        } elseif ($primary_data) {
            $insert_value = $db->table($table)->insert($data_format);
            list($where_str, $where_paras) = $where ?? self::where_str_bind($primary_data, $primary_data);

            $modify_cc = $db->table($table)->where($where_str, $where_paras)->count_value();
            $rs        = ['_type_' => 'insert', '_modify_cc_' => $modify_cc, '_insert_value_' => $insert_value];
            $rs        = is_array($primary_data) ? array_merge($primary_data, $rs) : $rs;
            if ($modify_cc > 0) {
                return succeed($rs);
            } else {
                return error("失败:插入数据库失败[" . __LINE__ . "][" . json_encode_unescaped($rs) . "]");
            }
        }
        return error("失败:插入数据库失败[" . __LINE__ . "][参数有误\$is_auto_increment:" . json_encode_unescaped($is_auto_increment) . "或\$primary_data]:" . json_encode_unescaped($primary_data) . "");
    }

    /**
     * 数据"更新"
     *
     * @param pdo $db 数据库句柄
     * @param array $data 字段数据(要包含$primary_data里字段数据)
     * @param array $field_info 默认字段数据
     * @param array|null $primary_data 主要数据，判断是否更新还是插入
     * @param string|null $table 表名
     * @param array|null $where
     * @return array
     */
    static public function update(pdo $db, array $data, array $field_info, ?array $primary_data, ?string $table = null, ?array $where = null): array
    {
        // 格式化
        $data_format = static::format($data, $field_info, true);

        list($where_str, $where_paras) = $where ?? self::where_str_bind($primary_data, $primary_data);
        $modify_cc = $db->table($table)->where($where_str, $where_paras)->update($data_format);
        if ($modify_cc) {
            return succeed(array_merge($primary_data, ['_type_' => 'update', '_modify_cc_' => $modify_cc]));
        } else {
            return error("失败:更新数据库失败[" . __LINE__ . "][" . json_encode_unescaped($primary_data) . "]");
        }
    }


    /**
     * 数据自适应"更新"或"插入"
     *
     * @param pdo $db 数据
     * @param array $data 字段数据(要包含$primary_data里字段数据)
     * @param array $field_info 字段信息
     * @param array|null $primary_data 主要数据，判断是否更新还是插入
     * @param string|null $table 表名
     * @param bool $is_auto_increment 数据插入是否自增长
     *
     * @return array
     */
    static public function update_insert(pdo $db, array $data, array $field_info, ?array $primary_data = null, ?string $table = null, bool $is_auto_increment = true): array
    {
        // 检查 数据是"更新"还是"插入"
        $is_update = false;
        $where     = null;
        if ($primary_data) {
            $where = self::where_str_bind($primary_data, $primary_data);
            list($where_str, $where_paras) = $where;
            $is_update = $db->table($table)->where($where_str, $where_paras)->count_value('`' . array_keys($primary_data)[0] . '`') > 0;
        }

        // 更新数据
        if ($is_update) {
            return static::update($db, $data, $field_info, $primary_data, $table, $where);
        } else {
            // 插入数据
            return static::insert($db, $data, $field_info, $primary_data, $table,$is_auto_increment, $where);
        }
    }

    /**
     * SQL where str bind 数据生成
     *
     * @param array $fields
     * @param array|null $where_data
     * @param pdo|null $pdo
     * @return array
     */
    static public function where_str_bind(array $fields, ?array $where_data, ?pdo $pdo = null): array
    {
        // 执行
        $where_str  = [];
        $where_bind = [];
        if ($where_data) { // 请求参数不能为空
            foreach ($fields as $field => $operation) {
                if (isset($where_data[$field])) {
                    if ('=' == $operation) {
                        $where_str[]        = " `{$field}` =:{$field} ";
                        $where_bind[$field] = $where_data[$field];
                    } elseif ('>' == $operation) {
                        $where_str[]        = " `{$field}` >:{$field} ";
                        $where_bind[$field] = $where_data[$field];
                    } elseif ('<=' == $operation) {
                        $where_str[]        = " `{$field}` <=:{$field} ";
                        $where_bind[$field] = $where_data[$field];
                    } elseif ('<' == $operation) {
                        $where_str[]        = " `{$field}` <:{$field} ";
                        $where_bind[$field] = $where_data[$field];
                    } elseif ('<=' == $operation) {
                        $where_str[]        = " `{$field}` <=:{$field} ";
                        $where_bind[$field] = $where_data[$field];
                    } elseif ('like' == $operation) {
                        $where_str[]        = " `{$field}` like :{$field} ";
                        $where_bind[$field] = $where_data[$field];
                    } elseif ('%like' == $operation) {
                        $where_str[]        = " `{$field}` like :{$field} ";
                        $where_bind[$field] = "%{$where_data[$field]}";
                    } elseif ('like%' == $operation) {
                        $where_str[]        = " `{$field}` like :{$field} ";
                        $where_bind[$field] = "{$where_data[$field]}%";
                    } elseif ('%like%' == $operation) {
                        $where_str[]        = " `{$field}` like :{$field} ";
                        $where_bind[$field] = "%{$where_data[$field]}%";
                    } elseif ('between' == $operation) {
                        $where_str[]                   = " `{$field}` >:{$field}_start and `{$field}` <:{$field}_end ";
                        $where_bind[$field . '_start'] = $where_data[$field . '_start'];
                        $where_bind[$field . '_end']   = $where_data[$field . '_end'];
                    } elseif ('between=' == $operation) {
                        $where_str[]                   = " `{$field}` >=:{$field}_start and `{$field}` <=:{$field}_end ";
                        $where_bind[$field . '_start'] = $where_data[$field . '_start'];
                        $where_bind[$field . '_end']   = $where_data[$field . '_end'];
                    } elseif ('in' == $operation || 'in_str' == $operation) {
                        $vals = [];
                        if (is_array($where_data[$field])) {
                            if ('in_str' == $operation) {
                                foreach ($where_data[$field] as $val) {
                                    $vals[] = $pdo->quote($val, \PDO::PARAM_STR);
                                }
                            } else {
                                foreach ($where_data[$field] as $val) {
                                    $vals[] = (float)$val;
                                }
                            }
                            if ($vals) {
                                $where_str[] = " `{$field}` in (" . implode(',', $vals) . ") ";
                            }
                        }
                    } else { // 默认
                        $where_str[]        = " `{$field}` =:{$field} ";
                        $where_bind[$field] = $where_data[$field];
                    }
                }
            }
        }
        $where_str = implode(' and ', $where_str);
        return [$where_str, $where_bind];
    }


    /**
     * @param string $rows
     * @return array
     */
    static public function rows2array(string $rows = ''): array
    {
        $rs   = [];
        $rows = trim($rows);
        if ($rows) {
            $rows2 = explode("\n", $rows);
            if ($rows2 && is_array($rows2)) {
                foreach ($rows2 as $v) {
                    $v = trim($v);
                    if ($v) {
                        $rs[] = $v;
                    }
                }
            }
        }
        return $rs;
    }

    /**
     * 递归多维数组转为一级数组
     * @param array $array
     * @return array
     */
    static public function arrays2array(array $array): array
    {
        static $result_array = [];
        foreach ($array as $value) {
            if (is_array($value)) {
                static::arrays2array($value);
            } else {
                $result_array[] = $value;
            }
        }
        return $result_array;
    }

    /**
     * @param    $data  array|string|mixed
     * @param    $key   string
     * @param    $t     string
     * @param    $ps    boolean     true:有父级
     *                              false:没父级
     * @param    $ps_auto boolean   true:$ps无效数组多于1时加s父级 等于1时 没有父级
     *                              false:有没有父级 看$ps
     * @return  string
     */
    public static function array2xml($data, $key, $t = "", $ps = false, $ps_auto = false)
    {
        $xml = '';
        if ('#' == $key) {
            return $xml;
        } elseif (!is_array($data)) {
            if (strstr($key, '$')) {
                $key  = substr($key, 1);
                $data = stripslashes($data);
                $xml  .= "{$t}<{$key}><![CDATA[{$data}]]></{$key}>\n";
            } else {
                if (is_numeric($data)) {
                    // $data = printf("%s",$data);
                    $data = number_format($data, 0, '', '');
                }
                $xml .= "{$t}<{$key}>{$data}</{$key}>\n";
            }
        } elseif (array_keys($data) === range(0, count($data) - 1)) {
            $key2 = strstr($key, '$') ? substr($key, 1) : $key;
            if ($ps) {
                $xml .= "{$t}<{$key2}s>\n";
                foreach ($data as $data2) {
                    $xml .= static::array2xml($data2, $key, "{$t}\t", $ps, $ps_auto);
                }
                $xml .= "{$t}</{$key2}s>\n";
            } else {
                foreach ($data as $data2) {
                    $xml .= static::array2xml($data2, $key, "{$t}", $ps, $ps_auto);
                }
            }
        } else {
            if ($ps_auto) {
                $ps_c = 0;
                $ps   = false; // 是否唯一子结节，唯一子结点就不包
                foreach ($data as $key2 => $data2) {
                    if ('#' != $key2) {
                        $ps_c++;
                    }
                }
                if ($ps_c > 1) {
                    $ps = true;
                }
            }
            //////////////////////////////////////////////////////
            $v = '';
            foreach ($data as $key2 => $data2) {
                $v .= static::array2xml($data2, $key2, "{$t}\t", $ps, $ps_auto);
            }
            if (is_array($data['#'])) {
                $a = '';
                foreach ($data['#'] as $key2 => $data2) {
                    if (is_numeric($data2)) {
                        if ($data2 && strlen($data2) && '0' == substr($data2, 0, 1) && '.' != substr($data2, 1, 1)) {
                            // 0 开头的字符串
                            // $data2 = $data2;
                        } elseif ((float)$data2 != $data2) {
                            $data2 = number_format($data2, 3, '.', '');
                        } else {
                            $data2 = number_format($data2, 0, '', '');
                        }
                    }
                    $a .= " {$key2}=\"{$data2}\"";
                }
                if ($v) {
                    $xml .= "{$t}<{$key}{$a}>\n";
                    $xml .= $v;
                    $xml .= "{$t}</{$key}>\n";
                } else {
                    $xml .= "{$t}<{$key}{$a} />\n";
                }
            } else {
                if ($v) {
                    $xml .= "{$t}<{$key}>\n";
                    $xml .= $v;
                    $xml .= "{$t}</{$key}>\n";
                } else {
                    $xml .= "{$t}<{$key} />\n";
                }
            }
        }
        return $xml;
    }

    /**
     * 数据XML编码
     *
     * @param mixed $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id 数字索引key转换为的属性名
     * @return string
     */
    public static function data2xml($data, $item = 'item', $id = 'id')
    {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($val) || is_object($val)) ? static::data2xml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }
        return $xml;
    }

    /**
     * @param string $data_str 数据
     * @param string $fields 字段多个,分格
     * @param string $data_rows_delimiter 行分格符
     * @param string $data_delimiter 数据分格符
     * @param string $fields_delimiter 字段分格符
     * @return array
     */
    public static function str2array(string $data_str, string $fields, string $data_rows_delimiter = "\n", $data_delimiter = ':', string $fields_delimiter = ',')
    {
        $data        = explode($data_rows_delimiter, $data_str);
        $fields2     = explode($fields_delimiter, $fields);
        $fields2_len = count($fields2);

        $result = [];
        foreach ($data as $v) {
            $v = trim($v);
            if ($v) {
                $v_data = explode($data_delimiter, $v);
                $v_len  = count($v_data);
                if ($fields2_len == $v_len) {
                    $v_data2 = [];
                    foreach ($v_data as $k2 => $v2) {
                        $v_data2[$fields2[$k2]] = $v2;
                    }
                    $result[] = $v_data2;
                }
            }
        }
        return $result;
    }

    /**
     * @param mixed $data_str 数据
     * @param string $fields 字段多个,分格
     * @param string $data_delimiter 数据分格符
     * @param string $fields_delimiter 字段分格符
     * @return array
     */
    public static function array2str($data_str, string $fields, $data_delimiter = ':', string $fields_delimiter = ',')
    {
        $data = [];
        if (is_string($data_str)) {
            $data_str = json_decode($data_str, true);
        }
        if ($fields) {
            $fields = explode($fields_delimiter, $fields);
        }
        if ($data_str && is_array($data_str)) {
            foreach ($data_str as $v) {
                $v2 = [];
                foreach ($fields as $v3) {
                    $v2[] = $v[$v3];
                }
                $data[] = implode($data_delimiter, $v2);
            }
        }
        return $data;
    }

    /**
     * XML编码
     *
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id 数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public static function xml_encode($data, $root = 'ounun', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
    {
        if (is_array($attr)) {
            $_attr = [];
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml  = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml  .= "<{$root}{$attr}>";
        $xml  .= static::data2xml($data, $item, $id);
        $xml  .= "</{$root}>";

        return $xml;
    }

    /**
     * 创建 SimpleXMLElement 对象
     * @param $data
     * @return mixed
     */
    public static function xml_encode_simple($data)
    {
        // 创建 SimpleXMLElement 对象
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><site></site>');
        foreach ($data as $key => $value) {
            $xml->addChild($key, $value);
        }
        return $xml->asXML();
    }

    /**
     * 创建 Html 对象
     * @param $data
     * @param string $table_attributes
     * @return string
     */
    public static function html_table_encode($data, string $table_attributes = '')
    {
        if (empty($table_attributes)) {
            $table_attributes = ' style="border: darkcyan solid 1px;"';
        }
        if (is_array($data)) {
            $html = '<table' . $table_attributes . '>';
            foreach ($data as $key => $value) {
                $html .= "<tr><td>" . $key . "</td><td>" . $value . "</td></tr>";
            }
            $html .= "</table>";
            return $html;
        }
        return '';
    }
}
