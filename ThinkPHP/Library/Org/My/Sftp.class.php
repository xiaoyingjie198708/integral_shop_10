<?php
namespace Org\My;

/**
* @note   通过sftp同步文件
* @author zhangchong <zhangchong@social-touch.com>
* @date   2016-11-04 15:38:20
**/
class Sftp {
    private $config;
    /**
    * @note   构造函数
    * @access public
    * @author zhangchong <zhangchong@social-touch.com>
    * @date   2016-11-04 15:38:53
    * @param  array  config  配置文件  host，port，username，password
    **/
    public function __construct ($config=[])
    {
        if (empty($config)) {
            throw new \Exception('缺少配置信息：config');
        }
        $this->config = $config;
    }
    /**
    * @note   发送文件
    * @access public
    * @author zhangchong <zhangchong@social-touch.com>
    * @date   2016-11-04 16:39:32
    * @param  string  local  本地文件物理地址
    * @param  string  remote  远程文件物理地址
    * @return boolean
    **/
    public function send ($local, $remote)
    {
        if (!function_exists('ssh2_connect')) {
            throw new \Exception('缺少ssh2扩展');
        }
        $host = $this->config['host'];
        $port = $this->config['port'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $conn = ssh2_connect($host, $port);
        if(ssh2_auth_password($conn, $username, $password)) {
            $sftp = ssh2_sftp($conn);
        } else {
            throw new \Exception('ssh验证失败');
        }
        ssh2_sftp_mkdir($sftp, dirname($remote), 0777, true);
        $bool = ssh2_scp_send($conn, $local, $remote, 0777);  //上传文件
        return $bool;
    }
}