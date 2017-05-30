/**
 * Created by assassin on 2017/3/9.
 */
// /*
//  * author by:王高飞
//  * date:2016-07-11 15:52:18
//  */
$(function () {
    new ntScroll("content1Item");
});
function showResult(content, type, address, addressUrl, defaultUrl) {
    var buttons,
        img,
        contentClass = "";

    switch (type) {
        case 1:
            img = "result";
            break;
        case 2:
            img = "successful";
            break;
        case 3:
            img = "nothing";
            buttons = [{ text: "确定", clsName: "button" }];
            break;
        case -1:
            img = "failure";

            buttons = [{
                text: "继续抽奖",
                click: function () {
                    tools.sendData("详情页-点击继续抽奖", tools.queryPars("id"));

                    handleClick(true);
                },
                clsName: "button"
            }, {
                text: "我知道了",
                clsName: "button button-empty"
            }];

            contentClass = "color-red";
            break;
        case 9:
            img = "failure";
            contentClass = "color-red";

            buttons = [{ text: "我知道了", clsName: "button" }];
            break;
        default:
            img = "failure";
            contentClass = "color-red";

            buttons = [{ text: "确定", clsName: "button" }];
            break;
    }

    if (type == 1 || type == 2) {
        if (address) {
            buttons = [{
                text: "默认地址",
                clsName: "button",
                click: function () {
                    tools.loading("链接跳转中");

                    location.href = defaultUrl;
                }
            }, {
                text: "更换地址",
                click: gotoAddress,
                clsName: "button button-empty"
            }];
        } else {
            buttons = [{
                text: "现在就去填写",
                click: gotoAddress,
                clsName: "button"
            }];
        }

        address = "<p class='text-overhide'>" + (address || "您还没有填写地址") + "</p>";
    }

    address = address || "";

    showDialog(img, content, address, buttons, contentClass);

    function gotoAddress() {
        tools.loading("链接跳转中");

        location.href = addressUrl;
    }
}

function showDialog(img, content, address, buttons, contentClass) {
    var html = '<img class="' + img + '" src="' + siteConfig.PUBLIC + '/images/' + img + '-bg.png" />';
    html += '<div class="result-content"><div class="result-custom color-link ' + contentClass + '">' + content + address + '</div></div>';

    return tools.dialog({
        content: html,
        dialogClass: "dialog-result",
        btns: buttons
    });
}


/*兑换*/
function registerDialog(e){
    $("#registerContent").css("display","block");
    $("#dialog").show();
    $("#is_exchange_flag").val(1);
}
/*点击页面任何地方都隐藏内容*/
$("#dialog").click(function () {
    $('#registerContent').css("display","none");
    $("#dialog").hide();
})
/*抽奖*/
function registerLottery(e){
    $("#registerContent").css("display","block");
    $("#dialog").show();
    $("#is_exchange_flag").val(2);
}



function handleClick(isStart) {
    var goodID = tools.queryPars("id");

    if (!isStart) {
        tools.sendData("详情页-点击立即" + (isLottery ? "抽奖" : "兑换"), goodID);

        tools.confirm("是否确认参与本次" + (isLottery ? "抽奖？" : "兑换？"), function (idx, type) {
            if (type === "ok") {
                tools.sendData("详情页-确认" + (isLottery ? "抽奖" : "兑换"), goodID);

                startHandle();
            }
        });
    } else {
        startHandle();
    }

    function startHandle() {
        tools.ajax(tools.url("goods", isLottery ? "setlottery" : "setexchange", {
            id: goodID,
            sign: $("#hidSign").val()
        }), function (data) {
            showResult(data.data.content, data.data.type, data.data.address, data.data.url, data.data.info_url);
        });
    }
}

// tools.sendData("加载" + (isLottery ? "抽奖" : "兑换") + "商品详情页", tools.queryPars("id"));
function gotoLink(tag) {
    var text = $(tag).find("span.wrap-content").text();
    if (!text)
        text = $(tag).text();

    text = $.trim(text);

    tools.sendData("个人中心页-点击" + text);

    //tools.loading("链接跳转中");

    location.href = $(tag).attr("data-href");
}