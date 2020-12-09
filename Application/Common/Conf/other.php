<?php
return array(
    'AUTH_CONFIG'=>array( //权限认证配置
        'AUTH_ON' => true, //认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
        'AUTH_GROUP' => 'tm_admin_group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'tm_admin_group_access', //用户组明细表
        'AUTH_RULE' => 'tm_admin_rule', //权限规则表
        'AUTH_USER' => 'tm_admin_user',//用户信息表
        'AUTH_DEFAULT_CHECK'=>true,  //没有加入规则表的默认有权限访问，false：没有权限
    ),
    'TYPE_ID'=>'c6427db9-e03c-450b-92a5-e8f3a0270e33',//默认商品类型
    'UPLOAD_ROOT_PATH'=>'./Public/uploads/', //上传文件跟目录
    'UPLOAD_DEFAULT_SIZE'=>1007200, //默认上传大小 300k
    'UPLOAD_DEFAULT_EXT'=>array('jpg','png','gif','zip'), //默认上传后缀
    'FILE_TYPE'=>array( //上传文件类型
        'other'=>array('size'=>1007200,'ext'=>array('jpg','png','gif')),//300K
        'activity_file'=>array('size'=>20971520,'ext'=>array('zip')),//20M
        'pdf_file'=>array('size'=>20971520,'ext'=>array('pdf')),//20M
    ),
    'LOG_DIR'=>'./Public/logs/',
    'upload_sync_config'=>[ //远程服务器的配置，一个数组元素是一个机器
        [
            'host'=>'192.168.9.140',
            'port'=>'22',
            'username'=>'root',
            'password'=>'!QAZ2wsx',
            'upload_remote_url'=>'/djy/shop-static.chncpa.org/uploads',  
            //远程服务器 图片 存放根目录
        ],
        [
            'host'=>'192.168.9.139',
            'port'=>'22',
            'username'=>'root',
            'password'=>'!QAZ2wsx',
            'upload_remote_url'=>'/djy/shop-static.chncpa.org/uploads',  
            //远程服务器 图片 存放根目录
        ],
        //多个服务器往下加就可以了
    ],
);
?>