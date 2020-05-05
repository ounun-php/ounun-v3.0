<?php


namespace ounun\plugin;


use ounun\utils\caiji;

class translator
{
    public static $langs=[
        'zh'=>'中文',
        'en'=>'英语',
        'fra'=>'法语',
        'jp'=>'日语',
        'kor'=>'韩语',
        'de'=>'德语',
        'ru'=>'俄语',
        'spa'=>'西班牙语',
        'pt'=>'葡萄牙语',
        'it'=>'意大利语',
        'ara'=>'阿拉伯语',
        'th'=>'泰语',
        'el'=>'希腊语',
        'nl'=>'荷兰语',
        'pl'=>'波兰语',
        'bul'=>'保加利亚语',
        'est'=>'爱沙尼亚语',
        'dan'=>'丹麦语',
        'fin'=>'芬兰语',
        'cs'=>'捷克语',
        'rom'=>'罗马尼亚语',
        'slo'=>'斯洛文尼亚语',
        'swe'=>'瑞典语',
        'hu'=>'匈牙利语',
        'tr'=>'土耳其语',
        'id'=>'印尼语',
        'ms'=>'马来西亚语',
        'vie'=>'越南语',
        'yue'=>'粤语',
        'wyw'=>'文言文',
        'cht'=>'繁体中文'
    ];

    public static $allow_langs = [
        'baidu' => [
            'zh'=>'zh',
            'en'=>'en',
            'fra'=>'fra',
            'jp'=>'jp',
            'kor'=>'kor',
            'de'=>'de',
            'ru'=>'ru',
            'spa'=>'spa',
            'pt'=>'pt',
            'it'=>'it',
            'ara'=>'ara',
            'th'=>'th',
            'el'=>'el',
            'nl'=>'nl',
            'pl'=>'pl',
            'bul'=>'bul',
            'est'=>'est',
            'dan'=>'dan',
            'fin'=>'fin',
            'cs'=>'cs',
            'rom'=>'rom',
            'slo'=>'slo',
            'swe'=>'swe',
            'hu'=>'hu',
            'vie'=>'vie',
            'yue'=>'yue',
            'wyw'=>'wyw',
            'cht'=>'cht'
        ],
        'youdao' =>[
            'zh' => 'zh-CHS',
            'en' => 'en',
            'jp' => 'ja',
            'kor' => 'ko',
            'fra' => 'fr',
            'spa' => 'es',
            'pt' => 'pt',
            'it' => 'it',
            'ru' => 'ru',
            'vie'=>'vi',
            'de'=>'de',
            'ara'=>'ar',
            'id'=>'id',
        ],
        'qq' => [
            'zh' => 'zh',
            'en' => 'en',
            'jp' => 'jp',
            'kor' => 'kr',
            'de' => 'de',
            'fra' => 'fr',
            'spa' => 'es',
            'it' => 'it',
            'tr' => 'tr',
            'ru' => 'ru',
            'pt' => 'pt',
            'vie' => 'vi',
            'id' => 'id',
            'ms' => 'ms',
            'th' => 'th',
            'cht' => 'zh-TW'
        ]
    ];

    /**
     * 翻译入口
     * @param $q
     * @param $from
     * @param $to
     * @return mixed
     */
    public static function translate($q,$from,$to){
        $transConf=$GLOBALS['_sc']['c']['translate'];
        if(empty($from)||empty($to)){

            return $q;
        }
        $apiType=strtolower($transConf['api']);
        if(empty($apiType)){

            return $q;
        }

        $allowLangs=self::$allow_langs[$apiType];
        if(empty($allowLangs)){

            return $q;
        }
        $from=$allowLangs[$from];
        $to=$allowLangs[$to];
        if(empty($from)||empty($to)){

            return $q;
        }
        if($from==$to){
            return $q;
        }


        if('baidu'==$apiType){
            $return=self::api_baidu($q, $from, $to);
        }elseif('youdao'==$apiType){
            $return=self::api_youdao($q, $from, $to);
        }elseif('qq'==$apiType){
            $return=self::api_qq($q, $from, $to);
        }
        return $return['success']?$return['data']:$q;
    }

