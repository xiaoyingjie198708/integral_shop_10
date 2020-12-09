 /*----------------------------------------其他操作-------------------------------------------------*/
 //1--修改地址，2--确认收款，3--修改订单状态,4--取消订单，5--修改内部状态，6--添加备注，7--退款,8--优惠金额
$('.update_order').live('click', function () {
    var _order_id = $(this).data('order_id');
    var _type = $(this).data('type');
    var _action = $(this).data('action');
    var _title = '';
    if (_type == 1) {
        _title = '修改收货人地址';
    } else if (_type == 2) {
        _title = '确认收款';
    } else if (_type == 3) {
        _title = '修改订单状态';
        var _status = $(this).data('status');
        if (_status == 8 || _status == 11 || _status == 12) {
            return false;
        }
    } else if (_type == 4) {
        _title = '取消订单';
    }else if(_type == 5){
        _title = '修改内部状态';
    }else if(_type == 6){
        _title = '添加备注';
    }else if(_type == 7){
         if($(this).data('order_status') == 8 || $(this).data('order_status') == 11 || $(this).data('order_status') == 12){
            return false;
        }
        if($(this).data('status') == 0 || $(this).data('status') == 3 || $(this).data('status') == 4){
            return false;
        }
        _title = '退款申请';
    }else if(_type == 8){
       _title = '优惠金额'; 
    }else{
        return false;
    }
    tipwindows(_order_id + ' ' + _title, getOrderBox(_order_id, _action), 820);
});
//弹出对话框
function getOrderBox(_order_id, _action) {
    var _return = '';
    $.ajaxSetup({async: false});
    $.post(_action, {order_id: _order_id}, function (data) {
        _return = data.info;
    }, 'json');
    return _return;
}
/*-----------------------------------------------确认取消-------------------------------------------------------*/
$(".makesure_cancel").live('click',function(){
    if(confirm('确认取消完成吗')){
        $.post($(this).data('action'), {order_id: $(this).data('order_id')}, function (data) {
            if (data.status) {
               location.reload();
            }else{
                alert(data.info);
            }
        });
    }
});
/*-----------------------------------------------下载发票-------------------------------------------------------*/
$(".download_invocie").live('click',function(){
    ajax_loading(false);
    var _order_id = $(this).data('order_id');
    var _action = $(this).data('action');
    $.post(_action, {order_id: _order_id}, function (data) {
        if (data.status) {
           window.location.href = _action + "?order_id=" + _order_id; 
        }else{
            alert(data.info);
        }
    });
});
