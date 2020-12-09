<?php
namespace Org\My;
class Curl{
	/**
	 * curl get 方式获取数据
	 *
	 * @param string $url
	 */
	public static function get($url)
	{
            $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXY, C('PROXY_IP'));
            curl_setopt($ch, CURLOPT_PROXYPORT, C('PROXY_PORT'));
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
	    $tmp = curl_exec ($ch);
		curl_close($ch);
		return $tmp;
	}
	/**
	 * 要请求的post
	 *
	 * @param string 	$url 	要请求的url
	 * @param array 	$params 要传递的参数数组
     * @param int 	    $time   超时时间，缺省10秒
	 */
    public static function post($url,$params,$header=array(),$time=10) {
        if(is_array($params)) $paramsStr =  http_build_query($params);
        else $paramsStr = $params;
        $paramsStr = htmlspecialchars_decode($paramsStr);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsStr);
        curl_setopt($ch, CURLOPT_PROXY, C('PROXY_IP'));
        curl_setopt($ch, CURLOPT_PROXYPORT, C('PROXY_PORT'));
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        if(count($header) > 0) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $returnValue = curl_exec($ch);
        curl_close($ch);
        return $returnValue;
    }
    
    /**
     * 生成curl get方式请求完整URL
     * @param  string     $url     请求的api节点URL
     * @param  array      $params  请求的api节点的参数
     * @return string
    **/
    public function create_get_url($url,$params=array()) {
        $params_str = http_build_query($params);
        return $params ? $url.'?'.$params_str : $url;
    }
}