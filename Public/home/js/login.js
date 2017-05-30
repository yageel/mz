/*
 * author by:王高飞
 * date:2016-06-12 12:48:39
 */

$("#btnLogin").bind("click", function () {
    var mobile = $.trim($("#txtMobile").val()),
        pwd = $("#txtPwd").val();

    if (!mobile) {
        tools.alert("请输入手机号！");
        return;
    }

    if (!tools.isMobile(mobile)) {
        tools.alert("输入的手机号码格式不正确！");
        return;
    }

    if (!pwd) {
        tools.alert("请输入登录密码！");
        return;
    }

    tools.ajax(tools.url("login", "post"), {
        mobile: mobile,
        pwd: pwd
    });
});

$(function () {
    new ntScroll("loginContent");
});

tools.sendData("加载登录页");