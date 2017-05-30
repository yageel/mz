/*
 * author by:王高飞
 * date:2016-07-04 14:21:10
 */
        var mainScroll;

$(function () {
    var pageIndex = 1,
        pageCount = $("#templateCommentContent").attr("data-page"),
        adid = $("#articleId").val(),
        admetaid = $("#admetaid").val(),
        pageSize = 20;

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    mainScroll = new ntScroll("templateCommentContent", {
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;

            tools.ajax(tools.url("adComment", "templatecomment_api"), {
                p: pageIndex,
                pageSize: pageSize,
                adid: adid,
                admetaid: admetaid
            }, function (data) {
                function createSplitDom(name, pars) {
                    var div = document.createElement("div");

                    if (name === '新鲜') {
                        div.id = 'xxpl-container';
                    }

                    div.className = 'dividing-container';

                    div.innerHTML = '<div class="dividing-text"><label class="color-link">' + (data.data[pars] ? name + '评论' : '暂无' + name + '评论') + '</label></div>';

                    return div;
                }

                mainScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);
                pageCount = data.data.total_pages;

                var templateCommentContent = $("#templateCommentContent"),
                        fragment = document.createDocumentFragment();

                if (info.type === "refresh") {
                    templateCommentContent.find("div.dividing-container").remove();
                    templateCommentContent.find("div.comment-item").remove();

                    fragment.appendChild(createSplitDom('热门', 'top3'));

                    $.each(data.data.top3, function () {
                        fragment.appendChild(createItemHTML(this));
                    });

                    fragment.appendChild(createSplitDom('新鲜', 'list'));
                }

                $.each(data.data.list, function () {
                    fragment.appendChild(createItemHTML(this));
                });

                templateCommentContent.find(".pull-down").before(fragment);

                mainScroll.refresh();
                mainScroll.haveMore(pageIndex < pageCount);

                if (info.type === "refresh") {
                    $.delay(function () {
                        mainScroll.setPullRefreshState(false);
                    }, 800);
                } else {
                    mainScroll.setPullMoreState(false, false);
                }
            }, {
                loading: false,
                errorCallback: function () {
                    pageCount = 0;

                    mainScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](false);

                    $.delay(function () {
                        mainScroll[info.type === "more" ? "setPullMoreState" : "setPullRefreshState"](false);
                        mainScroll.haveMore(pageIndex < pageCount);
                    }, 800);
                },
                type: "get"
            });
        }
    });
    $("#frameVideo").attr("src", $("#frameVideo").attr("data-src"));
});

function checkBind() {
    //判断是否登录
    if (!tools.isBind()) {
        tools.confirm("请您先完成注册后进行操作？", function (i, type) {
            if (type === "ok")
                location.href = tools.url("register", "index");
        });
        return false;
    }

    return true;
}

//点赞事件
function changeSupport(tag, e, commId) {
    //检测是否登录
    if (!checkBind())
        return;

    tag = $(tag);
    e.stopPropagation();

    var isSupport = tag.hasClass("support-yes"),
            adid = $("#articleId").val(),
            commentID = commId;

    if (isSupport)
        tag.removeClass("support-yes").removeClass("color-red").addClass("color-remarks").addClass("animing");
    else
        tag.addClass("support-yes").removeClass("color-remarks").addClass("color-red").addClass("animing");

    tools.ajax(tools.url("adComment", "favorites"), {
        theType: isSupport ? 2 : 1,
        commentId: commentID,
        adid: adid
    }, function (data) {
        console.log(data);
        var zanNum = tag.find("label").text(),
                nowNum = 0;

        if (data.state == 1) {
            if (isSupport)
                nowNum = parseInt(zanNum) - 1;
            else
                nowNum = parseInt(zanNum) + 1;
            tag.find("label").text(nowNum.toString());
        }
    }, {
        loading: false
    });
}

