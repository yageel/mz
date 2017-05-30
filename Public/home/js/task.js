/*
 * author by:王高飞
 * date:2016-07-13 10:38:59
 */

(function () {
    var tabContents, startX, startY, currentX, winWidth, currentIndex, moveIndex = 0, direction;

    $("#divTabContents").bind("touchstart", function (e) {
        if (!winWidth)
            winWidth = $(this).width();

        currentIndex = $("#ulTabIndex").find(".active").index();

        currentX = -currentIndex * winWidth;

        tabContents = this;
        startX = e.changedTouches[0].pageX;
        startY = e.changedTouches[0].pageY;

        $(tabContents).clearTransition();
    });

    $.bindEvent(window, "touchmove,touchend,touchcancel", function (e) {
        if (tabContents && direction != "tb") {
            var offsetX = e.changedTouches[0].pageX - startX,
                endX;

            switch (e.type) {
                case "touchmove":
                    moveIndex++;

                    if (moveIndex < 4)
                        return;
                    else if (!direction) {
                        var offsetY = e.changedTouches[0].pageY - startY;
                        direction = Math.abs(offsetY) > Math.abs(offsetX) ? "tb" : "lr";

                        if (direction === "tb") {
                            initPar();
                            return;
                        } else {
                            content1Scroll.disable(true);
                            content2Scroll.disable(true);
                        }
                    }

                    endX = offsetX + currentX;
                    break;
                default:
                    if (Math.abs(offsetX) > winWidth * .3) {
                        if (offsetX > 0) {
                            if (currentIndex)
                                endX = currentX + winWidth;
                            else
                                endX = currentX;
                        } else {
                            if (currentIndex < 1)
                                endX = currentX - winWidth;
                            else
                                endX = currentX;
                        }
                    } else {
                        endX = currentX;
                    }

                    $(tabContents).transition("transform");

                    break;
            }

            tabContents.style.transform = tabContents.style.webkitTransform = "translate3d(" + endX + "px,0,0)";

            if (e.type != "touchmove") {
                $("#ulTabIndex").children().eq((-endX) / winWidth).addClass("active").siblings().removeClass("active");
                initPar();
            }
        }

        if (e.type != "touchmove")
            direction = undefined;
    });

    function initPar() {
        startY = tabContents = currentX = startX = currentIndex = undefined;
        content1Scroll.disable(false);
        content2Scroll.disable(false);
        moveIndex = 0;
    }
})();

var content1Scroll = new ntScroll("content1Item"),
    content2Scroll = new ntScroll("content2Item");

$(function () {
    content1Scroll.refresh();
    content2Scroll.refresh();
});

function changeTag(tag, idx) {
    tools.sendData("任务页-点击Tab" + $(tag).text());

    $(tag).addClass("active").siblings().removeClass("active");

    $("#divTabContents").transition("transform").css({
        transform: "translate3d(-" + idx * 100 + "%,0,0)",
        webkitTransform: "translate3d(-" + idx * 100 + "%,0,0)"
    });
}

function revice_mg(tag){
    tools.loading("领取奖励");
    tools.ajax(tools.url('taskmall','revice_mg'), {
        id:""
    }, function (data) {

    });
}
function reviceLcnc(task_id) {
    tools.loading("领取奖励中");
    tools.ajax(tools.url('taskmall', 'revice_lcnc'), {"task_id": task_id}, function (data) {
        console.log(data);
    });
    // tools.closeLoading();
}
function gotoLink(tag, taskType, taskID) {

    tools.sendData("商城任务页-点击" + (taskType === "superTask" ? "超级任务" : "里程碑"), taskID);

    location.href = $(tag).attr("data-href");
}
function goToLcnc(tag, taskType, taskID) {
    if (!tools.isBind()) {
        tools.confirm("该任务需要在本平台登录再参与才有资格领取奖励，确定直接参加该任务吗？", "注册提示", function (idx, type) {
            if (type == "ok") {
                location.href = tools.url("register", "index");
            } else if (type == "cancel") {
                gotoLink(tag, taskType, taskID);
            }
        }, {okText: "现在注册", cancelText: "直接参加"});
    } else {
        tools.loading("链接跳转中");
        tools.sendData("商城任务页-超级任务页-点击马上参与" + (taskType === "superTask" ? "超级任务" : "里程碑"), taskID);
        console.log({"task_id": taskID, "url": $(tag).attr("data-href")});
        location.href = tools.url("taskmall", "go_to_lcnc", {"task_id": taskID});
    }
}
function getMoney(tag, taskType, taskID) {
    if ($(tag).hasClass("button-disable"))
        return;

    tools.sendData("商城任务页-里程碑页-点击领取奖励" + (taskType === "superTask" ? "超级任务" : "里程碑"), taskID);

    tools.ajax(tools.url('taskmall','milestonefinsh'), {
        taskID: taskID
    }, function (data) {
        tools.toast(data.data.content);

        if (data.data.image) {
            var parentDom = $(tag).parent(),
                progress = data.data.signCount / data.data.count * 100;

            parentDom.prev().find("img").attr("src", data.data.image);
            parentDom.find("b").css("width", progress + "%");
            parentDom.find("p").text(data.data.name);
            $(tag).prev().find("i.color-orange").text(data.data.money);

            tag.onclick = function () {
                getMoney(tag, "milestoneTask", data.data.taskID);
            };

            $(tag).text(data.data.buttontxt)[data.data.buttonenble ? "removeClass" : "addClass"]("button-disable");
        } else {
            $(tag).addClass("button-disable").text("已完成");
        }
    });
}

tools.sendData("加载任务页");