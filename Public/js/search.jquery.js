jQuery.fn.extend({
    search: function(options) {
        var _this = $(this);
        var _param = {
            search:'',         //搜索的关键字
            textColor:'red',   //文字高亮颜色      默认 red     色值也可以 比如 #ff0000
            bgColor:'yellow',  //文字背景高亮颜色  默认 yellow  色值也可以 比如 #ff0000
            isEmpty:false,     //空字符是否继续    默认 false
            isShowBg:false,    //是否显示高亮背景  默认 false
            full:true,         //是否全文搜索      默认 true    false：搜索到第一个匹配的就终止
            callback:function(){return;}//回调函数 支持传入参数 为当前jquery对象
        };
        if(typeof(options) != 'object') _param.search = options;
        else $.extend(_param,options);
        if ($.trim(_param.search) == '' && !_param.isEmpty) return false;
        if ($('#search-wrap-css').length == 0) {
            $('<style id="search-wrap-css"></style>').html('.search-wrap-bg{background-color:'+_param.bgColor+';color:'+_param.textColor+';}\n.search-wrap-text{color:'+_param.textColor+';}').appendTo($('head'));
        }
        var _regExp = new RegExp(_param.search,'g');
        var _replaceText = _param.isShowBg ? '<span class="search-wrap-bg">'+_param.search+'</span>' : '<span class="search-wrap-text">'+_param.search+'</span>';
        _this.clearSearch();
        var _html = _this.html();
        _html = _html.replace(/<!--(?:.*)\-->/g,'');
        var _tags = /[^<>]+|<(\/?)([A-Za-z]+)([^<>]*)>/g;
        var _obj = _html.match(_tags);
        $.each(_obj, function(index, content){
            if(!/<(?:.|\s)*?>/.test(content)){
                content = content.replace(_regExp,_replaceText);
                _obj[index] = content;
                if(!_param.full) return false;
            }
        });
        var _html = _obj.join('');
	    _this.html(_html);
        _param.callback(_this);
    },
    clearSearch:function() {
        $('.search-wrap-text,.search-wrap-bg').each(function() {
			var text = document.createTextNode($(this).text());
			$(this).replaceWith($(text));
		});
    }
});