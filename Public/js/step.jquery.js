(function($) {
    $.fn.step=(function(options) {
        var _this = $(this);
        var _param = {option:3,step:0,name:[]};
        $.extend(_param,options);
        if(typeof(options) != 'object') _param.step = options;
        if (_param.step) {
            _this.find('.bar').width(_this.width()/(_this.parent().find('.step_child').length-1)*(_param.step-1)+10);
            for (var i=0; i<_param.step; i++) {
                _this.parent().find('.step_child').eq(i).addClass('active');
            }
        }else {
            if(_param.name.length == 0) {
                for (var i=0; i<_param.option; i++) {
                    _param.name.push('Step '+(i+1));
                }
                _param.name.push('Finish');
            }else {
                _param.name.push('完成');
            }
            _this.parent().find('.step_box').remove();
            _this.find('.bar').width(0);
            var _box = $('<div class="step_box"></div>').css({width:_this.width(),height:_this.outerHeight()}).appendTo(_this.parent());
            for (var i=0; i<=_param.option; i++) {
                var _left = _this.width()/_param.option*i;
                if (i == 0 || i == _param.option) var _child = $('<div class="step_child"></div>').css({left:_left-10}).appendTo(_box);
                else var _child = $('<div class="step_child"></div>').css({left:_left}).appendTo(_box);
                var _step_name = $('<div class="step_name"></div>').html(_param.name[i]).appendTo(_box);
                _step_name.css({top:_this.outerHeight()+15,left:_child.position().left+_child.outerWidth()/2-_step_name.width()/2})
            }
        }
    });
})(jQuery);