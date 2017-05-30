/**
 * Created by Qinmj on 2017/3/1.
 */

//选择金额
function initial(){
    $("#default").addClass("checkbox-green");
    $("#l1").attr("dataid",1);
    $("#l2").attr("dataid","");
    if($("#default").attr("checked")){
        $("#chooseMoney").attr("checked",false);
        $("#chooseMoney").removeClass("checkbox-green");
    }
}

function chooseMoney(){
    $("#l1").attr("dataid","");
    $("#l2").attr("dataid",2);
    $("#default").attr("checked",false);
    $("#default").removeClass("checkbox-green");
    $("#chooseMoney").addClass("checkbox-green");
    $("#chooseMoney").attr("checked","checked");
}

//赴约
function appointment(tag){
    var label1 = document.getElementById("l1"),
        label1Val = label1.innerText,
        money = "",
        currentVal = parseInt($("#current").text()),
        countVal = parseInt($("#count").text());

    //打赏金额(取money变量)
    if(!($("#l1").attr("dataid") || $("#l2").attr("dataid"))){
        tools.alert("请输入打赏金额~!");
        return;
    }

    if($("#l1").attr("dataid")){
        // money = label1Val;//固定金额8元
        money=8;
    }else if($("#l2").attr("dataid")){
        money = parseFloat($("#money").val());
    }
    if(!tools.isNumber(money) || money<=0){
        tools.alert("输入打赏金额的格式不正确!");
        return;
    }

    /**
     * 生成支付订单
     */
    var id=$("#activeId").val();

    tools.ajax(tools.url("mvkt", "order"),{
        id: id,
        money:money
    }, function (result){
        if(result.state==200){
            // tools.alert(result.data);
            $("#ordersn").val(result.msg);//订单流水号
            callpay(result.data);
        }else if(result.state==400){
            tools.alert(result.msg, "系统提示");
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
            // var wxcallback = $("#wxcallback").val();
            var order_sn = $("#ordersn").val();
            var url = "/index.php?s=/mvkt/index/type/" + tools.getCityID() + "/from/" + tools.getFromType();

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
                    tools.ajax(tools.url("mvkt", "order_success"),{
                        order_sn: order_sn,
                    }, function (ret){
                        // tools.alert("恭喜您成功参与活动~");
                        tools.alert("恭喜您成功参与活动~",function(){
                            window.location.href = url;
                        });
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

