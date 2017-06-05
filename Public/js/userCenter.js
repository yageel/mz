/*
 * author by:王高飞
 * date:2016-06-12 11:04:43
 */

$(function () {
    new ntScroll("userCenterContent");
});

function auto(){
    setTimeout(function(){
        tools.ajax(tools.url('register','wh',{loading:false}),{
            r: Date.parse(new Date())
        }, function (result){
            auto();
        });
    }, 30000);
}
auto();

function gotoLink(tag) {
    var text = $(tag).find("span.wrap-content").text();
    if (!text)
        text = $(tag).text();

    text = $.trim(text);

    tools.sendData("个人中心页-点击" + text);

    location.href = $(tag).attr("data-href");
}

function gotoLink2(tag,title) {
    tools.sendData(title);
    location.href = $(tag).attr("data-href");
}

tools.sendData("加载个人中心页");

$('#btnVercode').click(function(){
    var id = $('input[name="package_id"]').val();
    var spread_id = $('input[name="spread_id"]:checked').val();
    if(id == ''){
        tools.alert("请选择购买套餐~", "系统提示~");
        return false;
    }
    tools.ajax($('input[name="url"]').val(),{
        package_id: id,
        spread_id:spread_id
    }, function (result){
        if(result.error==0){
            callpay(result.data);
        }else if(result.error==1){
            tools.alert(result.msg, "系统提示");
        }
    });
});

//调用微信JS api 支付
//调用微信JS api 支付
function jsApiCall(data)
{
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        data,
        function(res){
            switch (res.err_msg){
                case 'get_brand_wcpay_request:cancel':
                    tools.alert("支付取消", "系统提示");
                    break;
                case 'get_brand_wcpay_request:fail':
                    tools.alert("支付错误", "系统提示");
                    break;
                case 'get_brand_wcpay_request:ok':
                    window.location.href = '';
                    // tools.alert("支付成功", "系统提示");
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
