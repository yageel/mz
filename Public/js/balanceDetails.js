/*
 * author by:王高飞
 * date:2016-06-12 11:04:43
 */

$(function () {
    var pageIndex = 1,
        pageCount = $("#balanceDetailsItems").attr("data-page"),
        pageSize = 20,
        isMontyDetails = $("#balanceDetailsItems").attr("data-type") === "money";

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    new ntScroll("balanceDetailsContent");

    window.detailsScroll = new ntScroll("balanceDetailsItems", {
        pullRefresh: true,
        pullMore: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;

            tools.ajax($('#balanceDetailsItems').attr('data-url'), {
                p: pageIndex,
                pageSize: pageSize
            }, function (data) {
                detailsScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);
                pageCount = data.data.total_pages;

                var balanceDetailsItems = $("#balanceDetailsItems"),
                    fragment = document.createDocumentFragment();

                if (info.type === "refresh")
                    balanceDetailsItems.find("a").remove();
                fragment.textContent(data.html )
                // fragment.innerHTML = (data.html);

                balanceDetailsItems.find(".pull-down").before(fragment);

                detailsScroll.refresh();
                detailsScroll.haveMore(pageIndex < pageCount);

                if (info.type === "refresh") {
                    $.delay(function () {
                        detailsScroll.setPullRefreshState(false);
                    }, 800);
                } else {
                    detailsScroll.setPullMoreState(false, false);
                }
            }, {
                loading: false,
                errorCallback: function () {
                    pageCount = 0;

                    detailsScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](false);

                    $.delay(function () {
                        detailsScroll[info.type === "more" ? "setPullMoreState" : "setPullRefreshState"](false);
                        detailsScroll.haveMore(pageIndex < pageCount);
                    }, 800);
                },
                type: "get"
            });
        }
    });

    if (pageCount < 2)
        detailsScroll.haveMore(false);

    tools.sendData("加载" + (isMontyDetails ? "余额" : "M币") + "详情页");
});

function gotoLink(tag) {
    var text = $(tag).find("span.wrap-content").text();
    if (!text)
        text = $(tag).text();

    text = $.trim(text);

    // tools.sendData("个人中心页-M币详情页-点击" + text);
    tools.sendData("个人中心页-余额详情页-点击" + text);

    location.href = $(tag).attr("data-href");
}
function gotoLink2(tag,title) {
    tools.sendData(title);
    location.href = $(tag).attr("data-href");
}

// tools.sendData("加载个人中心页");