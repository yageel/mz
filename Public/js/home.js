/*
 * author by:王高飞
 * date:2016-06-07 19:58:15
 */
//更新当前项的位置
(function () {
    var dataID = tools.queryPars("item") || tools.session("homeItemID");
    if (dataID !== null) {
        var allIndexDom = $("#mainIndex").children(),
            allBgDom = $("#mainBgList").children();

        $("#mainItemList").children().each(function (i) {
            if ($(this).attr("data-id") == dataID) {
                allIndexDom.eq(0).removeClass("active");
                allIndexDom.eq(i).addClass("active");
                return false;
            }
            allBgDom.eq(i).appendTo("#mainBgList");
            $(this).appendTo("#mainItemList");
        });
    }
})();

/*
 * 绑定window的touch事件实现首页滑块功能。
 */
(function () {
    var startPoint,
        moveIndex,
        direction,
        topItemDom,
        topBgItemDom,
        winWidth,
        isAnim = false,
        totalAngle = 56,
        isMove = false;

    $.bindEvent(window, "touchstart,touchend,touchmove,touchcancel", function (e) {
        if (!isAnim) {
            var moveX = moveY = 0;

            if (startPoint) {
                moveX = e.changedTouches[0].pageX - startPoint.x;
                moveY = e.changedTouches[0].pageY - startPoint.y;
            }

            switch (e.type) {
                case "touchstart":
                    if (!startPoint && !$("#leftMenu").hasClass("show")) {
                        startPoint = {x: e.changedTouches[0].pageX, y: e.changedTouches[0].pageY};
                        moveIndex = 0;
                        topItemDom = $("#mainItemList").children().eq(0);
                        topBgItemDom = $("#mainBgList").children().eq(0);
                        winWidth = document.body.offsetWidth;
                    }
                    break;
                case "touchmove":
                    if (moveIndex++ == 3)
                        direction = Math.abs(moveX) > Math.abs(moveY) ? "lr" : "tb";

                    if (direction === "lr" && startPoint)
                        setItemAngle(moveX);
                    break;
                default:
                    if (direction === "lr" && startPoint) {
                        setItemAngle(moveX, true);

                        tools.session("homeItemID", topItemDom.next(true).attr("data-id") || "");
                    }

                    startPoint = moveIndex = direction = topItemDom = topBgItemDom = winWidth = undefined;
                    break;
            }
        }
        e.preventDefault();
    });

    //绑定每一个Item的Transition以及Animation完成事件。
    $("#mainItemList").children().bind("transitionend,webkitTransitionEnd", function (e) {
        var dom = $(this);
        dom.removeAttr("style");

        $("#mainItemList").removeClass("move");

        if (isMove) {
            dom.removeClass("show").appendTo("#mainItemList");
            dom.parent().children().eq(0).addClass("show");
        }

        isAnim = false;
    }).bind("animationend,webkitAnimationEnd", function (e) {
        //防止在主内容区域的动画捕捉事件捕获到。
        e.stopPropagation();
    });

    //绑定背景图的Transition完成事件。
    $("#mainBgList").children().bind("transitionend,webkitTransitionEnd", function (e) {
        var dom = $(this);
        dom.removeAttr("style");

        if (isMove)
            dom.appendTo("#mainBgList");
    });

    //给主内容区域绑定一个Animation完成事件。
    $("#contentContainer").bind("animationend,webkitAnimationEnd", function () {
        isAnim = false;
    });

    function setItemAngle(moveX, isEnd) {
        var percent = moveX / winWidth,
            angle = percent * totalAngle,
            opacity = .9 - Math.abs(percent) * .9;

        if (isEnd) {
            if (Math.abs(angle) > totalAngle * 0.1) {
                isMove = true;
                opacity = 0;

                angle = angle > 0 ? totalAngle : -totalAngle;

                tools.sendData("首页-滑动页卡");
                if(topItemDom.next(true).attr("data-pv")){
                tools.sendService(topItemDom.next(true).attr("data-pv"));}
            } else {
                angle = 0;
                isMove = false;
                opacity = 1;
            }

            isAnim = true;

            topItemDom.transition("all", .6);
            
            //topItemDom.attr("data-id");
            
            topBgItemDom.transition("all", .6);

            if (isMove) {
                $("#mainItemList").addClass("move");
                $("#mainIndex").find(".active").removeClass("active").next(true).addClass("active");
            }
        } else {
            isMove = false;
        }

        topBgItemDom.css("opacity", opacity);

        topItemDom.css({
            transform: "rotate(" + angle + "deg)",
            webkitTransform: "rotate(" + angle + "deg)",
            opacity: opacity
        });
    }
})();

