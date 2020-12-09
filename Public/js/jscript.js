(function ($) {
    var g = !!$.Tween;
    if (g) {
        $.Tween.propHooks['backgroundPosition'] = {get: function (a) {
                return parseBackgroundPosition($(a.elem).css(a.prop))
            }, set: setBackgroundPosition}
    } else {
        $.fx.step['backgroundPosition'] = setBackgroundPosition
    }
    ;
    function parseBackgroundPosition(c) {
        var d = (c || '').split(/ /);
        var e = {center: '50%', left: '0%', right: '100%', top: '0%', bottom: '100%'};
        var f = function (a) {
            var b = (e[d[a]] || d[a] || '50%').match(/^([+-]=)?([+-]?\d+(\.\d*)?)(.*)$/);
            d[a] = [b[1], parseFloat(b[2]), b[4] || 'px']
        };
        if (d.length == 1 && $.inArray(d[0], ['top', 'bottom']) > -1) {
            d[1] = d[0];
            d[0] = '50%'
        }
        f(0);
        f(1);
        return d
    }
    function setBackgroundPosition(a) {
        if (!a.set) {
            initBackgroundPosition(a)
        }
        $(a.elem).css('background-position', ((a.pos * (a.end[0][1] - a.start[0][1]) + a.start[0][1]) + a.end[0][2]) + ' ' + ((a.pos * (a.end[1][1] - a.start[1][1]) + a.start[1][1]) + a.end[1][2]))
    }
    function initBackgroundPosition(a) {
        a.start = parseBackgroundPosition($(a.elem).css('backgroundPosition'));
        a.end = parseBackgroundPosition(a.end);
        for (var i = 0; i < a.end.length; i++) {
            if (a.end[i][0]) {
                a.end[i][1] = a.start[i][1] + (a.end[i][0] == '-=' ? -1 : +1) * a.end[i][1]
            }
        }
        a.set = true
    }}
)(jQuery);
jQuery.easing["jswing"] = jQuery.easing["swing"];
jQuery.extend(jQuery.easing, {def: "easeOutQuad", swing: function (e, t, n, r, i) {
        return jQuery.easing[jQuery.easing.def](e, t, n, r, i)
    }, easeInQuad: function (e, t, n, r, i) {
        return r * (t /= i) * t + n
    }, easeOutQuad: function (e, t, n, r, i) {
        return-r * (t /= i) * (t - 2) + n
    }, easeInOutQuad: function (e, t, n, r, i) {
        if ((t /= i / 2) < 1)
            return r / 2 * t * t + n;
        return-r / 2 * (--t * (t - 2) - 1) + n
    }, easeInCubic: function (e, t, n, r, i) {
        return r * (t /= i) * t * t + n
    }, easeOutCubic: function (e, t, n, r, i) {
        return r * ((t = t / i - 1) * t * t + 1) + n
    }, easeInOutCubic: function (e, t, n, r, i) {
        if ((t /= i / 2) < 1)
            return r / 2 * t * t * t + n;
        return r / 2 * ((t -= 2) * t * t + 2) + n
    }, easeInQuart: function (e, t, n, r, i) {
        return r * (t /= i) * t * t * t + n
    }, easeOutQuart: function (e, t, n, r, i) {
        return-r * ((t = t / i - 1) * t * t * t - 1) + n
    }, easeInOutQuart: function (e, t, n, r, i) {
        if ((t /= i / 2) < 1)
            return r / 2 * t * t * t * t + n;
        return-r / 2 * ((t -= 2) * t * t * t - 2) + n
    }, easeInQuint: function (e, t, n, r, i) {
        return r * (t /= i) * t * t * t * t + n
    }, easeOutQuint: function (e, t, n, r, i) {
        return r * ((t = t / i - 1) * t * t * t * t + 1) + n
    }, easeInOutQuint: function (e, t, n, r, i) {
        if ((t /= i / 2) < 1)
            return r / 2 * t * t * t * t * t + n;
        return r / 2 * ((t -= 2) * t * t * t * t + 2) + n
    }, easeInSine: function (e, t, n, r, i) {
        return-r * Math.cos(t / i * (Math.PI / 2)) + r + n
    }, easeOutSine: function (e, t, n, r, i) {
        return r * Math.sin(t / i * (Math.PI / 2)) + n
    }, easeInOutSine: function (e, t, n, r, i) {
        return-r / 2 * (Math.cos(Math.PI * t / i) - 1) + n
    }, easeInExpo: function (e, t, n, r, i) {
        return t == 0 ? n : r * Math.pow(2, 10 * (t / i - 1)) + n
    }, easeOutExpo: function (e, t, n, r, i) {
        return t == i ? n + r : r * (-Math.pow(2, -10 * t / i) + 1) + n
    }, easeInOutExpo: function (e, t, n, r, i) {
        if (t == 0)
            return n;
        if (t == i)
            return n + r;
        if ((t /= i / 2) < 1)
            return r / 2 * Math.pow(2, 10 * (t - 1)) + n;
        return r / 2 * (-Math.pow(2, -10 * --t) + 2) + n
    }, easeInCirc: function (e, t, n, r, i) {
        return-r * (Math.sqrt(1 - (t /= i) * t) - 1) + n
    }, easeOutCirc: function (e, t, n, r, i) {
        return r * Math.sqrt(1 - (t = t / i - 1) * t) + n
    }, easeInOutCirc: function (e, t, n, r, i) {
        if ((t /= i / 2) < 1)
            return-r / 2 * (Math.sqrt(1 - t * t) - 1) + n;
        return r / 2 * (Math.sqrt(1 - (t -= 2) * t) + 1) + n
    }, easeInElastic: function (e, t, n, r, i) {
        var s = 1.70158;
        var o = 0;
        var u = r;
        if (t == 0)
            return n;
        if ((t /= i) == 1)
            return n + r;
        if (!o)
            o = i * .3;
        if (u < Math.abs(r)) {
            u = r;
            var s = o / 4
        } else
            var s = o / (2 * Math.PI) * Math.asin(r / u);
        return-(u * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * i - s) * 2 * Math.PI / o)) + n
    }, easeOutElastic: function (e, t, n, r, i) {
        var s = 1.70158;
        var o = 0;
        var u = r;
        if (t == 0)
            return n;
        if ((t /= i) == 1)
            return n + r;
        if (!o)
            o = i * .3;
        if (u < Math.abs(r)) {
            u = r;
            var s = o / 4
        } else
            var s = o / (2 * Math.PI) * Math.asin(r / u);
        return u * Math.pow(2, -10 * t) * Math.sin((t * i - s) * 2 * Math.PI / o) + r + n
    }, easeInOutElastic: function (e, t, n, r, i) {
        var s = 1.70158;
        var o = 0;
        var u = r;
        if (t == 0)
            return n;
        if ((t /= i / 2) == 2)
            return n + r;
        if (!o)
            o = i * .3 * 1.5;
        if (u < Math.abs(r)) {
            u = r;
            var s = o / 4
        } else
            var s = o / (2 * Math.PI) * Math.asin(r / u);
        if (t < 1)
            return-.5 * u * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * i - s) * 2 * Math.PI / o) + n;
        return u * Math.pow(2, -10 * (t -= 1)) * Math.sin((t * i - s) * 2 * Math.PI / o) * .5 + r + n
    }, easeInBack: function (e, t, n, r, i, s) {
        if (s == undefined)
            s = 1.70158;
        return r * (t /= i) * t * ((s + 1) * t - s) + n
    }, easeOutBack: function (e, t, n, r, i, s) {
        if (s == undefined)
            s = 1.70158;
        return r * ((t = t / i - 1) * t * ((s + 1) * t + s) + 1) + n
    }, easeInOutBack: function (e, t, n, r, i, s) {
        if (s == undefined)
            s = 1.70158;
        if ((t /= i / 2) < 1)
            return r / 2 * t * t * (((s *= 1.525) + 1) * t - s) + n;
        return r / 2 * ((t -= 2) * t * (((s *= 1.525) + 1) * t + s) + 2) + n
    }, easeInBounce: function (e, t, n, r, i) {
        return r - jQuery.easing.easeOutBounce(e, i - t, 0, r, i) + n
    }, easeOutBounce: function (e, t, n, r, i) {
        if ((t /= i) < 1 / 2.75) {
            return r * 7.5625 * t * t + n
        } else if (t < 2 / 2.75) {
            return r * (7.5625 * (t -= 1.5 / 2.75) * t + .75) + n
        } else if (t < 2.5 / 2.75) {
            return r * (7.5625 * (t -= 2.25 / 2.75) * t + .9375) + n
        } else {
            return r * (7.5625 * (t -= 2.625 / 2.75) * t + .984375) + n
        }
    }, easeInOutBounce: function (e, t, n, r, i) {
        if (t < i / 2)
            return jQuery.easing.easeInBounce(e, t * 2, 0, r, i) * .5 + n;
        return jQuery.easing.easeOutBounce(e, t * 2 - i, 0, r, i) * .5 + r * .5 + n
    }});
