<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo (C("APP_TITLE")); ?></title>
<link href="/Public/css/login.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="bg"></div>
<div class="login_Box">
	<img src="/Public/images/logo.png" class="login_Logo">
    <form class="login_Form" id="loginform" action="<?php echo U('Index/login');?>" autocomplete="off">
    	<span class="error_Tip"></span>
    	<div class="txt_C">
        	<em>用户名</em>
            <input type="text" class="txt userName_Txt" id="username" name="username">
        </div>
        <div class="txt_C">
        	<em>密码</em>
            <input type="password" class="txt password_Txt" id="password" name="password" type="password">
        </div>
        <div class="txt_C">
        	<em>验证码</em>
            <input type="text" class="txt validateCode_Txt" id="verify" name="verify">
            <a href="javascript:;" class="refresh"><img class="validateCode_Img" onclick="this.src=this.src+'?'" src="<?php echo U('Index/verify',array('width'=>92,'height'=>29));?>" title="点击换一张"></a>
        </div>
        <input type="submit" class="submit_Button" value="登 录">
    </form>
</div>
<script src="/Public/js/jquery.min.js"></script>
<script src="/Public/js/unicorn.login.js"></script> 
<script>
$(".login_Form em").click(function(){
	$(this).hide();
	$(this).next().focus()	
})
$(".login_Form .txt").each(function(){
    if($(this).val()!=""){
        $(this).prev().hide()	
    }	
});
$(".login_Form .txt").focus(function(){
	$(this).prev().hide()	
}).blur(function(){
	if($(this).val()==""){
		$(this).prev().show()	
	}	
})
</script>
</body>
</html>