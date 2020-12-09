var _is_show_ajax_loading = true;
$(document).ajaxStart(function() {
    if (_is_show_ajax_loading) {
        $('<div class="ajax-start"></div>').appendTo($('body'));
        $('<div class="ajax-start-loading"></div>').appendTo($('body'));
    }
});
$(document).submit(function() {
    if (_is_show_ajax_loading) {
        $('<div class="ajax-start"></div>').appendTo($('body'));
        $('<div class="ajax-start-loading"></div>').appendTo($('body'));
    }
});
$(window).scroll( function() {
    $('.ui-datepicker').find('select').addClass('no-select2');
    if ($('select:not(.no-select2)').length) {
        $('select').select2('close');
    }
});
$(window).unload(function() {
    remove_loading();
});
$('a').live('click',function() {
    var _href = $(this).attr('href');
    if ($(this).data('loading') == false) {}
    else {
        if (_href && !/^[#].*$/.test(_href) && !/^[j][a][v][a][s][c][r][i][p][t][:].*$/i.test(_href)) {
            if (_is_show_ajax_loading) {
                $('<div class="ajax-start"></div>').appendTo($('body'));
                $('<div class="ajax-start-loading"></div>').appendTo($('body'));
            }
        }
    }
});
function remove_loading() {
    $('.ajax-start,.ajax-start-loading').remove();
}
function ajax_loading(_param) {
    _is_show_ajax_loading = (_param ? true : false);
}
$(document).ajaxStop(function(){
   $('.ajax-start,.ajax-start-loading').remove();
});
//显示错误信息
function error(msg,_this) {
    remove_error();
    var _span = $('<span></span>').addClass('help-inline my_error').html(msg);
    _this.parents('.controls').eq(0).append(_span);
    if(_this.parents('.control-group').length > 1){
        _this.parents('.control-group').eq(0).addClass('error');
    }else {
        _this.parents('.control-group').addClass('error');
    }
    remove_loading();
}
//删除错误信息
function remove_error() { $('.my_error').remove();$('.error').removeClass('error');}
//创建蒙板层
function createbackdrop() {$('<div class="modal-backdrop in"></div>').appendTo($('body'));}
//弹框
function tipwindows(title,content,_width,_height,_close_fun,_top) {
    createbackdrop();
    content = $.isFunction(content) ? content() : content;
    var _tip_box = $('<div class="modal"></div>').appendTo($('body'));
    if(_width && _width !='auto') _tip_box.css({width:_width,'margin-left':'-'+_width/2+'px'});
    var _tip_html = '<div class="modal-header"><button data-dismiss="modal" class="close" type="button">×</button><h3>'+title+'</h3></div><div class="modal-body" style="'+(_height && _height!='auto' ? 'max-height:'+(_height > $(window).height()-80 ? $(window).height()-80 : _height)+'px' : '')+'">'+content+'</div>';
    _tip_box.html(_tip_html);
    if (_top) var _margin_top = _tip_box.outerHeight() > $(window).height() ? 0 : _tip_box.outerHeight()/2;
    else var _margin_top = 0;
    _tip_box.css({'margin-top':'-'+_margin_top+'px'});
    if(_margin_top == 0) _tip_box.css({top:77});
    $('.modal .close').click(function(){tip_close(_close_fun);});
}
//关闭弹框
function tip_close(fun) {
    $('.modal,.modal-backdrop').remove();
    if (fun && $.isFunction(fun)) fun();
}
//正则验证邮箱
function checkEmail(_email) {
    return /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/.test(_email);
}
//正则验证手机号
function checkMobile(_mobile) {
    return /^1[3-9]{1}[0-9]{9}$/.test(_mobile);
}

//正则验证URL地址,地址不需要以http开头
function checkURL(_url) {  
    return /^((https|http|ftp|rtsp|mms)?:\/\/)(.*)/.test(_url);
}

//正则验证金钱
function checkMoney(_money) {
    if(!_money) return false;
    return /^\d*\.?\d{0,2}$/.test(_money);
}

/**
 判断输入框中输入的日期格式为yyyy-mm-dd和正确的日期
*/
function IsDate(mystring) {
    var reg = /^(\d{4})-(\d{2})-(\d{2})$/;
    var str = mystring;
    var arr = reg.exec(str);
    if (str=="") return true;
    if (!reg.test(str) || (RegExp.$2 > 12) || (RegExp.$3 > 31)){
        return false;
    }
    return true;
}
//base64 加密
function base64_encode(input) {
    _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var output = "";
    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
    var i = 0;
    while (i < input.length) {
        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);
        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;
        if (isNaN(chr2)) {
            enc3 = enc4 = 64;
        } else if (isNaN(chr3)) {
            enc4 = 64;
        }
        output = output +
        _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
        _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
    }
    return output;
}
//base64 解密
function base64_decode(input) {
    _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var output = "";
    var chr1, chr2, chr3;
    var enc1, enc2, enc3, enc4;
    var i = 0;
    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
    while (i < input.length) {
        enc1 = _keyStr.indexOf(input.charAt(i++));
        enc2 = _keyStr.indexOf(input.charAt(i++));
        enc3 = _keyStr.indexOf(input.charAt(i++));
        enc4 = _keyStr.indexOf(input.charAt(i++));
        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;
        output = output + String.fromCharCode(chr1);
        if (enc3 != 64) {
            output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
            output = output + String.fromCharCode(chr3);
        }
    }
    return output;
}
//设置cookie
function setCookie(name,value){
    var Days = 30; //此 cookie 将被保存 30 天
    var exp = new Date();    //new Date("December 31, 9998");
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}
//获取cookie
function getCookie(name){
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
    if(arr != null) return unescape(arr[2]); return null;
}
//删除cookie
function delCookie(name){
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);
    if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}
