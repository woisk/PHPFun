<?php
/**
 * 校验类
 */

class Verify
{
    /**
     * 密钥
     * @var array
     */
    static private $privateKey = array(
        'iphone' => 'a85bb0674e08986c6b115d5e3a4884fa',
        'android' => 'fd4ad5fcfa0de589ef238c0e7331b585',
        'ipad' => 'ad9fcda2e679cf9229e37feae2cdcf80',
        'web' => '0ed29744ed318fd28d2c07985d3ba633',
        'h5' => 'fd4ad5fcfa0de589ef238c0e7331b585',
        'other' => '6tjjbg7ecrcd3ulgqizbqavfrutixhm7',
    );

    /**
     * 初始化
     * @param array $params
     * @throws \Exception
     */
    static public function init(array $params)
    {
        if (defined("VERIFYSIGN") && !VERIFYSIGN){
            return true;
        }
        if(!isset($params['secret'])){
            return false;
        }
        $clientSecret = $params['secret'];
        $params['client_type'] = 'iphone';//暂定一种类型
        $params['private_key'] = self::$privateKey[strtolower($params['client_type'])];
        unset($params['secret'],$params['client_type']);

        $_params = self::packageSort($params);
        $_makeKey = self::makeSign($_params);
        $verifySign = self::verifySign($_makeKey, $clientSecret);
        return $verifySign;
    }

    /**
     * 排序参数
     * @param array $package
     * @return array
     */
    static protected function packageSort(array $package)
    {
        ksort($package);
        reset($package);
        return $package;
    }

    /**
     * 组合签名
     * @param array $package
     * @return string
     */
    static protected function makeSign(array $package)
    {
        $packageList = array();
        foreach ($package as $key => $val) {
            $packageList[] = trim($key . '=' . urldecode($val));
        }
        return strtolower(md5(implode('&', $packageList)));
    }

    /**
     * 校验签名
     * @param $submitSign
     * @param $makeSign
     * @return bool
     */
    static protected function verifySign($submitSign, $makeSign)
    {
        return strtolower($submitSign) == strtolower($makeSign);
    }

    /**
     * 加密
     * @param array $params
     * @return array
     */
    static public function encrypt(array $params)
    {
        if (defined("VERIFYSIGN") && !VERIFYSIGN){
            return $params;
        }
        $params['client_type'] = 'iphone';//暂定一种类型
        $params['private_key'] = self::$privateKey[strtolower($params['client_type'])];
        unset($params['secret'],$params['client_type']);

        $_params = self::packageSort($params);
        $_makeKey = self::makeSign($_params);
        $params['secret'] = $_makeKey;
        unset($params['private_key']);
        return $params;
    }

    /**
     * 弹幕服务请求外部api的加密规则
     * @param  array  $params 参数
     * @return array
     */
    static public function encrypt_api(array $params)
    {
        $params['private_key'] = self::$privateKey[strtolower($params['client_type'])];
        unset($params['client_secret']);
        $_params = self::packageSort($params);
        $_makeKey = self::makeSign($_params);
        $params['client_secret'] = $_makeKey;
        unset($params['private_key']);
        return $params;
    }
}