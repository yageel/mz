/*
 * author by:张靖
 * date:2016-06-12 13:24:32
 */

var updateTimeoutIdx,
    localStorageSendMsgKey = "REGISTER-SEND-MSG-TIME";

$("#btnRegister").bind("click", function () {
    var mobile = checkMobile(),
        vercode = $.trim($("#txtVercode").val());
    
    var is_exchange_flag=$.trim($("#is_exchange_flag").val());

    if (!mobile)
        return;

    if (!vercode) {
        tools.alert("请输入手机验证码！");
        return;
    }

    tools.sendData("注册页-点击更改绑定手机");
    
    //hdv4注册
    tools.ajax(tools.url("register", "ajax_register"), {
        mobile: mobile,
        vercode: vercode
    },function (ret) {
    	 tools.alert(ret.msg);
    	 var ebutt=document.getElementById("btnHandle");
    	 if(is_exchange_flag ==1){
    		 $("#btnHandle").text("立即兑换");
    		 ebutt.setAttribute("onclick","handleClick()");
    	 }else{
    		 $("#btnHandle").text("立即抽奖");
    		 isLottery=true;
    		 ebutt.setAttribute("onclick","handleClick()");
    	 }
    	 
    	 $("#registerContent").css("display","none");
    	 $("#dialog").hide();
         return;
    });
    
});

$("#btnVercode").bind("click", function () {
    var $tag = $(this),
        mobile = checkMobile();

    if (!mobile || $tag.hasClass("gray"))
        return;
    
    tools.sendData("注册页-点击获取验证码");

    tools.ajax(tools.url("register", "vercode_api"), {
        mobile: mobile
    }, function () {
        tools.storage(localStorageSendMsgKey, (new Date()).getTime());

        updateVercodeBtn();
    });
});

function updateVercodeBtn() {
    clearTimeout(updateTimeoutIdx);

    var $btn = $("#btnVercode").addClass("gray");

    _update();

    updateTimeoutIdx = $.delay(_update, 1000);

    function _update() {
        var now = (new Date()).getTime(),
            sendTime = tools.storage(localStorageSendMsgKey);

        if (!sendTime) {
            $btn.removeClass("gray");
            return;
        }

        var countdown = 60000 - now + parseInt(sendTime),
            countdownSeconds;

        countdownSeconds = parseInt(countdown / 1000);

        if (countdown % 1000 > 0)
            countdownSeconds++;

        $btn.text("剩余" + countdownSeconds + "秒");

        if (countdown > 0){
            updateTimeoutIdx = $.delay(_update, countdown % 1000);
        } else {
            clearTimeout(updateTimeoutIdx);
            tools.removeStorage(localStorageSendMsgKey)
            $btn.text("重新获取").removeClass("gray");
        }
    }
}

function checkMobile() {
    var mobile = $.trim($("#txtMobile").val());

    if (!mobile) {
        tools.alert("请输入手机号！");
        return false;
    }

    if (!tools.isMobile(mobile)) {
        tools.alert("输入的手机号码格式不正确！");
        return false;
    }

    return mobile;
}

$(function () {
    new ntScroll("registerContent");
});

updateVercodeBtn();

tools.sendData("加载注册页");