//生成图片地址
function createImageUrl(img,_size) {
    _size = _size ? _size : '0_0';
    return _image_host + 'img/'+_size+'/'+base64_encode(img)+'.jpeg';
}
//图片居中显示
function image_center(_box,_width,_height) {
    _width = _width ? _width : _box.outerWidth();
    _height = _height ? _height : _box.outerHeight();
    _box.find('img').load(function() {
        var img_height = 0;
        var img_width = 0;
        var real_height = $(this).height();
        var real_width = $(this).width();
        if(real_height > real_width && real_height > _height){
            var persent = real_height / _height;
            real_height = _height;
            real_width = real_width / persent;
        }else if(real_width > real_height && real_width > _width){
            var persent = real_width / _width;
            real_width = _width;
            real_height = real_height / persent;
        }
        if(real_height < _height){
            img_height = (_height - real_height)/2;
        }
        if(real_width < _width){
            img_width = (_width - real_width)/2;
        }
        $(this).css({'margin':'0 auto'});
        _box.css({height:(_height-img_height)+'px',paddingTop:img_height+'px'});
    });
}
//字符串分割为数组
function explode(_str,_arr) {
    if(!_arr) return [];
    return _arr.split(_str);
}
//数组变成字符串
function implode(_str,_arr) {
    var _return = '';
    for (var i=0; i<_arr.length; i++) {
        _return += _arr[i]+(i == (_arr.length-1) ? '' : _str);
    }
    return _return;
}
//选择商品公共函数依赖的全局变量
var _choose_goods_common_goods_ids = [];
var _choose_goods_common_goods_names = [];
//选择商品公共函数
//参数1：_ids: 初始化goods_id 支持2种写法，数组和字符串
//参数2：回调函数名称 例如上面的 test
//参数3：类型 1：表示单选  其他多选  默认多选
/*--------------------------------------------*/
function choose_goods(_ids,_callback,_type,_url) {
    if (_ids) {
        if ($.isArray(_ids)) _choose_goods_common_goods_ids = _ids;
        else _choose_goods_common_goods_ids = _ids.split(',');
        if (_choose_goods_common_goods_ids.length > 0 && _type == 1) {
            var _choose_goods_sku_temp = _choose_goods_common_goods_ids[0];
            _choose_goods_common_goods_ids = [];
            _choose_goods_common_goods_ids.push(_choose_goods_sku_temp);
        }
    }else {
        _choose_goods_common_goods_ids = [];
        _choose_goods_common_goods_names = [];
    }
    var _return = '';
    $.ajaxSetup({async:false});
    $.post(_root+(_url ? _url : '/Base/choose_goods'),{type:_type,callback:_callback},function(data){_return = data.info;},'json');
    tipwindows('选择商品（'+(_type == 1 ? '单选）' : '多选）'),_return,900,550);
    if(_url) $('.modal').css({'margin-top':0,top:0});
}

