/*
* 上传插件
* @author zhangchong
* @time 2014/6/16
* @return json对象
* @params options = {
*         url:'upload.php', // 上传地址
*         success:function(data) {
*             //上传成功回调函数 data:json对象
*         },
*         error:function() {
*             //上传错误回调函数 默认alert('错误信息');
*         }}
*/
(function($) {
    $.fn.upload=(function(options) {
        var _this = $(this);
        var _param = {
                url:_image_host+'upload',
                input_name:'upload_file[]',
                iframe_id:'upload_iframe',
                multiple:false,
//                domain:(_domain ? _domain : 'twomi.cn'),
                params:'',
                loading:'',
                start:'',
                success:'',
                error:function(data){alert(data);}
            };
        $.extend(_param,options);
        _this.setConfig(_param); //设置参数
        if(!_this.data('url')) return _this.data('error')('url is empty!');
        var _input = _this.createUploadBox(); //创建iframe和form
        _this.setConfig(_param); //设置参数
        _input.change(function() {
            if($.isFunction(_this.data('start'))) {
                var _bool = _this.data('start')(_this);
                if(_bool === false) return false;
            }
            _this.createLoadingBox(); //设置loading
            $(this).parents('form').submit(); //表单提交
            _this.getCallbackData(); //获取上传返回数据，并执行回调函数
        });
    });
    $.fn.getCallbackData=(function() {
        var _this = $(this);
        $('#'+_this.data('iframe_id')+'_'+_this.data('sort')).bind('load',function() {
            _this.removeLoadingBox(); //加载完成删除loading
            $('.ajax-start,.ajax-start-loading').remove(); //删除loading
//            if(_this.data('domain')) document.domain = _this.data('domain');
            var _data = $(this).contents().find('body').html();
            if (_data) {
                try {
                    _data = $.parseJSON(_data);
                    if(_data.status == 1){
                        if (_this.data('success')) {
                            _this.data('success')(_data.data);
                            var _input = $('#upload_box_div_'+_this.data('sort')).find('input[type=file]');
                            var _input_clone = _input.clone(true);
                            _input.after(_input_clone);
                            _input.remove();
                            _input = _input_clone;
                        }
                    }else _this.data('error')(_data.info);
                }catch (e) {
                    _this.data('error')(e)
                }
            }else {
                _this.data('error')('upload fail!');
            }
            $(this).unbind('load');
        });
    });
    $.fn.createLoadingBox=(function() {
        var _loading = $('<div></div>').css({'opacity':0.8,top:$(this).offset().top,left:$(this).offset().left,width:$(this).outerWidth(),height:$(this).outerHeight(),'position':'absolute','z-index':'999999','font-size':'12px','text-align':'center','line-height':$(this).outerHeight()+'px','background':'#fff','color':'#000'}).attr('id','upload_loading_box_'+$(this).data('sort')).appendTo($('body'));
        if($(this).data('loading')) _loading.css('background','url('+$(this).data('loading')+') center center no-repeat #fff');
        else _loading.html('uploading…');
        return _loading;
    });
    $.fn.removeLoadingBox=(function() {
        $('#upload_loading_box_'+$(this).data('sort')).remove();
    });
    $.fn.setConfig=(function(_param) {
        for (var param in _param ) $(this).data(param,_param[param]);
        $(this).setOtherParams(); //设置其他参数
    });
    $.fn.unupload=(function() {
        $('#'+$(this).data('iframe_id')+'_'+$(this).data('sort')).remove();
        $('#upload_box_div_'+$(this).data('sort')).remove();
        $(this).data('sort','');
    });
    $.fn.createUploadBox=(function() {
        var _this = $(this);
        var _index = $('.upload_iframe').length+1;
        _this.data('sort',_index);
        var _iframe = $('<iframe class="upload_iframe" id="'+_this.data('iframe_id')+'_'+_index+'" name="'+_this.data('iframe_id')+'_'+_index+'" frameborder="0" style="display:none;"></iframe>').appendTo($('body'));
        var _div = $('<div class="upload_box_div" id="upload_box_div_'+_index+'" style="width:20px;height:20px;padding:0;margin:0;overflow:hidden; position:absolute;top:-10000px;left:-10000px;z-index:99999;"></div>').css({'opacity':0}).appendTo($('body'));
        var _form = $('<form method="post" action="'+_this.data('url')+'" enctype="multipart/form-data" target="'+_this.data('iframe_id')+'_'+_index+'"></form>').appendTo(_div);
        var _input = $('<input type="file" style="width:'+_this.outerWidth()*2+'px;height:'+_this.outerHeight()*2+'px;cursor:pointer;font-size:100px;" title="" name="'+_this.data('input_name')+'" />').appendTo(_form);
        if(_this.data('multiple')) _input.attr('multiple','multiple');
        if (!$(this).is(':hidden')) _div.css({top:_this.offset().top,left:_this.offset().left});
        _div.css({width:_this.outerWidth(),height:_this.outerHeight()});
        _this.add(_div).mousemove(function() { _this.resetPosition();});
        return _input;
    });
    $.fn.resetPosition=(function() {
        if ($(this).is(':hidden')) $('#upload_box_div_'+$(this).data('sort')).css({top:-10000,left:-10000});
        else $('#upload_box_div_'+$(this).data('sort')).css({top:$(this).offset().top,left:$(this).offset().left});
        $('#upload_box_div_'+$(this).data('sort')).css({width:$(this).outerWidth(),height:$(this).outerHeight()});
        $('#upload_box_div_'+$(this).data('sort')).find('input[type=file]').css({width:$(this).outerWidth()*2,height:$(this).outerHeight()*2});
    });
    $.fn.setOtherParams=(function() {
        $('#upload_box_div_'+$(this).data('sort')).find('.upload_other_param').remove();
        if($(this).data('params')) {
            var _params = $(this).data('params').split(',');
            if (_params.length > 0) {
                for (var i=0; i<_params.length; i++) {
                    var _temp_arr = _params[i].split(':');
                    $('<input type="hidden" class="upload_other_param" name="'+_temp_arr[0]+'" value="'+_temp_arr[1]+'" />').appendTo($('#upload_box_div_'+$(this).data('sort')+' form'));
                }
            }
        }
    });
})(jQuery);