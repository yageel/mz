/*
 * author by:王高飞
 * date:2016-06-13 11:40:44
 */

var storeScroll = new ntScroll("storeContent");

$(function () {
    storeScroll.refresh();
});

(function () {
    var ul = $("#ulNotice").bind("transitionend,webkitTransitionEnd", function () {
        ul.append(ul.children().eq(0)).clearTransition().css({
            transform: "translate3d(0, 0, 0)",
            webkitTransform: "translate3d(0, 0, 0)"
        });
    });

    var timeoutIdx = setInterval(function () {
        ul.transition("transform", 0.6).css({
            transform: "translate3d(0, -40px, 0)",
            webkitTransform: "translate3d(0, -40px, 0)"
        });
    }, 4000);

    $("#divHideNotice").click(function () {
        clearInterval(timeoutIdx);

        $(this).parent().parent().bind("transitionend,webkitTransitionEnd", function () {
            $(this).remove();
            $("#storeContent").css("paddingBottom", 0);
            storeScroll.refresh();
        }).transition("transform", 0.6).css({
            transform: "translate3d(0, 40px, 0)",
            webkitTransform: "translate3d(0, 40px, 0)"
        });
    });
})();

(function () {
    var middleAD = $("#middleAD"),
        items = middleAD.children(),
        animing,
        timeout,
        startLeft,
        direction;

    middleAD.bind("transitionend,webkitTransitionEnd", function () {
        middleAD.clearTransition();

        setOffset(0);

        items.eq(0).css("zIndex", 1);

        if (animing < 0)
            items.eq(0).appendTo("#middleAD");
        else
            items.eq(items.doms.length - 1).prependTo("#middleAD");

        items = middleAD.children();

        items.eq(0).css({
            left: 0,
            zIndex: 2
        });

        animing = undefined;

        initInterval();
    }).bind("touchstart", function (e) {
        if (!animing) {
            startLeft = e.changedTouches[0].pageX;

            items.eq(0).css("zIndex", 2);

            clearInterval(timeout);

            direction = timeout = undefined;
        }
    });

    $.bindEvent(window, "touchmove,touchend", function (e) {
        if (startLeft) {
            var offset = e.changedTouches[0].pageX - startLeft;

            if (e.type === "touchmove") {
                setOffset(offset);
            } else {
                middleAD.transition("transform");

                if (Math.abs(offset) > screen.width * 0.1)
                    go(offset > 0 ? 1 : -1);
                else
                    setOffset(0);

                startLeft = undefined;
            }
        }
    });

    function go(i) {
        animing = i;

        middleAD.transition("transform");

        setOffset(i * screen.width);
    }

    function setOffset(left) {
        if (left > 0 && direction != "right")
            items.eq(items.doms.length - 1).css("left", "-100%");
        else if (left < 0 && direction != "left")
            items.eq(1).css("left", "100%");

        direction = left < 0 ? "left" : "right";

        var css = {
            transform: "translate3d(" + left + "px,0,0)"
        };
        css.webkitTransform = css.transform;

        middleAD.css(css);
    }

    function initInterval() {
        if (!timeout) {
            timeout = setInterval(function () {
                go(-1);
            }, 4000);
        }
    }

    initInterval();
})();

function gotoLink(tag, title, id) {
    tools.sendData(title, id);

    location.href = $(tag).attr("data-href");
}

tools.sendData("加载美客商城首页");