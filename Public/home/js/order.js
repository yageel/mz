/**
 * Created by assassin on 2017/3/2.
 */

$(function () {
    new ntScroll("orderContent");
});

function gotoLink(tag) {
    var text = $(tag).find("span.wrap-content").text();
    if (!text)
        text = $(tag).text();

    text = $.trim(text);

    tools.sendData("个人中心页-点击" + text);

    //tools.loading("链接跳转中");

    location.href = $(tag).attr("data-href");
}

// $("#message").addEventListener("change",function(){
//     console.log($("#message").val());
// })