    /*百度翻译接口*/
    public static function api_baidu($q,$from,$to){
        $apiConf=$GLOBALS['_sc']['c']['translate']['baidu'];

        $salt = time ();
        $sign = $apiConf['appid'] . $q . $salt . $apiConf['key'];
        $sign = md5 ( $sign );
        $data = caiji::html_get( 'https://api.fanyi.baidu.com/api/trans/vip/translate',
            null, null,'utf-8',array('from'=>$from,'to'=>$to,'appid'=>$apiConf['appid'],'salt'=>$salt,'sign'=>$sign,'q'=>$q));
        $data = json_decode ( $data );

        $return=array('success'=>false);
        if($data->error_code){
            $return['error']='error:'.$data->error_code.'-'.$data->error_msg;
        }else{
            $transData = '';
            foreach ( $data->trans_result as $trans ) {
                $transData .= $trans->dst."\r\n";
            }
            if ($transData) {
                $return['success']=true;
                $return['data']=$transData;
            }
        }

        return $return;
    }
    /*有道翻译接口*/
    public static function api_youdao($q,$from,$to){
        $apiConf=$GLOBALS['_sc']['c']['translate']['youdao'];

        $salt = time ();
        $sign = $apiConf['appkey'] . $q . $salt . $apiConf['key'];
        $sign = md5 ( $sign );
        $data = caiji::html_get ( 'https://openapi.youdao.com/api',
            null, null,'utf-8',array('from'=>$from,'to'=>$to,'appKey'=>$apiConf['appkey'],'salt'=>$salt,'sign'=>$sign,'q'=>$q));
        $data = json_decode ( $data );

        $return=array('success'=>false);
        if(!empty($data->errorCode)){
            $return['error']='error:'.$data->errorCode;
        }else{
            $transData = '';
            foreach ( $data->translation as $trans ) {
                $transData .= $trans."\r\n";
            }
            if ($transData) {
                $return['success']=true;
                $return['data']=$transData;
            }
        }
        return $return;
    }

    /*腾讯翻译接口*/
    public static function api_qq($q,$from,$to)
    {
        $api_conf   = \ounun::$global['translate']['qq'];

        $secret_id  = $api_conf['secretid'];
        $secret_key = $api_conf['secretkey'];



        $param=[];
        $param["Nonce"] = rand();
        $param["Timestamp"] = time();
        $param["Region"] = "ap-shanghai";
        $param["SecretId"] = $secret_id;
        $param["Action"] = "TextTranslate";
        $param["Version"] = "2018-03-21";
        $param["SourceText"] = $q;
        $param["Source"] = $from;
        $param["Target"] = $to;
        $param['ProjectId']='0';


        ksort($param);

        $sign_str = "GETtmt.ap-shanghai.tencentcloudapi.com/?";
        foreach ( $param as $key => $value ) {
            $sign_str = $sign_str . $key . "=" . $value . "&";
        }
        $sign_str = substr($sign_str, 0, -1);


        $param['Signature'] = base64_encode(hash_hmac("sha1", $sign_str,$secret_key, true));

        $return = ['success'=>false];


        ksort($param);

        $url='';
        foreach ( $param as $key => $value ) {
            $url = $url . $key . "=" . urlencode($value) . "&";
        }
        $url  = trim($url,'&');
        $data = caiji::html_get('https://tmt.'.$param["Region"].'.tencentcloudapi.com/?'.$url, null, null,'utf-8');
        $data = json_decode ( $data,true );

        if(!empty($data['Response']['TargetText'])){
            $return['success']=true;
            $return['data']=$data['Response']['TargetText'];
        }
        return $return;
    }

    public static function api_langs_get($api_type){
        $allowLangs=self::$allow_langs[$api_type];
        if(!empty($allowLangs)&&is_array($allowLangs)){
            foreach($allowLangs as $k=>$v){
                if(empty(self::$langs[$k])){
                    unset($allowLangs[$k]);
                }else{
                    $allowLangs[$k]=self::$langs[$k];
                }
            }
        }
        return is_array($allowLangs)?$allowLangs:null;
    }
}
