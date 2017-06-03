/**
 * Created by Qinmj on 2017/3/1.
 */
$(function () {
    new ntScroll("rechargeContainer");
});

// $(".itemContent").bind("click", function () {
//     $(".itemContent").find('span').removeClass('checked');
//     $(this).find('span').addClass("checked");
//     $(".itemContent").find('.item').removeClass('checked-border');
//     $(this).find('.item').addClass("checked-border");
// });
function check(){
    if($("#chooseImg").hasClass('active')){
        set_order();
    }else{
        tools.alert("请阅读相关法律使用条款并勾选", "系统提示");
    }
}
/**
 * 生成支付订单
 */
function set_order(){
    var id = $(".checked").attr('data-value');
    tools.ajax(tools.url("recharge", "order"),{
        id: id
    }, function (result){
        if(result.error==0){
            //if(test == 1){
                location.href = result.data;
            //}else{
            //    set_pay(result.data);
            //}
        }else if(result.error==1){
            tools.alert(result.message, "系统提示");
        }
    });
}

function set_pay(id){
    tools.ajax(tools.url("recharge", "pay"),{
        id: id
    }, function (result){
        if(result.error==0){
            callpay(result.data);
        }else if(result.error==1){
            tools.alert(result.message, "系统提示");
        }
    });
}

//调用微信JS api 支付
//调用微信JS api 支付
function jsApiCall(data)
{
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        data,
        function(res){
            var wxcallback = $("#wxcallback").val();
            var goodid = $("#goodsid").val();
            if(wxcallback==2){
                var url = "/index.php?s=/goods/order/id/"+goodid+"/type/" + tools.getCityID() + "/gfrom/" + tools.getFromType()+"/wxpaycallback/2";
            }else{
                var url = "/index.php?s=/user/index/type/" + tools.getCityID() + "/gfrom/" + tools.getFromType()+"/wxpaycallback/1";
            }

            /*window.location.href = url;
            return false;*/
            switch (res.err_msg){
                case 'get_brand_wcpay_request:cancel':
                    tools.alert("支付取消", "系统提示");
                    break;
                case 'get_brand_wcpay_request:fail':
                    tools.alert("支付错误", "系统提示");
                    break;
                case 'get_brand_wcpay_request:ok':
                    //tools.alert("支付成功", "系统提示");
                    if(typeof(data.url) != 'undefined' ){
                        window.location.href = data.url;
                    }else{
                        window.location.href = url;
                    }

                    break;
            }
        }
    );
}

function callpay(data)
{
    if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', jsApiCall);
            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
        }
    }else{
        jsApiCall(data);
    }
}

