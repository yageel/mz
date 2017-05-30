/*
 * author by:王高飞
 * date: 2016-07-21 15:33:01
 */

$(function () {
    new ntScroll("sortContent");
});

function getAward(tag) {
    if ($(tag).hasClass("button-disable"))
        return;

    tools.sendData("游戏排行页-点击领取奖励");

    tools.ajax(tools.url('game','gameintegral_ex'), function (ret) {
        tools.toast(ret.data.content);
        
        $(tag).addClass("button-disable");
    });
}

tools.sendData("加载游戏排行页");