//选择商家公共函数依赖的全局变量
var _choose_shops_common_shops_ids = [];
var _choose_shops_common_shops_names = [];
//选择商家公共函数
//参数1：_ids: 初始化shops_id 支持2种写法，数组和字符串
//参数2：回调函数名称 例如上面的 test
//参数3：类型 1：表示单选  其他多选  默认多选
/*--------------------------------------------*/
function choose_shops(_ids,_callback,_type,_url) {
    if (_ids) {
        if ($.isArray(_ids)) _choose_shops_common_shops_ids = _ids;
        else _choose_shops_common_shops_ids = _ids.split(',');
        if (_choose_shops_common_shops_ids.length > 0 && _type == 1) {
            var _choose_goods_sku_temp = _choose_shops_common_shops_ids[0];
            _choose_shops_common_shops_ids = [];
            _choose_shops_common_shops_ids.push(_choose_goods_sku_temp);
        }
    }else {
        _choose_shops_common_shops_ids = [];
        _choose_shops_common_shops_names = [];
    }
    var _return = '';
    $.ajaxSetup({async:false});
    $.post(_root+(_url ? _url : '/Base/choose_shops'),{type:_type,callback:_callback},function(data){_return = data.info;},'json');
    tipwindows('选择商家（'+(_type == 1 ? '单选）' : '多选）'),_return,1100,550);
    if(_url) $('.modal').css({'margin-top':0,top:0});
}

//选择优惠券公共函数依赖的全局变量
var _choose_coupon_common_coupon_ids = [];
var _choose_coupon_common_coupon_names = [];
//选择商家公共函数
//参数1：_ids: 初始化shops_id 支持2种写法，数组和字符串
//参数2：回调函数名称 例如上面的 test
//参数3：类型 1：表示单选  其他多选  默认多选
/*--------------------------------------------*/
function choose_coupon(_ids,_callback,_type,_url) {
    if (_ids) {
        if ($.isArray(_ids)) _choose_coupon_common_coupon_ids = _ids;
        else _choose_coupon_common_coupon_ids = _ids.split(',');
        if (_choose_coupon_common_coupon_ids.length > 0 && _type == 1) {
            var _choose_goods_sku_temp = _choose_coupon_common_coupon_ids[0];
            _choose_coupon_common_coupon_ids = [];
            _choose_coupon_common_coupon_ids.push(_choose_goods_sku_temp);
        }
    }else {
        _choose_coupon_common_coupon_ids = [];
        _choose_coupon_common_coupon_names = [];
    }
    var _return = '';
    $.ajaxSetup({async:false});
    $.post(_root+(_url ? _url : '/Base/choose_coupon'),{type:_type,callback:_callback},function(data){_return = data.info;},'json');
    tipwindows('选择优惠券（'+(_type == 1 ? '单选）' : '多选）'),_return,1100,550);
    if(_url) $('.modal').css({'margin-top':0,top:0});
}
/*--------------------------------------------------------------弹出框-------------------------------------------------------*/
function createBox(){
    $('<div></div>').css({'background-color': '#fff',height:'100%',left:0,'opacity':0.00001,'position':'fixed',top:0,width:'100%','z-index': 998}).addClass('mask').appendTo($('body'));
    var _box = $('<div class="export_box"></div>').css({width:350, height:200,'position':'fixed',top:'50%', left:'50%','margin':'-100px 0 0 -175px','z-index':999}).appendTo($('body'));
    $('<div class="export_bg"></div>').css({'position':'absolute','background-color':'#000','opacity':0.3, width:350, height:200,'border-radius':'5px'}).appendTo(_box);
    $('<div></div>').css({'position':'absolute',width:310, height:160, 'background-color':'#fff',padding:10, 'margin':'10px 0 0 10px'}).html('<div class="export_title"><span class="page_count"></span><a style="float:right;margin-top:-5px;font-size:24px;" href="javascript:;" class="export_close">╳</a></div><div class="export_content" style="overflow:auto;height:146px;"></div>').appendTo(_box);
}
function resizeheight() { $('.export_content').scrollTop(10000);}
function createLog(_content) {
    $('<div></div>').html(_content).appendTo($('.export_content'));
    resizeheight();
}
$(".export_close").live('click',function(){
    $('.mask').remove(); $('.export_box').remove();
});
