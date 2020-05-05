<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\db;


class utils
{
    /** @var string 整数类型 */
    const Type_Int = 'int';
    /** @var string 浮点类型 */
    const Type_Float = 'float';
    /** @var string 布尔类型 */
    const Type_Bool = 'bool';
    /** @var string JSON类型 */
    const Type_Json = 'json';
    /** @var string 字符串类型 */
    const Type_String = 'string';
    /** @var string 枚举 - 字段类型 */
    const Type_Enum = 'enum';
    /** @var string 下线(子集) - 字段类型 */
    const Type_Child = 'child';

    /** @var string 日期转秒数0点0分 */
    const Type_Date2Time_00 = 'd2t00';
    /** @var string 日期转秒数23点59分59秒 */
    const Type_Date2Time_24 = 'd2t24';
    /** @var string 日期时间转秒数 */
    const Type_String2Time = 's2t';

    /**
     * 数据库bind
     * @param array $bind_data    字段数据
     * @param array $data_default 默认字段数据
     * @param bool $is_update true:更新  false:插入
     * @param bool $is_update_default 数据插入 -> 本字段无效，
     *                                           数据更新 -> true:已默认字段数据为主  false:已字段数据为主
     * @return array
     */
    static public function bind(array $bind_data, array $data_default, bool $is_update = true, bool $is_update_default = false)
    {
        // extend ext
        if($bind_data['ext'] && is_array($bind_data['ext'])){
            if($bind_data['extend'] && is_string($bind_data['extend'])) {
                $bind_data['extend'] = json_decode_array($bind_data['extend']);
            }else{
                $bind_data['extend'] = (array)$bind_data['extend'];
            }
            if(!is_array($bind_data['extend'])){
                $bind_data['extend'] = [];
            }
            foreach ($bind_data['ext'] as $k=>$v){
                if($v){
                    $bind_data['extend'][$k]=$v;
                }
            }
        }
        // bind
        $bind = [];
        if ($is_update) {
            $fields = $is_update_default ? array_keys($data_default) : array_keys($bind_data);
        } else {
            $fields = array_keys($data_default);
        }
        foreach ($fields as $field) {
            $value = $data_default[$field];
            if ($value && isset($bind_data[$field])) {
                if (static::Type_Int == $value['type']) {
                    $bind[$field] = (int)$bind_data[$field];
                } elseif (static::Type_Float == $value['type']) {
                    $bind[$field] = (float)$bind_data[$field];
                } elseif (static::Type_Bool == $value['type']) {
                    $bind[$field] = $bind_data[$field] ? true : false;
                } elseif (static::Type_Json == $value['type']) {
                    $extend = $bind_data[$field];
                    if(is_array($extend) || is_object($extend)){
                        $bind[$field] = json_encode_unescaped($extend);
                    }else{
                        $extend = json_decode_array($bind_data[$field]);
                        if ($extend) {
                            $bind[$field] = json_encode_unescaped($extend);
                        }
                    }
                } elseif (static::Type_Date2Time_00 == $value['type']) {
                    $value_data  = $bind_data[$field];
                    if(empty($value_data)){
                        $value_data = $value['default'];
                    }
                    $bind[$field] = strtotime($value_data . " 00:00:00");
                } elseif (static::Type_Date2Time_24 == $value['type']) {
                    $value_data  = $bind_data[$field];
                    if(empty($value_data)){
                        $value_data = $value['default'];
                    }
                    $bind[$field] = strtotime($value_data . " 23:59:59") + 1;
                } elseif (static::Type_String2Time == $value['type']) {
                    $value_data  = $bind_data[$field];
                    if(empty($value_data)){
                        $value_data = $value['default'];
                    }
                    $bind[$field] = strtotime($value_data);
                } else {
                    $bind[$field] = (string)$bind_data[$field];
                }
            } elseif ($value) {
                if (static::Type_Json == $value['type']) {
                    $extend = $value['default'];
                    if(is_array($extend) || is_object($extend)){
                        $bind[$field] = json_encode_unescaped($extend);
                    }else{
                        $extend = json_decode_array($bind_data[$field]);
                        if ($extend) {
                            $bind[$field] = json_encode_unescaped($extend);
                        }
                    }
                }elseif (static::Type_Date2Time_00 == $value['type']) {
                    $value_data = $value['default'];
                    $bind[$field] = strtotime($value_data . " 00:00:00");
                } elseif (static::Type_Date2Time_24 == $value['type']) {
                    $value_data = $value['default'];
                    $bind[$field] = strtotime($value_data . " 23:59:59") + 1;
                } elseif (static::Type_String2Time == $value['type']) {
                    $value_data = $value['default'];
                    $bind[$field] = strtotime($value_data);
                } elseif(null === $value['default']){
                    unset($bind[$field]);
                } else {
                    $bind[$field] = $value['default'];
                }
            }
            // echo "\$field:{$field} \$value['type']:{$value['type']} \$bind[\$field]:{$bind[$field]} \$value:".json_encode_unescaped($value)."\n";
        }
        return $bind;
    }

