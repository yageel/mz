/*
 * author by:王高飞
 * date:2016-07-13 15:35:23
 */
tools.storage("is_valid", $("#is_valid").val());

var scroll = new ntScroll("ggkContent"),
    context = $('#canGGK').doms[0].getContext("2d"),
    offset = { left: 0, top: 0 },
    isClearing = false, isDisable = false, 
    myMoney = parseFloat($("#lblMyMBI").text()),
    consumption = parseFloat($("#lblConsumption").text()),
    alertPromptIdx;

$(function () {
    scroll.refresh();
    
    var dom = $('#canGGK').doms[0];

    while (dom.tagName != "BODY") {
        offset.left += dom.offsetLeft;
        offset.top += dom.offsetTop;

        dom = dom.offsetParent;
    }

    $("#divGgkBG").show();

    $(context.canvas).attr("width", $(context.canvas).width()).attr("height", $(context.canvas).height());

    //判断：当自身M币不够的时候就执行disableBg()方法。
    if (consumption > myMoney) {
        disableBg();
    } else {
        drawGgkBg();
        updateTime();
    }
});

/*刮刮卡代码*/
function drawGgkBg() {
    var rem0_12 = $.remToPx(0.12),
        rem0_22 = $.remToPx(0.22),
        rem0_08 = $.remToPx(0.08);

    $("#lblTimePrompt")[isDisable ? "show" : "hide"]();
    $("#divGgkLabel").css("backgroundColor", isDisable ? '#9F3724' : '#E94625');

    context.globalCompositeOperation = "source-over";

    context.clearRect(0, 0, context.canvas.width, context.canvas.height);

    context.beginPath();
    context.arc(0, 0, rem0_12, 0, Math.PI * 2, true);
    context.arc(0, context.canvas.height, rem0_12, 0, Math.PI * 2, true);
    context.closePath();
    context.fillStyle = '#ffffff';
    context.fill();

    context.beginPath();
    for (var i = 0; i < 9; i++) {
        context.arc(context.canvas.width, rem0_12 + i * rem0_22, rem0_08, 0, Math.PI * 2, true);
    }
    context.closePath();
    context.fillStyle = '#ffffff';
    context.fill();

    context.globalCompositeOperation = "destination-over";
    context.fillStyle = isDisable ? '#987E2D' : '#EAC82A';
    context.fillRect(0, 0, context.canvas.width, context.canvas.height);
}
/*点击页面任何地方都隐藏内容*/
$("#dialog").click(function () {
  $('#registerContent').css("display","none");
  $("#dialog").hide();
});

if (!parseInt(tools.storage("is_valid")))
    $("#prize_info").css("display", "none");

function clearCovering(e) {
	//是否登录
	if (!tools.isBind()) {
	    $("#registerContent").css("display","block");
	    $("#dialog").show();
	    return;
	}

	//不允许刮奖
	var pop_txt="特惠活动期间，所有用户只能参与一次刮刮卡活动";
	if(!parseInt(tools.storage("is_valid"))){
		if(!alertPromptIdx){
			alertPromptIdx = tools.alert(pop_txt, function(){
				alertPromptIdx = null;
				var theurl=$("#redirect_url").val();	
				window.location.href=theurl;
			});
		}
		
		return;
	}
	
    if (isDisable)
        return;

    e.stopPropagation();
    e.preventDefault();

    context.globalCompositeOperation = "destination-out";
    context.fillStyle = "rgb(255,123,172)";
    context.beginPath();
    context.arc(e.targetTouches[0].clientX - offset.left, e.targetTouches[0].clientY - offset.top, 10, 0, Math.PI * 2);
    context.fill();
    context.closePath();

    isClearing = true;
}