/*
 * 所有项按钮的点击方法。
 */
function itemClick(tag, id) {
    tag = $(tag);

    if (tag.hasClass("button-disable")){
        //add by allen 
        if(tag.attr("data-href")){ location.href = tag.attr("data-href");return;}
        if(tag.attr("data-card")){ 
            tools.sendService(tag.attr("data-clicked-ajax"));
            try {
                var wxCard = eval("(" + tag.attr("data-card") + ")");

                tools.addCard(wxCard, function () {
                    tools.sendService(tag.attr("data-looked-ajax"));
                });
            } catch (e) {
                tools.alert("系统错误，请重试！");
            }
        }
        
        return;
    }
    var stepIdx = tag.attr("data-index"),
        steps = tag.attr("data-steps"),
        disable = tag.attr("data-disable"),
        texts = tag.attr("data-text");

    tools.sendData("首页-点击" + tag.text(), id);

    if (stepIdx && steps && disable && texts) {
        try {
            steps = eval("(" + steps + ")");
            disable = eval("(" + disable + ")");
            texts = eval("(" + texts + ")");

            if (parseInt(stepIdx) >= steps.length)
                stepIdx = 0;

            window[steps[stepIdx]](tag, texts[stepIdx], disable[stepIdx], stepIdx, id);
        } catch (e) {
            tools.alert("数据出错，请刷新重试！");
        }
    } else {
        gotoLink(tag);
    }
}

function addWxCard(tag, text, disable, stepIdx, id) {
    tools.sendService(tag.attr("data-clicked-ajax"));

    try {
        var wxCard = eval("(" + tag.attr("data-card") + ")");

        tools.addCard(wxCard, function () {
            tools.sendService(tag.attr("data-looked-ajax"));

            setBtnState(tag, text, disable, stepIdx, id);
        });
    } catch (e) {
        tools.alert("系统错误，请重试！");
    }
}

/*
 * 链接跳转函数。
 */
function gotoLink(tag, text, disable, stepIdx, id) {
    //tools.loading("链接跳转中");
    location.href = tag.attr("data-href");

    if (text !== undefined && disable !== undefined && stepIdx !== undefined && id !== undefined)
        setBtnState(tag, text, disable, stepIdx, id);
}

/*
 * 跳转到留资页面。
 */
function getInfo(tag, text, disable, stepIdx, id) {
    tools.session("getInfoText", text);
    tools.session("getInfoDisable", disable);
    tools.session("getInfoStepIdx", stepIdx);
    tools.session("getInfoID", id);

    tools.loading("链接跳转中");
    location.href = tag.attr("data-href");
}

/*
 * 跳转到投票页
 */
function gotoVote(tag, text, disable, stepIdx, id) {
    tools.session("voteText", text);
    tools.session("voteDisable", disable);
    tools.session("voteStepIdx", stepIdx);
    tools.session("voteID", id);

    tools.loading("链接跳转中");
    location.href = tag.attr("data-href");
}

/*
 * 打开红包。
 */
