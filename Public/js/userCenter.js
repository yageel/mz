/*
 * author by:王高飞
 * date:2016-06-12 11:04:43
 */

$(function () {
    new ntScroll("userCenterContent");
});

function sug(img){
    var okText, title;

    callback = function (){}

    var i = tools.dialog({
        title: title || "客服二维码",
        content: "<div style='min-height: 200px;'><img src='"+img+"' style='width: 100%' /></div> ",
        dialogClass: "dialog-alert",
        contentStyle: {
            padding: "5px 20px 0 20px",
            color: "#888",
            fontSize: "15px",
            lineHeight: "150%"
        },
        btns: [{
            text: okText || "确定",
            click: function(){
                tools.closeDialog(i);
            },
            clsName: "ok"
        }]
    });
}

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
            $('input[name="order_sn"]').val(result.order_sn);
            callpay(result.data);
        }else if(result.error==1){
            tools.alert(result.msg, "系统提示");
        }
    });
});

$('#btnVercode6').click(function(){
    var spread_id = [];
    if($('input:checked').val() == undefined){
        tools.alert("请选择绑定的设备~", "系统提示~");
    }else{
        alert(22);
    }
    $('input:checked').each(function(){
        spread_id.push($(this).val())
    });

    alert(spread_id);
    return false;
    if(spread_id.length < 1){
        tools.alert("请选择绑定的设备~", "系统提示~");
        return false;
    }
    tools.ajax($('input[name="url"]').val(),{
        spread_id:spread_id
    }, function (result){
        if(result.state == 1){
            tools.alert("绑定成功~",function(){
                window.location.href = $('input[name="url2"]').val();
            });

        }else{
            tools.alert(result.msg);
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
                    var order_sn = $('input[name="order_sn"]').val();
                    if(order_sn == ''){
                        tools.alert("订单ID为空~");
                        return false;
                    }
                    // 同步成功状态~
                    tools.ajax($('input[name="updateurl"]').val(),{
                        order_sn:$('input[name="order_sn"]').val()
                    }, function (result){
                        // 自动更新~
                        window.location.href = $('input[name="starturl"]').val().replace('order_snid', $('input[name="order_sn"]').val());
                    });
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
