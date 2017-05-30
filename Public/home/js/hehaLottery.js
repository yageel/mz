/*
 * author by:王高飞
 * date:2016-09-01 13:39:25
 */

$(function () {
    new ntScroll("divMainHeha");
});

var lotteryItems = $("#divLotteryItems").children();

function startLottery(i,msg,url) {
    i = i || 5;

    var currentIdx = $("#divLotteryItems").find(".active").index() || 0,
        lotteryCount = 0,
        itemIdx = currentIdx,
        time = 50;

    $("#divLotteryPoints").addClass("animing");

    function _temp() {
        currentIdx++;

        var lastItemDom;

        if (currentIdx > 7) {
            currentIdx = 0;

            lastItemDom = lotteryItems.eq(7);
        } else {
            lastItemDom = lotteryItems.eq(currentIdx - 1);
        }

        lastItemDom.removeClass("active");

        lotteryItems.eq(currentIdx).addClass("active");

        lotteryCount++;

        if (time < 300) {
            setTimeout(_temp, time);

            if (lotteryCount > 22)
                time += 10;
        } else {
            if (itemIdx == i) {
                tools.alert(msg,function () {
                    location.href = url;
                });
                $("#divLotteryPoints").removeClass("animing");
            } else {
                time += 10;
                setTimeout(_temp, time);
                itemIdx++;
                if (itemIdx > 7) {
                    itemIdx = 0;
                }
            }
        }
    }

    setTimeout(_temp, time);
}

function lottery() {
    tools.sendData("heha抽奖页-点击抽奖");
    tools.ajax(tools.url("active_heha", "dolottery"), function (ret) {
        startLottery(ret.data.index,ret.msg,ret.url);
    },{
        errorCallback: function (errorMsg) {
            tools.alert(errorMsg.msg);
            $("#divLotteryPoints").removeClass("animing");
        }
    });
}

function confirmSubmit(tag) {
    var mobile = $.trim($("#txtMobile").val());
    if (!mobile) {
        tools.alert("请输入手机号！");
        return;
    } else if (!tools.isMobile(mobile)) {
        tools.alert("您输入的手机号格式不正确！");
        return;
    }

    tools.sendData("heha抽奖页-点击确认提交");

    tools.ajax(tools.url("active_heha", "bind_mobile"),{mobile:mobile});
}

tools.sendData("加载heha抽奖页");