(function ($) {
    $(function () {
        var zm = window.zm || {};
        var validate = zm.validate || {};
        var rules = $.extend({equal: /^.*$/, required: /^.+$/, numeric: /^[0-9]+$/, integer: /^\-?[0-9]+$/, decimal: /^\-?[0-9]*\.?[0-9]+$/, date: /^\-?[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/, phone: /^[01]?[-.]?(\([2-9]\d{2}\)|[2-9]\d{2})[-.]?\d{3}[-.]?\d{4}$/, idcard: /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/, email: /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/, alpha: /^[a-z]+$/i, alphaNumeric: /^[a-z0-9]+$/i, ip: /^((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})$/i, base64: /[^a-zA-Z0-9\/\+=]/i, minlength6: /^.{6,}$/, url: /^((http|https):\/\/(\w+:{0,1}\w*@)?(\S+)|)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/}, validate.rules);
        var messages = $.extend({equal: 'The %s and %0s is inconsistent.', required: 'The %s is required.', email: 'The %s must contain a valid email address.', phone: 'The %s must contain a valid phone number.', alpha: 'The %s must only contain alphabetical characters.', alphaNumeric: 'The %s must only contain alpha-numeric characters.', numeric: 'The %s must contain only numbers.', integer: 'The %s must contain an integer.', decimal: 'The %s must contain a decimal number.', ip: 'The %s must contain a valid IP.', base64: 'The %s must contain a base64 string.', url: 'The %s must contain a valid URL.', minlength6: 'The %s length greater than 6', date: 'The %s must contain a valid Date.'}, validate.messages);
        $.fn.validateForm = function (warningFn) {
            var success = true;
            var tag = $(this).data('tag') || 'div';
            $(this).find('input,textarea').each(function () {
                var el = $(this);
                el.keyup(function () {
                    el.next(tag).remove()
                });
                var validate = $(this).data('validate');
                if (validate) {
                    var val = $(this).val();
                    var arrValidate = validate.split('|');
                    var msg = el.data('message');
                    var isValidated = true;
                    $.each(arrValidate, function (i, v) {
                        if (!rules[v].test(val)) {
                            var errorMsg = msg ? msg : messages[v].replace('%s', el.prop('placeholder'));
                            if (warningFn) {
                                warningFn(el, errorMsg)
                            } else {
                                el.next(tag).remove().end().after('<' + tag + ' class="field-error">' + errorMsg + '</' + tag + '>')
                            }
                            success = false;
                            isValidated = false;
                            return false
                        } else if (v == 'equal' && el.data('for') && val != $('#' + el.data('for')).val()) {
                            var errorMsg = msg ? msg : messages[v].replace('%s', el.prop('placeholder')).replace('%0s', $('#' + el.data('for')).prop('placeholder'));
                            if (warningFn) {
                                warningFn(el, errorMsg)
                            } else {
                                el.next(tag).remove().end().after('<' + tag + ' class="field-error">' + errorMsg + '</' + tag + '>')
                            }
                            success = false;
                            isValidated = false;
                            return false
                        }
                    });
                    if (isValidated) {
                        el.next('.field-error').remove()
                    }
                }
                return success;
            });
            return success
        };
        $('.frm-submit').submit(function () {
            var success = $(this).validateForm();
            if (success && $(this).attr('callback')) {
                try {
                    var fn = $(this).attr('callback');
                    $.post($(this).attr('action'), $(this).serialize(), function (r) {
                        eval(fn + '(' + r + ')')
                    })
                } catch (e) {
                }
                return false
            } else {
                return success
            }
        })
    })
})(jQuery);
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory)
    } else {
        factory(jQuery)
    }
    jQuery.cookie.json = true
})(function (jQuery) {
    var pluses = /\+/g;
    function encode(s) {
        return config.raw ? s : encodeURIComponent(s)
    }
    function decode(s) {
        return config.raw ? s : decodeURIComponent(s)
    }
    function stringifyCookieValue(value) {
        return encode(config.json ? JSON.stringify(value) : String(value))
    }
    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\')
        }
        try {
            s = decodeURIComponent(s.replace(pluses, ' '));
            return config.json ? JSON.parse(s) : s
        } catch (e) {
        }
    }
    function read(s, converter) {
        var value = config.raw ? s : parseCookieValue(s);
        return jQuery.isFunction(converter) ? converter(value) : value
    }
    var config = jQuery.cookie = function (key, value, options) {
        if (value !== undefined && !jQuery.isFunction(value)) {
            options = jQuery.extend({}, config.defaults, options);
            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setTime(+t + days * 864e+5)
            }
            return(document.cookie = [encode(key), '=', stringifyCookieValue(value), options.expires ? '; expires=' + options.expires.toUTCString() : '', options.path ? '; path=' + options.path : '', options.domain ? '; domain=' + options.domain : '', options.secure ? '; secure' : ''].join(''))
        }
        var result = key ? undefined : {};
        var cookies = document.cookie ? document.cookie.split('; ') : [];
        for (var i = 0, l = cookies.length; i < l; i++) {
            var parts = cookies[i].split('=');
            var name = decode(parts.shift());
            var cookie = parts.join('=');
            if (key && key === name) {
                result = read(cookie, value);
                break
            }
            if (!key && (cookie = read(cookie)) !== undefined) {
                result[name] = cookie
            }
        }
        return result
    };
    config.defaults = {json: true};
    jQuery.removeCookie = function (key, options) {
        if (jQuery.cookie(key) === undefined) {
            return false
        }
        jQuery.cookie(key, '', jQuery.extend({}, options, {expires: -1}));
        return!jQuery.cookie(key)
    }
});
function jugeWideScreen() {
    if (jQuery(window).width() > 1366) {
        jQuery('body').addClass('zm-widescreen');
        jQuery('.w-big-kv,.w-small-kv').find('img').each(function () {
            jQuery(this).attr('src', $(this).attr('src').replace('/small/', '/big/'))
        })
    } else {
        jQuery('body').removeClass('zm-widescreen');
        jQuery('.w-big-kv,.w-small-kv').find('img').each(function () {
            jQuery(this).attr('src', $(this).attr('src').replace('/big/', '/small/'))
        })
    }
}
jQuery(window).on('resize', jugeWideScreen);
jQuery(document).ready(function () {
    jugeWideScreen()
});
jQuery(function () {
    jQuery(".numeric").keydown(function (event) {
        if (jQuery.inArray(event.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 || (event.keyCode == 65 && event.ctrlKey === true) || (event.keyCode >= 35 && event.keyCode <= 39)) {
            return
        } else {
            if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105)) {
                event.preventDefault()
            }
        }
    })
});
function maskClose() {
    jQuery('#maskClose').remove();
    jQuery(jQuery.maskid).hide();
    jQuery('#mask').remove();
    jQuery.maskid = null
}
jQuery('form').submit(function () {
    return false
});
function mask(id) {
    jQuery.maskid = id = '#' + id;
    if (jQuery('#mask').length == 0) {
        jQuery('body').append('<div id="mask" style="display:none"></div><a id="maskClose" style="display:block;color:#FFF; line-height:23px; text-align:center; font-size:30px;z-index:99999;position:absolute; text-decoration:none;" href="javascript:maskClose()"></a>')
    }
    jQuery('#mask').show().css({'position': 'absolute', 'top': 0, 'z-index': 99990, 'width': jQuery(window).width(), 'height': jQuery(document).height(), 'background-color': '#000', 'opacity': 0.8});
    var left = jQuery(window).scrollLeft() + jQuery(window).width() / 2 - jQuery(jQuery.maskid).outerWidth() / 2, top = jQuery(window).scrollTop() + jQuery(window).height() / 2 - jQuery(jQuery.maskid).outerHeight() / 2;
    top = Math.max(10, top);
    jQuery('#maskClose').show().css({'left': left + jQuery(jQuery.maskid).outerWidth() - jQuery('#maskClose').outerWidth() - 15, 'top': top + jQuery('#maskClose').outerHeight()});
    jQuery(id).show().css({'box-shadow': '0px 0px 20px #999', '-moz-box-shadow': '0px 0px 20px #999', '-webkit-box-shadow': '0px 0px 20px #999', 'border-radius': '3px 3px 3px 3px', '-moz-border-radius': '3px', '-webkit-border-radius': '3px', top: top, left: left, 'z-index': 99998, 'position': 'absolute'})
}
jQuery(window).resize(function () {
    if (jQuery.maskid) {
        var left = jQuery(window).scrollLeft() + jQuery(window).width() / 2 - jQuery(jQuery.maskid).outerWidth() / 2, top = jQuery(window).scrollTop() + jQuery(window).height() / 2 - jQuery(jQuery.maskid).outerHeight() / 2;
        top = Math.max(10, top);
        jQuery('#mask').show().css({'width': jQuery(window).width(), 'height': jQuery(document).height()});
        jQuery('#maskClose').show().css({'left': left + jQuery(jQuery.maskid).outerWidth() - jQuery('#maskClose').outerWidth() - 15, 'top': top + jQuery('#maskClose').outerHeight()});
        jQuery(jQuery.maskid).show().css({top: top, left: left})
    }
});
var zm = zm || {};
zm.langs = {"pagebar": {"first": "首页", "pre": "上一页", "next": "下一页", "last": "末页", "itemPerPage": "条/页", "info": "显示{1}-{2}条 共{0}条"}, "data": {"nodata": "无数据...", "loading": "正在加载中...", "errorLoading": "与后台联系错误"}};
(function ($) {
    $.fn.mask = function (msg) {
        var self = $(this);
        var _pMaskEl = self.parent();
        _pMaskEl.css({position: 'relative'});
        $('.__ys_mask', _pMaskEl).remove();
        var maskEl = $('<div></div>');
        maskEl.addClass('__ys_mask');
        maskEl.css({position: 'absolute', zIndex: (parseInt(_pMaskEl.css("zIndex"), 100) + 1) + '', height: _pMaskEl.outerHeight() + 'px', width: _pMaskEl.outerWidth() + 'px', top: '0px', left: '0px', opacity: '0.6', background: "#E1E1E1", cursor: 'wait'});
        var msgAttr = {width: 100, height: 20};
        maskEl.html('<div style="text-align:center;height:' + msgAttr.height + 'px;width:' + msgAttr.width + 'px;margin-left:' + (_pMaskEl.outerWidth() - msgAttr.width) / 2 + 'px;margin-top:' + (_pMaskEl.outerHeight() - msgAttr.height) / 2 + 'px;border:solid 1px #999;padding:5px;color:black;background-color:#AAA;">' + msg || zm.langs.data.loading + '<div>');
        _pMaskEl.append(maskEl)
    };
    $.fn.unmask = function () {
        var self = $(this);
        var _pMaskEl = self.parent();
        $('.__ys_mask', _pMaskEl).remove()
    };
    $.fn.serializeObject = function (upBlank) {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (!upBlank && this.value == '')
                return true;
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]]
                }
                o[this.name].push(this.value)
            } else {
                o[this.name] = this.value
            }
        });
        return o
    };
    $.ys = $.ys || {};
    String.format = function () {
        var s = arguments[0];
        for (var i = 0; i < arguments.length - 1; i++) {
            var reg = new RegExp("\\{" + i + "\\}", "gm");
            s = s.replace(reg, arguments[i + 1])
        }
        return s
    };
    $.fn.ys_grid = function (config) {
        var self = this;
        self.store = {data: {}};
        config = $.extend({pageOptions: [10, 15, 20, 30, 50], title: false, curPage: 1, totalPage: 1, sm: false, stripeRows: true}, config);
        config.params = $.extend({start: 0, limit: 20}, config.params);
        config.perPage = config.params.limit;
        self.view = {topbar: $('<div class="ys-grid-topbar"></div>'), pagebar: $('<div class="ys-grid-pagebar"></div>'), grid: {thead: $('<thead></thead>'), tbody: $('<tbody></tbody>')}, noData: '<tr><td colspan="{0}" style="color:red;text-align:center;">' + zm.langs.data.nodata + '</td></tr>', loadingData: '<tr><td colspan="{0}">' + zm.langs.data.loading + '</td></tr>', page: {first: '<a class="js-page-first" href="javascript:void(0)">' + zm.langs.pagebar.first + '</a>', prev: '<a class="js-page-pre" href="javascript:void(0)">' + zm.langs.pagebar.pre + '</a>', next: '<a class="js-page-next" href="javascript:void(0)">' + zm.langs.pagebar.next + '</a>', last: '<a class="js-page-last" href="javascript:void(0)">' + zm.langs.pagebar.last + '</a>', perPage: '<span class ="ys-page-per-wrapper"><select name="page" class="ys-page-per">{0}</select>' + zm.langs.pagebar.itemPerPage + '<span>', pageInfo: '<span class="ys-page-info">' + zm.langs.pagebar.info + '</span>', number: '<a href="javascript:void(0)" class="js-page-number">{0}</a>', curNumber: '<strong>{0}</strong>'}};
        self.view.table = $('<table class="ys-grid"></table>').append(self.view.grid.thead).append(self.view.grid.tbody);
        $(self).html('').append(self.view.topbar).append(self.view.table).append(self.view.pagebar);
        self.view.grid.tbody.html(String.format(self.view.loadingData, config.columns.length + (config.sm ? 1 : 0)));
        self.view.table.css({'min-height': '100px', 'width': config.width ? (config.width + 'px') : '100%'});
        $('select', self.view.pagebar).val(config.perPage).change(function () {
            config.curPage = 1;
            config.perPage = $(this).val();
            config.params.limit = config.perPage;
            updatePage()
        });
        function initTopBar() {
            if (config.tbar) {
                $.each(config.tbar, function (i, bar) {
                    var _bar = '<' + (bar.tag || 'button') + ' href="#" class="ys-grid-topbar-button ' + (bar.cls ? bar.cls : '') + '" style="' + (bar.style ? bar.style : '') + '">' + (bar.text || '') + '</' + (bar.tag || 'button') + '>';
                    $(_bar).appendTo(self.view.topbar).click(bar.handler)
                })
            }
        }
        ;
        var hasFilter = false;
        function updateHeader() {
            var strHeader = '<tr class="ys-grid-header">';
            var strFilter = '';
            if (config.sm) {
                strHeader += '<th width="20" class="ys-grid-column-sm"><input type="checkbox" name="ys-item"></input></th>';
                strFilter += '<td></td>'
            }
            $.each(config.columns, function (i, el) {
                var w = el.width ? el.width : 100;
                strHeader += '<th width="' + w + '">' + el.header + '</th>';
                if (el.filter) {
                    strFilter += '<td style="padding:1px;" class="ys-grid-column-filter">' + (function () {
                        var html = '';
                        var name = el.filter.name || el.dataIndex;
                        var id = el.filter.id || 'filter_' + name + '_' + i;
                        if (el.filter.type == 'combo') {
                            html = '<select name="' + name + '" id="' + id + '">';
                            if (el.filter.options) {
                                $.each(el.filter.options, function (j, o) {
                                    html += '<option value="' + o[0] + '">' + o[1] + '</option>'
                                })
                            }
                            html += '</select>'
                        } else {
                            html = '<input type="text" name="' + name + '" id="' + id + '">'
                        }
                        return html
                    })() + '</td>';
                    hasFilter = true
                } else {
                    strFilter += '<td></td>'
                }
            });
            strHeader += '</tr>';
            if (hasFilter) {
                strHeader += '<tr>' + strFilter + '</tr>'
            }
            self.view.grid.thead.html(strHeader);
            if (config.sm) {
                $('input[type=checkbox]', self.view.grid.thead).click(function () {
                    if (this.checked) {
                        $('input[type=checkbox]', self.view.grid.tbody).each(function () {
                            this.checked = true
                        }).closest('tr').addClass('row_selected')
                    } else {
                        $('input[type=checkbox]', self.view.grid.tbody).each(function () {
                            this.checked = false
                        }).closest('tr').removeClass('row_selected')
                    }
                });
                self.getCheckedRowRecords = function () {
                    var rec = [];
                    $('input[type=checkbox]:checked', self.view.grid.tbody).each(function (i, cbRow) {
                        rec.push(self.store.data.items[cbRow.value])
                    });
                    return rec
                }
            }
            if (hasFilter) {
                $('td.ys-grid-column-filter', self.view.grid.thead).keydown(function (e) {
                    if (e.keyCode == 13) {
                        resetConfigParam();
                        self.store.load()
                    } else if (e.keyCode == 27) {
                        $('td > input', self.view.grid.thead).val('');
                        resetConfigParam();
                        self.store.load()
                    }
                }).change(function () {
                    resetConfigParam();
                    self.store.load()
                })
            }
        }
        ;
        function updateBody() {
            var strBody = '';
            if (self.store.data.totalCount > 0) {
                $.each(self.store.data.items, function (i, el) {
                    strBody += '<tr id="___' + i + '">';
                    if (config.sm) {
                        strBody += '<td class="ys-grid-column-sm"><input type="checkbox" name="ys-item' + i + '" value="' + i + '"></input></td>'
                    }
                    $.each(config.columns, function (index, obj) {
                        var val = obj.dataIndex != '' ? eval('(el.' + obj.dataIndex + ')') : '';
                        strBody += '<td class="' + (obj.type == 'action' ? 'grid_column_action ' : '') + (obj.cls || '') + '" style="' + (obj.style || '') + '">' + (obj.renderer ? obj.renderer(val, el) : val) + '</td>'
                    });
                    strBody += '</tr>'
                })
            } else {
                strBody += String.format(self.view.noData, config.columns.length + (config.sm ? 1 : 0))
            }
            self.view.grid.tbody.html(strBody);
            if (self.store.data.totalCount > 0) {
                $.each(self.store.data.items, function (i, el) {
                    $.each(config.columns, function (index, obj) {
                        if (obj.afterrender) {
                            var val = obj.dataIndex != '' ? eval('(el.' + obj.dataIndex + ')') : '';
                            var tdEl = $('tr:eq(' + index + ')', trEl);
                            var trEl = $('tr:eq(' + i + ')', self.view.grid.tbody);
                            obj.afterrender(val, el, tdEl, trEl, self.view.table)
                        }
                    })
                })
            }
            $('.grid_column_action', self.view.grid.tbody).click(function (e) {
                var t = e.target;
                if (t.tagName === 'A' || t.tagName === 'BUTTON') {
                    var rId = $(this).closest('tr')[0].id.substr(3);
                    var r = self.store.data.items[rId];
                    var action = t.title || t.href.substring(t.href.indexOf('#') + 1);
                    self[action] && self[action](r)
                }
            });
            if (config.stripeRows) {
                $('tr:even', self.view.grid.tbody).addClass('row_odd')
            }
            $('tr', self.view.grid.tbody).hover(function () {
                $(this).addClass('row_hover')
            }, function () {
                $(this).removeClass('row_hover')
            });
            if (config.sm) {
                $('input[type=checkbox]', self.view.grid.tbody).click(function () {
                    var allChecked = ($('input[type=checkbox]:checked', self.view.grid.tbody).length == $('input[type=checkbox]', self.view.grid.tbody).length);
                    if (this.checked) {
                        $(this).closest('tr').addClass('row_selected')
                    } else {
                        $(this).closest('tr').removeClass('row_selected')
                    }
                    if (allChecked) {
                        $('input[type=checkbox]', self.view.grid.thead)[0].checked = true
                    } else {
                        $('input[type=checkbox]', self.view.grid.thead)[0].checked = false
                    }
                })
            }
        }
        ;
        function updatePagebar() {
            if (config.noPageBar)
                return;
            var cp = config.curPage;
            var pp = config.perPage;
            var tp = config.totalPage;
            var strPage = '';
            strPage += String.format(self.view.page.pageInfo, self.store.data.totalCount, config.params.start + 1, config.params.start + self.store.data.items.length);
            strPage += self.view.page.first;
            if (cp > 1) {
                strPage += self.view.page.prev
            }
            var start = ((cp - 2) < 1 || tp <= 5) ? 1 : (cp - 2);
            var end = (start + 4) < tp ? (start + 5) : tp;
            var strNumber = '';
            for (var i = start; i <= end; i++) {
                if (i == cp) {
                    strNumber += String.format(self.view.page.curNumber, i);
                    continue
                }
                strNumber += String.format(self.view.page.number, i)
            }
            strPage += strNumber;
            if (cp < tp) {
                strPage += self.view.page.next
            }
            strPage += self.view.page.last;
            self.view.pagebar.html(strPage);
            $('select', self.view.pagebar).val(config.perPage)
        }
        ;
        self.view.pagebar.click(function (e) {
            switch (e.target.className) {
                case'js-page-first':
                    config.curPage = 1;
                    break;
                case'js-page-pre':
                    config.curPage -= 1;
                    break;
                case'js-page-next':
                    config.curPage = config.curPage - 0 + 1;
                    break;
                case'js-page-last':
                    config.curPage = (self.store.data.totalCount == 0 ? 1 : Math.ceil(self.store.data.totalCount / config.perPage));
                    break;
                case'js-page-number':
                    config.curPage = e.target.textContent || e.target.outerText - 0;
                    break;
                default:
                    return false
            }
            updatePage();
            e.preventDefault();
            e.stopPropagation()
        });
        function updatePage() {
            config.params.start = (config.curPage - 1) * config.perPage;
            self.store.load()
        }
        ;
        function init() {
            initTopBar();
            updateHeader()
        }
        ;
        self.store.load = function (cfg) {
            self.view.table.mask(zm.langs.data.loading);
            cfg = cfg || {};
            self.url = cfg.url || self.url || config.url;
            var filterParams = {};
            if (hasFilter) {
                $('td > input,td > select', self.view.grid.thead).each(function (i, el) {
                    if (el.value != '') {
                        filterParams[el.name] = el.value
                    }
                })
            }
            var params = $.extend({}, config.params, cfg.params, self.baseParams, filterParams);
            $.ajax({type: "POST", url: self.url, data: params, dataType: 'json', success: function (data) {
                    self.store.data = data;
                    config.totalPage = data.totalCount > 0 ? Math.ceil(data.totalCount / config.perPage) : 1;
                    updateBody();
                    updatePagebar();
                    self.view.table.unmask()
                }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(zm.langs.data.errorLoading);
                    self.view.table.unmask()
                }})
        };
        function resetConfigParam() {
            config.curPage = 1;
            config.params.start = 0
        }
        function initPageBarPerNumber() {
            var strOpt = '';
            $.each(config.pageOptions, function (i, opt) {
                strOpt += '<option value="' + opt + '">' + opt + '</option>'
            });
            return strOpt
        }
        ;
        self.config = config;
        init();
        return this
    }
})(jQuery);
if (typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function () {
        return this.replace(/^\s+|\s+$/g, '')
    }
}