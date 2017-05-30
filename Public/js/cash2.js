/*
 * author by:王高飞
 * date:2016-06-13 11:40:44
 */

$("#btnCash").bind("click", function () {
    var $me = $(this);

    if ($me.attr("data-contact") == "0") {
        if (tools.getFromType() == 1) {
            tools.ajax(tools.url("active", "get_user_subscribe"),{'t':123},function (ret) {
                tools.closeLoading();
                if(ret.data.userSubscribe != 1){
                    tools.alert("您还没关注我们，请先关注后再申请提现！",function(){
                        BeaconAddContactJsBridge.invoke('jumpAddContact');
                    });
                }else{
                    var money = $.trim($("#txtMoney").val());
                    var thetype=$("#cashtype").val();

                    if (!money) {
                        tools.alert("请输入要提现的金额!！");
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

                    tools.sendData("提现页-点击提现");

                    tools.ajax(tools.url("user", "cashSave"), {
                        money: money,
                        cashtype: thetype
                    });
                }
            });


        }

        if (tools.getFromType() == 2 || tools.getFromType() == 3) {
            tools.alert("您还没关注我们，请先关注后再申请提现！",function(){
                location.href = $me.attr("data-href");
            });
        }
        return;
    }

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

    tools.sendData("提现页-点击提现");

    tools.ajax(tools.url("user", "cashSave"), {
        money: money,
        cashtype: thetype  //add by maofei
    });
});

$(function () {
    new ntScroll("cashContent");
});

tools.sendData("加载提现页");