/*
 * author by:王高飞
 * date:2016-06-12 11:04:43
 */

$(function () {
    var pageIndex = 0,
        pageCount = 1,
        pageSize = 20;

    new ntScroll("winningDetailsContent");

    window.detailsScroll = new ntScroll("winningDetailsItems", {
        pullRefresh: true,
        pullMore: true,
        pullHandler: function (info) {
            if (info.type === "more") {
                if (pageIndex < pageCount)
                    pageIndex++;
            } else {
                pageIndex = 0;
            }

            tools.ajax("/getDetails_api", {
                pageIndex: pageIndex,
                pageSize: pageSize
            }, function (data) {
                if (info.type === "more") {
                    detailsScroll.setPullRefreshResult(true);
                }

                detailsScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);

                $.delay(function () {
                    detailsScroll[info.type === "more" ? "setPullMoreState" : "setPullRefreshState"](false);
                    detailsScroll.haveMore(pageIndex < pageCount);
                }, 800);
            }, {
                loading: false,
                errorCallback: function () {
                    detailsScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](false);

                    $.delay(function () {
                        detailsScroll[info.type === "more" ? "setPullMoreState" : "setPullRefreshState"](false);
                        detailsScroll.haveMore(pageIndex < pageCount);
                    }, 800);
                }
            });
        }
    });
});

tools.sendData("加载抽奖记录页");