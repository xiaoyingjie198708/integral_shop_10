(function($) {
    $.fn.export=(function(options) {
        var _this = $(this);
        var _param = {p:1};
        $.extend(_param,options);
        _this.live('click',function() {
            ajax_loading(false);
            if(_this.data('send') == 'off') return false;
            var _url = _this.data('url');
            if(!_url) { alert('url empty!'); return false;}
            _this.data('send','off');
            $.createBox();
            _param.p = 1;
            $.get(_url,_param,function (data) {
                _this.data('send','on');
                if (data == 'export') {
                    $.removeBox();
                    ajax_loading(true);
                    location.href = _url+'?'+$.param(_param);
                }else {
                    if (isNaN(Number(data))) { alert('500：后台代码错误'); $.removeBox(); return false;}
                    $.createLog(_param.p,data);
                    $.createStatus('OK');
                    _param.p++;
                    $.getExportData(_url,_param,data);
                }
            });
        });
    });
    $.getExportData=(function(_url,_params,page_count) {
        $.createLog(_params.p,page_count);
        $.get(_url,_params,function(data) {
            if (data == 'export') {
                $('.export_close').show();
                $('.export_close').bind('click',function() { $.removeBox();});
                ajax_loading(true);
                location.href = _url+'?'+$.param(_params);
            }else {
                if (isNaN(Number(data))) { alert('500：后台代码错误'); $.removeBox(); return false;}
                $.createStatus('OK');
                _params.p++;
                $.getExportData(_url,_params,data);
            }
        });
    });
    $.createBox=(function() {
        $('<div></div>').css({'background-color': '#fff',height:'100%',left:0,'opacity':0.00001,'position':'fixed',top:0,width:'100%','z-index': 998}).addClass('mask').appendTo($('body'));
        var _box = $('<div class="export_box"></div>').css({width:350, height:200,'position':'fixed',top:'50%', left:'50%','margin':'-100px 0 0 -175px','z-index':999}).appendTo($('body'));
        $('<div class="export_bg"></div>').css({'position':'absolute','background-color':'#000','opacity':0.3, width:350, height:200,'border-radius':'5px'}).appendTo(_box);
        $('<div></div>').css({'position':'absolute',width:310, height:160, 'background-color':'#fff',padding:10, 'margin':'10px 0 0 10px'}).html('<div class="export_title">数据导出中……请勿关闭浏览器<span class="page_count"></span><a style="display:none;float:right;margin-top:-5px;font-size:14px;" href="javascript:;" class="export_close">╳</a></div><div class="export_content" style="overflow:auto;height:146px;"></div>').appendTo(_box);
    });
    $.setPageCount=(function(page_count) { $('.page_count').html('（共'+page_count+'页）');});
    $.createLog=(function(_page,page_count) {
        if (_page > page_count) {
            $('<div></div>').html('数据生成完毕，正在打包……').appendTo($('.export_content'));
        }else {
            $.setPageCount(page_count);
            $('<div></div>').html('正在导出第'+_page+'页……<span style="color:green;"></span>').appendTo($('.export_content'));
        }
        $.resizeheight();
    });
    $.resizeheight=(function() { $('.export_content').scrollTop(10000);});
    $.createStatus=(function(_status) { $('.export_content div:last').find('span').html(_status);});
    $.removeBox=(function() { $('.mask').remove(); $('.export_box').remove();});
})(jQuery);