function showRedBox(tag, text, disable, stepIdx, id) {
	
	var redImage = tag.attr("data-image");
	var redContent = tag.attr("data-content");
	
	
    var dialogID = tools.dialog({
        content: '<div class="sign-today-box"><img src="/Public/images/hongbao-bg.png" /><p class="img-description">'+redContent+'</p><a href="javascript:void(0)" class="btnApartRed" id="btnApartRed[meIdx]"><img class="small-icon" src="'+redImage+'"/></a><a class="iconfont" href="javascript:void(0)" id="btnCloseRedBox[meIdx]">&#xe606;</a></div>',
        dialogClass: "sign-today-dialog"
    });

    //点击关闭红包按钮
    $("#btnCloseRedBox" + dialogID).bind("click", function () {
        tools.closeDialog(dialogID);
    });

    //点击拆红包的按钮
    $("#btnApartRed" + dialogID).bind("click", function () {
        var redBoxData = eval("(" + tag.attr("data-redbox") + ")");
        
        tools.sendData("首页-点击拆红包", id);
        
        tools.ajax(tools.url("index", "getRedbox"), redBoxData, function (data) {
            tools.closeDialog(dialogID);

            if (data.state == 1) {
                setBtnState(tag, text, disable, stepIdx, id);
                
                if (data.data) {
                    dialogID = tools.dialog({
                        content: '<div class="winning-box"><img src="' + siteConfig.PUBLIC + '/images/winning-bg.png?v=2" /><a href="javascript:void(0)" id="btnCloseWinning[meIdx]"></a><em><span class="color-focus">' + data.data.money + '</span><label class="color-link">' + data.data.unit + '</label></em><p class="color-link">已存入<a href="' + tools.url("user", "index", { tab: data.data.unit == "元" ? "" : "mbi" }) + '" onclick="tools.loading(\'链接跳转中\')" class="color-focus">我的' + (data.data.unit == "元" ? "余额" : "M币") + '</a>，' + (data.data.unit == "元" ? "累计一元以上可以提现" : "可直接在M币商城使用") + '</p></div>',
                        dialogClass: "winning-dialog"
                    });

                    //点击关闭未中奖按钮
                    $("#btnCloseWinning" + dialogID).click(function () {
                        tools.closeDialog(dialogID);
                    });
                } else {
                    dialogID = tools.dialog({
                        content: '<div class="not-winning-box"><img src="' + siteConfig.PUBLIC + '/images/notWinning-bg.png?v=1" /><a href="javascript:void(0)" id="btnCloseNotWinning[meIdx]"></a></div>',
                        dialogClass: "not-winning-dialog"
                    });

                    //点击关闭未中奖按钮
                    $("#btnCloseNotWinning" + dialogID).click(function () {
                        tools.closeDialog(dialogID);
                    });
                }
            }
        });
    });
}

/*
 * 设置按钮状态。
 */
function setBtnState(tag, text, disable, stepIdx, id) {
    id = id === undefined ? "btnSignToday" : "otherItemBtn" + id;
            
    stepIdx++;

    if (disable)
        tag.addClass("button-disable");

    tag.text(text);

    tools.session(id, escape(text) + "&" + disable + "&" + stepIdx);

    tag.attr("data-index", stepIdx);
}

/*
 * 各种杂项处理。
 */
$(function () {
    //更新已点击的项按钮状态
    tools.eachSession(function (key) {
        var items = this.toString().split("&");
        if (items.length === 3) {
            $("#" + key).text(unescape(items[0])).attr("data-index", items[2]);
    
            if (items[1] === "true")
                $("#" + key).addClass("button-disable");
        }
    });

    //首页几个卡项内容给定滚动属性。
    $("#mainItemList").children().each(function () {
        new ntScroll($(this).children().eq(0).children().doms[0]);
    });

    //设置引导页
    tools.guideInit("HOME-GUIDE", [{
        img: "home-guide-1.png?v=1",
        imgSize: "80% auto",
        location: "center 62%",
        buttons: [{}]
    }, {
        img: "home-guide-2.png?v=1",
        imgSize: "80% auto",
        location: "center 82%",
        buttons: [{}]
    }, {
        img: "home-guide-3.png?v=1",
        imgSize: "80% auto",
        location: "1px -5px",
        buttons: [{}]
    }]);

    //预加载两个常用图片，preLoad只能预加载图片，不能预加载其他东西。
    tools.preLoad([siteConfig.PUBLIC + "/images/redbox-bg.png"]);
    tools.preLoad([siteConfig.PUBLIC + "/images/notWinning-bg.png"]);
});
tools.sendData("加载V4首页");
var dataID = tools.queryPars("item") || tools.session("homeItemID");
if(dataID){
    tools.sendService("/mlmkpv.php?id="+dataID+"&first=1&type="+tools.queryPars("type"));
}else{
    tools.sendService($("#firstcard").attr("data-pv"));
}