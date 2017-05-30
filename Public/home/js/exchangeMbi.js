$(function () {
    new ntScroll("exchangeMbiContent");
});

function exchange(city_name) {
    tools.sendData("兑换M币页-点击一键兑换");
    var total_integral = $('#total_integral').val();
    var total_mb_all = $('#total_mb_all').val();
    var city = $('#city').val();
    var openid = $('#openid').val();
    var sign = $('#sign').val();

    tools.confirm("本次活动同一用户（同一手机、同一设备号、同一微信）仅可参与一次，您当前使用的“"+city_name+"”旧版积分"+total_integral+"全部兑换为"+total_mb_all+"M币，确认兑换？", function (idx, type) {
        if (type === "ok") {
            tools.sendData("兑换M币页-确定一键兑换");

            tools.ajax(tools.url("market", "dh",{city:city, openid:openid,sign:sign}));
        }
    });
}

function toRegister(tag) {
    tools.sendData("兑换M币页-点击一键兑换");
    tools.confirm("您还没有注册系统，请先注册？", function (idx, type) {
        location.href = $(tag).attr("data-href");
    });
}

function gotoLink(tag) {
    //tools.loading("链接跳转中");

    tools.sendData("兑换M币页-点击马上体验");

    location.href = $(tag).attr("data-href");
}