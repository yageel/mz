/**
 * Created by Qinmj on 2017/3/3.
 */
$(function () {
    new ntScroll("orderContent");
});

function goEnd(){
    // var isIE = CheckIE();  //判断是否是IE浏览器,方法略
    var obj = document.getElementById("message");
    obj.scrollTop = obj.scrollHeight;
    obj.selectionStart = obj.value.length;
}

function gotoLink(tag) {
    var text = $(tag).find("span.wrap-content").text();
    if (!text)
        text = $(tag).text();

    text = $.trim(text);

    tools.sendData("个人中心页-点击" + text);

    //tools.loading("链接跳转中");

    location.href = $(tag).attr("data-href");
};

function showResult(content, type, address, addressUrl, defaultUrl) {
    var buttons,
        img,
        contentClass = "";

    switch (type) {
        case 1:
            img = "result";
            break;
        case 2:
            img = "successful";
            break;
        case 3:
            img = "nothing";
            buttons = [{ text: "确定", clsName: "button" }];
            break;
        case 9:
            img = "failure";
            contentClass = "color-red";

            buttons = [{ text: "我知道了", clsName: "button" }];
            break;
        default:
            img = "failure";
            contentClass = "color-red";
            buttons = [{ text: "确定", clsName: "button" }];
            break;
    }

    showDialog(img, content, buttons, contentClass);
}

function showDialog(img, content, buttons, contentClass) {
    var html = '<img class="' + img + '" src="' + siteConfig.PUBLIC + '/images/' + img + '-bg.png" />';
    html += '<div class="result-content"><div class="result-custom color-link ' + contentClass + '">' + content + '</div></div>';

    return tools.dialog({
        content: html,
        dialogClass: "dialog-result",
        btns: buttons
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
            switch (res.err_msg){
                case 'get_brand_wcpay_request:cancel':
                    tools.alert("支付取消", "系统提示");
                    break;
                case 'get_brand_wcpay_request:fail':
                    //tools.alert("支付错误", "系统提示");
                    break;
                case 'get_brand_wcpay_request:ok':
                    tools.alert("支付成功", "系统提示");

                    var id = $("#goods_id").val();
                    tools.ajax(tools.url("goods", "exchangeurl"),{
                        id: id
                    }, function (result){
                        window.location.href = result.url;
                    });
                    break;
            }
        }
    );
}

function callpay(data)
{
    if (typeof WeixinJSBridge == "undefined"){
        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
    }else{
        jsApiCall(data);
    }
}

function startMoneyExchange(goodID,message,balance){
    tools.ajax(tools.url("recharge", "goodsorder", {
        msg: message,
        balance:balance,
        id: goodID
    }), function (data) {
        if(data.data.type == 3){
            $("#dialog").css("display","block");
            $("#rechargeContent").css("display","block");
        }else if(data.data.type == 17){
            //if(test == 1){
                window.location.href = data.data.data;
            //}else{
            //    set_pay(data.data.data);
            //}

        }else{
           // tools.alert(data.data.content);
            showResult(data.data.content, data.data.type, data.data.address, data.data.url, data.data.info_url);
        }
    });
}

function startExchange(goodID,message) {

    tools.ajax(tools.url("goods", "setexchange", {
        id: goodID,
        msg: message
    }), function (data) {
        if(data.data.type == 3){
            $("#rechargeContent").css("display","block");
        }else{
            showResult(data.data.content, data.data.type, data.data.address, data.data.url, data.data.info_url);

            if(data.data.type == 2){
                var jumpurl = data.data.info_url;
                setTimeout(function () {
                    window.location.href = jumpurl;
                },1000);
            }
        }

    });
}




//关闭弹窗
$("#cancel").click(function(){
    $("#rechargeContent").css("display","none");
    $("#dialog").css("display","none");
})

function sureExchange(){
    //新增兑换金额不足
    var id = $("#goods_id").val();
    var goodClass = $("#goods_class").val();
    var msg = $("#message").val();

    if($("input[name=menberAddressId]").val()=="" || $("input[name=menberAddressId]").val() == null){
        tools.alert("请设置您的领取地址");
        return false;
    }

    var balance = $("#chooseImg").hasClass('active');

    tools.confirm("是否确认参与本次兑换？", function (idx, type) {
        if (type === "ok") {
            var messageList = tools.session("messageList") || "[]";
            messageList = JSON.parse(messageList);
            if(messageList[0]){
                messageList.pop();
            }
            tools.session("messageList", JSON.stringify(messageList));

            if(goodClass == 1){
                startExchange(id,msg);
            }else{
                startMoneyExchange(id,msg,balance);
            }
        }
    });
}



function saveAddress(){
    var name = $.trim($("#txtName").val()),
        phone = $.trim($("#txtPhone").val()),
        remark = $.trim($("#remark").val()),
        address = $("#lblAddress").text(),
        detailAddress = $.trim($("#txtAddress").val()),
        address_id = $(".radio-default.selected").attr('data-value');
    if(address_id == '0'){
        if (!name) {
            tools.alert("请输入姓名！");
            return;
        }

        if (!phone) {
            tools.alert("请输入手机号！");
            return;
        }

        if (!tools.isMobile(phone)) {
            tools.alert("输入的手机号格式不正确！");
            return;
        }

        if (!address.indexOf("请选择")) {
            tools.alert("请选择省份市区！");
            return;
        }

        if (!detailAddress) {
            tools.alert("请输入详细地址！");
            return;
        }
    }

    tools.ajax(tools.url("goods", "addressedit"), {
        id: $('#goods_id').val(),
        real_name: name,
        phone: phone,
        address: address,
        detail_address: detailAddress,
        address_id: address_id,
        remark:remark
    }, function (ret) {
        window.location.href = ret.data.data;
    });

}


var messageDom = document.getElementById("message");
messageDom.addEventListener("change",function(){
    // console.log($("#message").val());
    var messageList = tools.session("messageList") || "[]";
    messageList = JSON.parse(messageList);
    if(messageList[0]){
        messageList.pop();
    }

    messageList.push($("#message").val());
    tools.session("messageList", JSON.stringify(messageList));
});

//从session取出留言信息
var messageList2 = tools.session("messageList") || "[]";
messageList2 = JSON.parse(messageList2);

$('#message').val(messageList2[0]);