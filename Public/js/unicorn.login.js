$(document).ready(function(){
	var login = $('#loginform');
	var recover = $('#recoverform');
	var speed = 400;
    if($.browser.msie == true && $.browser.version.slice(0,3) < 10) {
        $('input[placeholder]').each(function(){
            var input = $(this);
            $(input).val(input.attr('placeholder'));
            $(input).focus(function(){
                 if (input.val() == input.attr('placeholder')) {
                     input.val('');
                 }
            });
            $(input).blur(function(){
                if (input.val() == '' || input.val() == input.attr('placeholder')) {
                    input.val(input.attr('placeholder'));
                }
            });
        });
    }
    $('#username').focus();
    $('#loginform').submit(function() {
        var _this = $(this);
        $('.error_Tip').html('');
        $('.error').removeClass('error');
        if(_this.data('send') == 'off') return false;
        if(!$.trim($('#username').val())){ error('请输入用户名','username'); return false; }
        if(!$.trim($('#password').val())){ error('请输入密码','password'); return false; }
        if(!$.trim($('#verify').val())){ error('请输入验证码','verify'); return false; }
        _this.data('send','off');
        $.post($(this).attr('action'),$(this).serialize(),function(data) {
            _this.data('send','on');
            if(data.status) location.reload();
            else error(data.info,data.data);
        },'json');
        return false;
    });
    function error(_msg,_this) {
        $('.error_Tip').html(_msg);
        if(_this) {
            $('#'+_this).val('').focus();
            $('#'+_this).addClass('error');
        }
        $('#verify').next().trigger('click');
    }
});