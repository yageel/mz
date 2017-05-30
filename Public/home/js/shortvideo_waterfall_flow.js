(function () {
    var winHeight, scrollHeight,
        bottomOffset = 50, isLoading = false,
        pageIndex = 1, pageCount = 5, videoHeight = ($(window).width() - 30) / 2 * 0.73 + 22,
        verticalVideoHeight = videoHeight * 2 + 10;

    $("#divMainList").find("a").each(function () {
        var tag = $(this),
            isVertical = tag.hasClass("vertical");

        tag.height(isVertical ? verticalVideoHeight : videoHeight).find("img").height(isVertical ? verticalVideoHeight - 22 : videoHeight - 22);
    });

    function getScrollTop() {
        if (document.documentElement && document.documentElement.scrollTop)
            return document.documentElement.scrollTop;

        if (document.body)
            return document.body.scrollTop;

        return 0;
    }

    function getScrollHeight() {
        var scrollHeight = 0,
            bodyScrollHeight = 0,
            documentScrollHeight = 0;

        if (document.body)
            bodyScrollHeight = document.body.scrollHeight;

        if (document.documentElement)
            documentScrollHeight = document.documentElement.scrollHeight;

        scrollHeight = (bodyScrollHeight - documentScrollHeight > 0) ? bodyScrollHeight : documentScrollHeight;

        return scrollHeight;
    }

    function appendVideo(listVideo) {
        var firstDirection,
            leftDom = $("#divLeftList"),
            rightDom = $("#divRightList"),
            leftChildren = leftDom.children(),
            rightChildren = rightDom.children(),
            leftAppendHtml = '', rightAppendHtml = '',
            verticalVideos = [], horizontalVideos = [],
            leftIsCurrent = leftChildren.length <= rightChildren.length,
            currentDirection = getVideoDirection(leftIsCurrent ? leftChildren.last() : rightChildren.last()) == "horizontal" ? "vertical" : "horizontal",
            currentCount = 1, loopDirection;

        if (currentDirection == "vertical") {
            var currentChildren = leftIsCurrent ? leftChildren : rightChildren;

            if (currentChildren.length < 2 || getVideoDirection(currentChildren.eq(-2)) == "vertical") {
                currentDirection = "horizontal";

                if (currentChildren.length == 0)
                    currentCount = 2;
            }
        } else {
            currentCount = 2;
        }

        for (var i = 0; i < listVideo.length; i++) {
            if (listVideo[i].direction === "vertical")
                verticalVideos.push(listVideo[i]);
            else
                horizontalVideos.push(listVideo[i]);
        }

        for (var i = 0; i < currentCount; i++) {
            var videoHTML = createVideoHtml(currentDirection, verticalVideos, horizontalVideos);

            if (videoHTML) {
                if (leftIsCurrent)
                    leftAppendHtml += videoHTML;
                else
                    rightAppendHtml += videoHTML;
            } else {
                break;
            }

            if (i == currentCount - 1) {
                leftIsCurrent = !leftIsCurrent;

                if (!leftIsCurrent)
                    currentDirection = currentDirection == "horizontal" ? "vertical" : "horizontal";
            }
        }

        while (verticalVideos.length || horizontalVideos.length) {
            currentCount = currentDirection == "horizontal" && horizontalVideos.length > 1 ? 2 : 1;

            for (var i = 0; i < currentCount; i++) {
                if (leftIsCurrent)
                    leftAppendHtml += createVideoHtml(currentDirection, verticalVideos, horizontalVideos);
                else
                    rightAppendHtml += createVideoHtml(currentDirection, verticalVideos, horizontalVideos);
            }

            leftIsCurrent = !leftIsCurrent;

            if (!leftIsCurrent)
                currentDirection = currentDirection == "horizontal" ? "vertical" : "horizontal";
        }

        leftDom.append(leftAppendHtml);
        rightDom.append(rightAppendHtml);
    }

    function videoSort(v1, v2) {
        return v1.sort < v2.sort;
    }

    function createVideoHtml(videoDirection, verticalVideos, horizontalVideos) {
        var videoData;

        if (videoDirection == "vertical" && verticalVideos.length)
            videoData = verticalVideos.shift();

        if (!videoData && horizontalVideos.length)
            videoData = horizontalVideos.shift();

        if (!videoData)
            return '';

        var html = '<a style="height:' + (videoData.direction == "vertical" ? verticalVideoHeight : videoHeight) + 'px;" href="' + videoData.href + '" class="video-item' + (videoData.direction == "vertical" ? " vertical" : "") + '">';
        html += '<img style="height:' + (videoData.direction == "vertical" ? verticalVideoHeight - 22 : videoHeight - 22) + 'px;" src="' + videoData.img + '" />';
        html += '<span>' + videoData.describe + '</span>';
        html += '</a>';

        return html;
    }

    function getVideoDirection(videoDom) {
        return videoDom.width() > videoDom.height() ? "horizontal" : "vertical";
    }

    /*
     * 开发时请修改此方法，改成ajax方式请求，这里是模拟网络请求过程。
     * 后台返回数据格式如下：
     *{
     *  state: 200,                         状态值，为200则代表数据请求正常
     *  msg: '没有更多了',                  状态值不为200时的错误提示信息
     *  data: {
     *    pageCount: 10                     总页数
     *    list: [{
     *      img: '../images/temp/temp8.jpg',          视频预览图
     *      describe: '长沙美食地图',                 视频描述文字
     *      id: 'xxxxxx',                             标识该视频的唯一ID
     *      href: '//baidu.com/id=1',                 点击该视频跳转到的详情页链接,
     *      direction: 'vertical',                    视频方向(horizontal 或者 vertical),
     *      sort: 1                                   排序权重（因为前端拿到数据后会自行排序，后端可不排序，横屏权重和竖屏权重独立计算，不冲突）
     *    },{
     *      img: '../images/temp/temp8.jpg',          视频预览图
     *      describe: '长沙美食地图',                 视频描述文字
     *      id: 'xxxxxx',                             标识该视频的唯一ID
     *      href: '//baidu.com/id=1',                 点击该视频跳转到的详情页链接,
     *      direction: 'vertical',                    视频方向(horizontal 或者 vertical),
     *      sort: 1                                   排序权重（因为前端拿到数据后会自行排序，后端可不排序，横屏权重和竖屏权重独立计算，不冲突）
     *    }]
     *  }
     *}
     */
    function getVideoData() {
        // var data = {
        //     state: 200,
        //     data: {
        //         pageCount: 4,
        //         list: [{
        //             img: '../images/temp/temp11.jpg',
        //             href: 'https://www.baidu.com',
        //             describe: '1',
        //             id: '1',
        //             direction: 'vertical',
        //             sort: 1
        //         }, {
        //             img: '../images/temp/temp10.jpg',
        //             href: 'https://www.baidu.com',
        //             describe: '2',
        //             id: '1',
        //             direction: 'vertical',
        //             sort: 2
        //         }, {
        //             img: '../images/temp/temp8.jpg',
        //             href: 'https://www.baidu.com',
        //             describe: '3',
        //             id: '1',
        //             direction: 'vertical',
        //             sort: 3
        //         }, {
        //             img: '../images/temp/temp9.jpg',
        //             href: 'https://www.baidu.com',
        //             describe: '4',
        //             id: '1',
        //             direction: 'vertical',
        //             sort: 4
        //         }, {
        //             img: '../images/temp/temp1.jpg',
        //             href: 'https://www.baidu.com',
        //             describe: '5',
        //             id: '1',
        //             direction: 'horizontal',
        //             sort: 1
        //         }, {
        //             img: '../images/temp/temp2.jpg',
        //             href: 'https://www.baidu.com',
        //             describe: '6',
        //             id: '1',
        //             direction: 'horizontal',
        //             sort: 2
        //         }, {
        //             img: '../images/temp/temp3.jpg',
        //             href: 'https://www.baidu.com',
        //             describe: '7',
        //             id: '1',
        //             direction: 'horizontal',
        //             sort: 3
        //         }]
        //     }
        // }
        //请求后台分页数据
    	var videoType=$("#video_more_type").val();
        if(!videoType){
            //console.log("请求分页类型参数错误"+videoType);
            return;
        }
        var method;  //12代表搜索

        if(videoType == "12"){
            method="ajax_search";
        }else if(videoType =="13"){
            method="ajax_watch";
        }else if(videoType =="14"){
            method="ajax_collection_page";
        }else{
            method="ajax_video_list";
        }

        if (pageIndex <= pageCount) {
            $.tools.ajax($.tools.url("shortvideo", method), {
                p: pageIndex,
                thetype: videoType,
                key: $.tools.queryString("key")
            },{
                loading: false,
                success: function (data) {
                    console.log(data);
                    if (data.state !== 200) {
                        $("#divLoadingMore").text(data.msg);
                    } else {
                        pageIndex++;
                        pageCount = data.data.pageCount;
                        if (pageIndex > pageCount)
                            $("#divLoadingMore").text("没有更多了");

                        appendVideo(data.data.list);

                        isLoading = false;
                    }
                }
            });
        }
    }

    $(window).scroll(function () {
        if (!winHeight)
            winHeight = $(window).height();

        scrollHeight = getScrollHeight();

        if (!isLoading && winHeight + getScrollTop() >= scrollHeight - bottomOffset) {
            isLoading = true;

            getVideoData();
        }
    });

    getVideoData();
})();