    /**
     * @param pdo $db
     * @param string $table
     * @param string $field         字段名称 有 `
     * @param string|int $id
     * @param array $bind_data      字段数据
     * @param array $bind_default   默认字段数据
     * @param bool $is_update_force        只是数据更新
     * @param bool $is_update_default      数据插入 -> 本字段无效，数据更新 -> true:已默认字段数据为主  false:已字段数据为主
     * @param bool $is_not_auto_increment  只是数据插入(无自增长)
     * @param string $field2               字段名称 没有 `
     * @return array|int
     */
    static public function update(pdo $db, string $table, string $field, $id, array $bind_data, array $bind_default,
                                  bool $is_update_force = false, bool $is_update_default = false,
                                  bool $is_not_auto_increment = false,string $field2 = '')
    {
        $is_update = true;
        if ($id) {
            if ($is_update_force == false) {
                $is_update = $db->table($table)->exists($field, $id);
            }
        } else {
            $is_update = false;
        }

        $bind = static::bind($bind_data, $bind_default, $is_update, $is_update_default);

        if ($is_update) {
            $modify_cc = $db->table($table)->where(" {$field} =:field  ", ['field' => $id])->update($bind);
            if ($modify_cc) {
                return $id;
            } else {
                return error("失败:更新数据库失败[".__LINE__."]\$table:{$table} \$id:{$id}");
            }
        } else {
            if($is_not_auto_increment){
                $field2 = $field2?$field2:str_replace(['`',' '],'',$field);
                if($id){
                    $bind[$field2] = $id;
                }else{
                    $id = $bind[$field2];
                }
                $db->table($table)->insert($bind);
                $modify_cc = $db->table($table)->exists($field, $id);
                if ($modify_cc) {
                    return $id;
                } else {
                    return error("失败:插入数据库失败[".__LINE__."]\$table:{$table} \$id:{$id}");
                }
            }else{
                $id = $db->table($table)->insert($bind);
                if ($id) {
                    return $id;
                } else {
                    return error("失败:插入数据库失败[".__LINE__."]\$table:{$table} \$id:{$id}");
                }
            }
        }
    }


    /**
     * @param string $rows
     * @return array
     */
    static public function rows2array(string $rows = '')
    {
        $rs   = [];
        $rows = trim($rows);
        if($rows){
            $rowss = explode("\n",$rows);
            if($rowss &&  is_array($rowss)){
                foreach ($rows  as $v){
                    $v = trim($v);
                    if($v){
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
     * @param    $ps    boolean        true:有父级
     *                               false:没父级
     * @param    $ps_auto boolean   true:$ps无效数组多于1时加s父级 等于1时 没有父级
     *                               false:有没有父级 看$ps
     * @return  string
     */
    public static function array2xml($data, $key, $t = "", $ps = false, $ps_auto = false)
    {
        $xml = '';
        if ('#' == $key) {
            return $xml;
        } elseif (!is_array($data)) {
            if (strstr($key, '$')) {
                $key = substr($key, 1);
                $data = stripslashes($data);
                $xml .= "{$t}<{$key}><![CDATA[{$data}]]></{$key}>\n";
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
                $ps = false; // 是否唯一子结节，唯一子结点就不包
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
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id 数字索引key转换为的属性名
     * @return string
     */
    public static  function data2xml($data, $item = 'item', $id = 'id')
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
        $data = explode($data_rows_delimiter, $data_str);
        $fields2 = explode($fields_delimiter, $fields);
        $fields2_len = count($fields2);

        $result = [];
        foreach ($data as $v) {
            $v = trim($v);
            if ($v) {
                $v_data = explode($data_delimiter, $v);
                $v_len = count($v_data);
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
     * @param mixed  $data_str   数据
     * @param string $fields     字段多个,分格
     * @param string $data_delimiter    数据分格符
     * @param string $fields_delimiter  字段分格符
     * @return array
     */
    public static function array2str($data_str,string $fields,$data_delimiter = ':', string $fields_delimiter = ',')
    {
        $data = [];
        if(is_string($data_str)){
            $data_str = json_decode($data_str, true);
        }
        if($fields){
            $fields = explode($fields_delimiter,$fields);
        }
        if($data_str && is_array($data_str)){
            foreach ($data_str as $v) {
                $v2     = [];
                foreach ($fields as $v3){
                    $v2[] =  $v[$v3];
                }
                $data[] = implode($data_delimiter,$v2);
            }
        }
        return $data;
    }

    /**
     * XML编码
     * @param mixed  $data 数据
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
        $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml .= "<{$root}{$attr}>";
        $xml .= static::data2xml($data, $item, $id);
        $xml .= "</{$root}>";

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
        foreach($data as $key=> $value) {
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
    public static function html_table_encode($data,string $table_attributes = '') {
        if(empty($table_attributes)){
            $table_attributes = ' style="border: darkcyan solid 1px;"';
        }
        if(is_array($data)){
            $html = '<table'.$table_attributes.'>';
            foreach($data as $key => $value) {
                $html .= "<tr><td>". $key. "</td><td>". $value. "</td></tr>";
            }
            $html .= "</table>";
            return $html;
        }
        return '';
    }
}
