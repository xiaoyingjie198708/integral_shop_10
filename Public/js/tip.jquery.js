(function($) {
    $.fn.tip=(function(options) {
        var _this = $(this);
        var _param = {message:'',position:'bottom 20px',color:'#999',bgColor:'#fff',bdColor:'#ccc',Event:'click',fontSize:'12px',hideTime:0,top:0,left:0,callback:''};
        $.extend(_param,options);
        if(typeof(options) != 'object') _param.message = options;
        if(!_param.message) return false;
        _this.bind(_param.Event,function(event) {
            $.tiphide();
            var _box = $('<div class="tip_box dropdown-menu show"></div>').css({'color':_param.color,'background':_param.bgColor,border:'1px solid '+_param.bdColor,'position':'absolute','padding':'10px','font-size':_param.fontSize,'text-align':'left','min-width':100}).html('<div id="tip_message">'+($.isFunction(_param.message) ? _param.message(_this) : _param.message)+'</div>').appendTo($('body'));
            var _point = $('<div>◆</div>').css({width:16,height:16,'position':'absolute','color':_param.bdColor,'font-size':'14px','line-height':'14px','font-family':'Arial,"SimSun",sans-serif'}).appendTo(_box);
            var _point_shade = _point.clone().css('color',_param.bgColor).appendTo(_box);
            var _position = _param.position.split(' ');
            _position[1] = _position[1] ? _position[1] : 'center';
            var _top,_left;
            switch (_position[0]) {
                case 'bottom':
                    _top = -8;
                    _left = (_position[1]=='center') ? (_box.outerWidth()-16)/2 : _position[1];
                    _point.css({top:_top,left:_left}); _point_shade.css({top:_top+1,left:_left});
                    _box.css({top:_this.offset().top+_this.outerHeight()+8+_param.top,left:_this.offset().left+_param.left});
                    break;
                case 'top':
                    _top = _box.outerHeight()-8;
                    _left = (_position[1]=='center') ? (_box.outerWidth()-16)/2 : _position[1];
                    _point.css({top:_top,left:_left}); _point_shade.css({top:_top-1,left:_left});
                    _box.css({top:_this.offset().top-_box.outerHeight()-8+_param.top,left:_this.offset().left+_param.left});
                    break;
                case 'left':
                    _top = (_position[1]=='center') ? (_box.outerHeight()-16)/2 : _position[1];
                    _left = _box.outerWidth()-8;
                    _point.css({top:_top,left:_left}); _point_shade.css({top:_top,left:_left-1});
                    _box.css({top:_this.offset().top-_box.outerHeight()/2+_this.outerHeight()/2,left:_this.offset().left-_box.outerWidth()-8});
                    break;
                case 'right':
                    _top = (_position[1]=='center') ? (_box.outerHeight()-16)/2 : _position[1];
                    _left = -8;
                    _point.css({top:_top,left:_left}); _point_shade.css({top:_top,left:_left+1});
                    _box.css({top:_this.offset().top-_box.outerHeight()/2+_this.outerHeight()/2,left:_this.offset().left+_this.outerWidth()+8});
                    break;
                default:
                    _top = -8;
                    _left = (_position[1]=='center') ? (_box.outerWidth()-16)/2 : _position[1];
                    _point.css({top:_top,left:_left}); _point_shade.css({top:_top+1,left:_left});
                    _box.css({top:_this.offset().top+_this.outerHeight()+8+_param.top,left:_this.offset().left+_param.left});
                    break;
            }
            $(document).bind('click',function() { $.tiphide();});
            _box.bind('click',function(event) {event.stopPropagation();});
            if(_param.callback) _param.callback(_this);
            event.stopPropagation();
        });
    });
    $.fn.untip=(function() {
        var _box = $(this).data('obj');
        if (_box) {
            _box.remove();
            $(this).data('obj','');
        }
    });
    $.tiphide=(function() {
        $('.tip_box').hide(0,function(){$(this).remove();});
    });
})(jQuery);