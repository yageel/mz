/*
 * author by:王高飞
 * date:2016-09-01 11:35:10
 */

$(function () {
    new ntScroll("divMainHeha");
});

function submitHeha(tag) {
    var mobile = $.trim($("#txtMobile").val());

    if (!mobile) {
        tools.alert("请输入手机号！");
        return;
    } else if (!tools.isMobile(mobile)) {
        tools.alert("您输入的手机号格式不正确！");
        return;
    }

    tools.sendData("heha助力页-点击提交");
    tools.ajax(tools.url("active_heha", "invite"),{mobile:mobile});
}

tools.sendData("加载heha下载页");
