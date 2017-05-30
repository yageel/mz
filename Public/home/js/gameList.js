/*
 * author by:王高飞
 * date: 2016-07-21 14:26:55
 */

$(function () {
    new ntScroll("gameListContainer");
});

function gotoLink(tag, title, id) {
    tools.sendData(title, id);

    location.href = $(tag).attr("data-href");
}

tools.sendData("加载游戏中心首页");