function openGgk() {
    context.globalCompositeOperation = "destination-out";
    context.fillStyle = "rgb(255,123,172)";
    context.beginPath();
    context.fillRect(0, 0, context.canvas.width, context.canvas.height);
    context.fill();

    tools.sendData("刮刮卡页-挂开覆盖层");

    tools.ajax(tools.url("guaguacard", "store"), function (ret) {
        if (ret.data.state == 1) {
            tools.storage("ggkLastOpenTime", new Date().getTime());
            tools.storage("ggkDuration", ret.data.duration);
            
        	tools.storage("is_valid", ret.data.is_valid);
          

            initGgkBG(ret.data);

            if (ret.data.type == "entity")
                showDialog(ret.data.content, ret.data.address, ret.data.addressUrl);
            else
                tools.toast(ret.data.content);

            if (ret.data.balance < consumption)
                disableBg();
            else
                updateTime();
        } else {
            tools.alert(ret.data.content, "错误信息");
        }
    });
}

function initGgkBG(data) {
    var html = '';

    if (data.nextType == "mbi") {
        html += '<span>';
        html += '    <label class="iconfont">&#xe608;</label>';
        html += '    <b>' + data.nextContent + '</b>M币';
        html += '</span>';
    } else if (data.nextType == "money") {
        html += '<span>';
        html += '    <label>￥</label>';
        html += '    <b>' + data.nextContent + '</b>';
        html += '</span>';
    } else {
        html += '<span>';
        html += '    <b>' + data.nextContent + '</b>';
        html += '</span>';
    }

    $("#divGgkBG").html(html);
}

function showDialog(content, address, addressUrl) {
    var buttons;

    if (address) {
        buttons = [{
            text: "默认地址",
            clsName: "button"
        }, {
            text: "更换地址",
            click: gotoAddress,
            clsName: "button button-empty"
        }];
    } else {
        buttons = [{
            text: "现在就去填写",
            click: gotoAddress,
            clsName: "button"
        }];
    }

    address = "<p class='text-overhide'>" + (address || "您还没有填写地址") + "</p>";

    var html = '<img class="result" src="' + siteConfig.PUBLIC + '/images/result-bg.png?v=1" />';
    html += '<div class="result-content"><div class="result-custom color-link">' + content + address + '</div></div>';

    return tools.dialog({
        content: html,
        dialogClass: "dialog-result",
        btns: buttons
    });

    function gotoAddress() {
        tools.loading("链接跳转中");

        location.href = addressUrl;
    }
}

$.bindEvent(window, "touchend", function () {
    if (isClearing) {
        var w = context.canvas.width, h = context.canvas.height;

        var imgData = context.getImageData(0, 0, w, h),
            pixles = imgData.data,
            tranPixLength = 0;

        for (var i = 0, j = pixles.length; i < j; i += 4) {
            if (pixles[i + 3] < 128)
                tranPixLength++;
        }

        if (tranPixLength / (pixles.length / 4) > 0.3)
            openGgk();

        isClearing = false;
    }
});

function updateTime() {
    var lastOpenTime = tools.storage("ggkLastOpenTime"),
        duration = tools.storage("ggkDuration");

    if (lastOpenTime && duration) {
        lastOpenTime = parseInt(lastOpenTime);
        duration = parseInt(duration) * 1000;

        var now = new Date().getTime(),
            countdown = duration - now + lastOpenTime;

        if (countdown < 0) {
            tools.removeStorage("ggkLastOpenTime");
            tools.removeStorage("ggkDuration");
            return false;
        }

        if (!isDisable) {
            isDisable = true;
            drawGgkBg();
            update();
        }

        return isDisable;
    }

    function update() {
        var now = new Date().getTime(),
            countdown = duration - now + lastOpenTime,
            countdownSeconds = parseInt(countdown / 1000);

        if (countdown % 1000 > 0)
            countdownSeconds++;

        if (countdownSeconds < 10)
            countdownSeconds = "0" + countdownSeconds;

        $("#lblCountdown").text(countdownSeconds);

        if (countdown > 0) {
            $.delay(update, countdown % 1000);
        } else {
            isDisable = false;
            drawGgkBg();
            tools.removeStorage("ggkLastOpenTime");
            tools.removeStorage("ggkDuration");
        }
    }

    return false;
}

function disableBg() {
    isDisable = true;
    drawGgkBg();

    $("#lblTimePrompt").text("您的M币余额不足");
}

tools.sendData("商城刮刮卡页-参与刮卡");
