/*
 * author by:王高飞
 * date:2016-06-13 11:40:44
 */

$("#btnCash").bind("click", function () {
    var $me = $(this);

    var money = $.trim($("#txtMoney").val());
    var thetype=$("#cashtype").val();//add by maofei

    if (!money) {
        tools.alert("请输入要提现的金额！");
        return;
    }

    if (!tools.isNumber(money)) {
        tools.alert("提现金额格式不正确！");
        return;
    }

    money = parseFloat(money);

    if (money < 1) {
        tools.alert("提现金额不能小于1块钱！");
        return;
    }

    total_amount = $('input[name="total_amount"]').val();
    if(money > total_amount){
        tools.alert("提现金额不能大于可提现总额！");
        return;
    }

    tools.sendData("提现页-点击提现");

    tools.ajax(tools.url("user", "cashSave"), {
        money: money
    });
});

$(function () {
    new ntScroll("cashContent");
});

tools.sendData("加载提现页");