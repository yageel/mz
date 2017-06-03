/*
 * author by:王高飞
 * date:2016-06-12 11:04:43
 */

$(function () {
    new ntScroll("userCenterContent");
});

function changeTab(tag, idx) {
    $(tag).addClass("active").siblings().removeClass("active");

    $("#tabItems").children().each(function (i) {
        $(this)[idx === i ? "addClass" : "removeClass"]("active");
    });

    $("#btnGoto" + (idx ? "Exchange" : "Cash")).show();
    $("#btnGoto" + (idx ? "Cash" : "Exchange")).hide();
}

function gotoLink(tag) {
    var text = $(tag).find("span.wrap-content").text();
    if (!text)
        text = $(tag).text();

    text = $.trim(text);

    tools.sendData("个人中心页-点击" + text);

    location.href = $(tag).attr("data-href");
}
function gotoLink2(tag,title) {
    tools.sendData(title);
    location.href = $(tag).attr("data-href");
}

tools.sendData("加载个人中心页");

//取消弹窗
$("#cancel").click(function(){
    $("#rechargeContent").css('display','none');
    $("#dialog").css('display','none');
    var url = "/index.php?s=/user/index/type/" + tools.getCityID() + "/gfrom/" + tools.getFromType();
    window.location.href = url;
});

//取消弹窗
$("#sure").click(function(){
    $("#rechargeContent").css('display','none');
    $("#dialog").css('display','none');
    var url = "/index.php?s=/user/index/type/" + tools.getCityID() + "/gfrom/" + tools.getFromType();
    window.location.href = url;
});