(function () {
    var commentID,
            commentLen = 0,
            commentContent,
            tag;



    window.sendComment = function () {
        //判断是否登录
        if (checkBind()) {
            var content = $.trim($("#txtComment").val()),
                    admetaid = $("#admetaid").val();

            if ($("#huifu").val() == 1) {
        
                commentID = $("#thehfId").val();
            } else {
                commentID = 0;
            }

            if (content.length > 140) {
                tools.alert("您的评论内容过长！");
                return;
            }
            if (content.length == 0) {
                tools.alert("请输入点什么");
                return;
            }

            var the_adid = $("#articleId").val();

            tools.ajax(tools.url("adComment", "addComment"), {
                adid: the_adid,
                admetaid: admetaid,
                commentID: commentID || "",
                content: content
            }, function (data) {
                $("#xxpl-container").after(createItemHTML(data.data));
                mainScroll.refresh();
                $("#txtComment").val('');
                $("#huifu").val("0");
            });
        }
    }

    window.commentUser = function () {
        //检测是否登录
        if (!checkBind()) {
            return;
        }
        var userName = tag.find("span.color-link").text(),
                txtComment;
        console.log(userName);
        console.log(tag);
        commentContent = tag.find('p').text();
        if (commentContent.indexOf('回复') === 0) {
            var tmp = commentContent.indexOf('：');
            commentContent = commentContent.substring(tmp + 1);
        }
        $("#huifu").val("1");

        commentID = tag.attr("data-id");
        $("#thehfId").val(commentID);
        txtComment = $("#txtComment").val("回复" + userName + ":").doms[0];


        try {
            txtComment.focus();
            commentLen = txtComment.value.length;

            if (document.selection) {
                var sel = txtComment.createTextRange();
                sel.moveStart('character', len);
                sel.collapse();
                sel.select();
            } else if (typeof txtComment.selectionStart == 'number' && typeof txtComment.selectionEnd == 'number') {
                txtComment.selectionStart = txtComment.selectionEnd = len;
            }
        } catch (e) {
        }

        tag = undefined;
        $("#divCommentMenus").removeClass("show");
    }

    //修改
    window.showMenu = function (item) {
        $("#divCommentMenus").removeClass("hide").addClass("show");
        //判断此评论是否是自己评论的
        commentID = $(item).attr("data-id");
        $("#thehfId").val(commentID);
        tools.ajax(tools.url("adComment", "getMycomment"), {
            commentID: commentID
        }, function (data) {
            //console.log(data);
            if (data.data == 1) {
                //自己的评论
                $("li#reportFlag").html("删除");
                $("#is_jubao").val("0");
            } else {
                $("li#reportFlag").html("举报");
                $("#is_jubao").val("1");
            }
        }, {
            loading: false
        });


        tag = $(item);
    }

    window.hideMenu = function (tag) {
        $("#divCommentMenus").removeClass("show").addClass("hide");

        tag = undefined;

    }

    window.reportUser = function () {
        //检测是否登录
        if (!checkBind()) {
            return;
        }

        commentID = $("#thehfId").val();//tag.attr("data-id");
        var is_delete = $("#is_jubao").val();//1举报 0删除

        tools.ajax(tools.url("adComment", "reportUser"), {
            is_report: is_delete,
            commentID: commentID
        }, function (data) {
            if (data.data == 0) {
                $(".comment-item-" + commentID).css("display", "none");

                return;
            } else {
                tools.alert("感谢您的举报，我们将会及时处理您的反馈信息");
                return;
            }
        });

        tag = undefined;
        $("#divCommentMenus").removeClass("show");
    }

    $("#txtComment").bind("keyup", function () {
        if (this.value.length < commentLen) {
            this.value = "";
            commentID = userID = undefined;
            commentLen = 0;
        }
    });
})();

function createItemHTML(item) {
    var html = '',
            commentItem = document.createElement("div");

    with (item) {
        commentItem.className = "comment-item comment-item-" + commentId;
        commentItem.setAttribute("onclick", "showMenu(this)");
        commentItem.setAttribute("data-id", commentId);

        html += '   <div class="wrap">';
        html += '        <div class="comment-user"><img src ="' + wx_pic + '" /></div>';
        html += '        <div class="comment-content wrap-content color-remarks">';
        html += '           <em>';
        html += '               <span class="color-link">' + wx_name + '</span>';
        html += '               <i>' + time + '</i>';
        html += '            </em>';
        if (item.reply_wx_name) {
            html += '           <p>回复' + reply_wx_name + '：' + content + '</p>';
            html += '           <blockquote>' + reply_content + ' </blockquote>';
        } else {
            html += '           <p>' + content + '</p>';
        }
        html += '         </div>';
        html += '         <div onclick="changeSupport(this,event,' + commentId + ' )" class="comment-support ' + (!is_fav ? 'color-remarks' : 'support-yes color-red') + '">';
        html += '               <div><img src="/Public/images/support-no.png"><img src="/Public/images/support-yes.png"></div>';
        html += '               <label>' + (item.favorites || '0') + '</label>';
        html += '          </div>';
        html += '   </div>';
    }

    commentItem.innerHTML = html;

    return commentItem;
}

