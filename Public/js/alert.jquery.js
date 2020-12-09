(function($) {
    $.fn.alert=(function(options) {
        var _param = {content:'',title:'',width:500,height:350};
        $.extend(_param,options);
        var _this = $(this);
        _this.data('data',_param);
        _this.live('click',function() {
            var _mask = $('<div></div>').css({'background-color': '#fff',height:'100%',left:0,'opacity':0,'position':'fixed',top:0,width:'100%','z-index': 998}).addClass('alert_mask').appendTo($('body'));
            var _box = $('<div class="alert_box"></div>').css({width:_param.width, height:_param.height,'position':'fixed',top:'50%', left:'50%','margin':'-'+(_param.height/2)+'px 0 0 -'+(_param.width/2)+'px','z-index':999,'color':'#666'}).appendTo($('body'));
            $('<div class="alert_bg"></div>').css({'position':'absolute','background-color':'#000','opacity':0.2, width:_param.width, height:_param.height,'border-radius':'5px'}).appendTo(_box);
            var _body = $('<div></div>').css({'position':'absolute',width:(_param.width-40), height:(_param.height-40), 'background-color':'#fff',padding:10, 'margin':'10px 0 0 10px'}).appendTo(_box);
            var _close = $('<a class="alert_close" href="javascript:;" style="position: absolute;font-size:14px;color:#000;width:20px;height:20px;top:5px;right:0;text-decoration:none;">╳</a>').appendTo(_body);
            $('<div class="alert_title" style="font-size:14px;height:16px;"><b>'+_param.title+'</b></div>').appendTo(_body);
            var _content = $('<div class="alert_content" style="font-size:12px;width:100%;overflow:auto;height:'+(_param.height-76)+'px;padding:10px 0;"></div>').appendTo(_body);
            if ($.isFunction(_param.content)) {
                _content.html(_param.content());
            }else {
                _content.html(_param.content);
            }
            _close.click(function() {_mask.add(_box).remove();});
        });
        $.alertRemove=(function() {_mask.add(_box).remove();});
    });
})(jQuery);