(function () {
    var winHeight, scrollHeight,
        bottomOffset = 50, isLoading = false,
        commentType= $("#commentType").val(),
        pageIndex = parseInt($("#divLoadingMore").attr("data-index")),
        pageCount = parseInt($("#divLoadingMore").attr("data-count"));

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


    function appendComment(listData) {
        if (listData && listData.length) {
            var html = '';

            $.each(listData, function () {
                with (this) {
                	if(commentType =="video_comment"){
                	    var headimg=head;
                	    if(!headimg){
                            headimg="/Public/images/shortvideo/comment_head.png";
                        }
                		 html += '<div class="comment-item wrap"><div class="user-head"><img src="' + headimg + '"></div><div class="wrap-content"><div class="wrap"><div class="wrap-content comment-info"><p>' + nickName + '</p><label>' + time + '</label></div><div class="good-item' + (isGood ? " active" : "") + '" data-id="' + id + '" data-target="comment" onclick="goodClick(this)" data-action="' + (isGood ? " 1" : "0") + '"><img src="/Public/images/shortvideo/good.png"><img src="/Public/images/shortvideo/good_active.png"><label>' + good + '</label></div></div><p class="comment-content">' + content + '</p></div></div>';
                	}else{
                		var theurl="onclick=\"location.href=$.tools.url('shortvideo', 'details',{ theid:" + video_id + " })\"";
                		html += '<div class="comment-item wrap"'+theurl+'><div class="user-head"><img src="' + head + '"></div><div class="wrap-content"><div class="wrap"><div class="wrap-content comment-info"><p>' + nickName + '</p><label>' + time + '</label></div><div class="good-item' + (isGood ? " active" : "") + '" data-id="' + id + '" data-target="comment" data-action="' + (isGood ? " 1" : "0") + '"><img src="/Public/images/shortvideo/good.png"><img src="/Public/images/shortvideo/good_active.png"><label>' + good + '</label></div></div><p class="comment-content">' + content + '</p></div></div>';
                	}
                   
                }
            });

            $("#divCommentList").append(html);
        }
    }

    function getVideoData() {
        if (pageIndex <= pageCount) {
            $.tools.ajax($.tools.url("shortvideo", "ajax_detail_comment"), {
                p: pageIndex,
                thetype: commentType,
                theid:$("#vedioId").val()
            },{
                loading: false,
                success: function (data) {
                    if (data.state !== 200) {
                        $("#divLoadingMore").text(data.msg);
                    } else {
                        pageIndex++;
                        pageCount = data.data.pageCount;
                        if (pageIndex > pageCount)
                            $("#divLoadingMore").text("没有更多了");

                        appendComment(data.data.list);

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

        if (!isLoading && winHeight + getScrollTop() >= scrollHeight - bottomOffset ) {
            isLoading = true;

            getVideoData();
        }
    });
    getVideoData();
})();
