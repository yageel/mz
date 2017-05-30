/**
 * Created by assassin on 2016/11/21.
 */
var mainScroll;
$(function() {
    var pageIndex = 1,
        pageCount = $("#detailCommentContent").attr("data-page"),
        pageSize = 20;

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    mainScroll = new ntScroll("detailCommentContent", {
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;

            tools.ajax(tools.url("active", "get_comment_list"), {
                id: $('#join_id').val(),
                p: pageIndex
            }, function (ret){
                console.log(ret);
                if (info.type === "refresh")
                    $("#detailCommentContent").find("div.item").remove();

                    var html = '',
                        contentList = ret.data.list,
                        username = '',
                        userimg = '',
                        content = '',
                        time = '',
                        id = '',
                        comment_id = '',
                        len = contentList.length;
                    for(var i=0;i<len;i++){
                        id = contentList[i]["id"];
                        username = contentList[i]["user"]["wx_name"];
                        userimg = contentList[i]["user"]["wx_pic"];
                        content = contentList[i]["content"];
                        time = contentList[i]["date_time"];
                        comment_id = contentList[i]["comment_id"];
                        console.log(comment_id);
                        var parent_comment = '',
                            reply_content = '',
                            reply_name = '';
                        if(comment_id > 0){
                            parent_comment = contentList[i]["parent_comment"];
                            reply_content = contentList[i]["parent_comment"]["content"];
                            reply_name = contentList[i]["parent_comment"]["user"]["wx_name"];
                        }
                        //if(contentList[i]["parent_comment"].length > 0){
                        //    parent_comment = contentList[i]["parent_comment"];
                        //    reply_content = contentList[i]["parent_comment"]["content"];
                        //    reply_name = contentList[i]["parent_comment"]["user"]["wx_name"];
                        //}
                        html += '<div class="item" data-id="' + id+ '" data-name="" onclick="reply(this)">';
                        html += '  <div class="wrap1">';
                        html += '     <div class="item-img">';
                        html+=  '        <img src="'+ userimg +'" alt="'+username+'">';
                        html+=  '     </div>';
                        html += '     <div class="item-info wrap-content">';
                        html += '       <span class="name">'+ username + '</span>';
                        html += '       <div style="float:right;display: inline-block;">';
                        html += '           <span class="color-common">'+ time +'</span>'
                        html += '       </div>'
                        if(comment_id>0 && parent_comment != ''){
                            html += '<p class="comments color-link">回复' + reply_name + '：' + content + '</p>';
                            html += '<div class="comments2 color-link">' + reply_content + '</div>'
                        }else{
                            html += '      <p class="comments color-link">'+ content + '</p>';
                        }
                        html += '     </div>';
                        html += '   </div>';
                        html += '</div>';
                    }
                $("#detailCommentContent").append(html);

                mainScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);
                pageCount = ret.data.total_page;

                mainScroll.refresh();
                mainScroll.haveMore(pageIndex < pageCount);

                if (info.type === "refresh") {
                    $.delay(function () {
                        mainScroll.setPullRefreshState(false);
                    }, 800);
                } else {
                    mainScroll.setPullMoreState(false, false);
                }
            },{
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
    //发送
    $('.sumbit').click(function(){
        var contentVal = $.trim($('textarea[name="content"]').val());
        if(contentVal.length<2 || contentVal.length>300){
            tools.alert("评论内容请保持在2-300长度~");
            return false;
        }
        tools.loading("加载中...");
        tools.ajax(tools.url("active", "share_comment"),{
            id:$('input[name="join_id"]').val(),
            comment_id:$('input[name="comment_id"]').val(),
            content:contentVal
        },function (ret) {
            console.log(ret.data.html);
            tools.closeLoading();
            if(ret.state == 200){
                var tempDom = document.createElement("div");
                tempDom.innerHTML = ret.data.html;
                if(tempDom.children.length){
                    $("#xxpl-container").after(tempDom.children[0]);
                }
                mainScroll.refresh();
            }
            $('#comment_block').hide();
        });
    });
});

function comment(tag,e){
    tag = $(tag);
    e.stopPropagation();
    var html = '';
    html += '<div class="item" onclick="showList()">';
    html += '  <div class="wrap1">';
    html += '      <div class="item-img">';
    html += '        <img src="/Public/images/task-icon.png" alt="">';
    html += '      </div>';
    html += '      <div class="item-info wrap-content">';
    html += '         <span class="name color-link">虞龙</span>';
    html += '         <div style="float:right;display: inline-block;">';
    html += '            <span class="color-common">2016.08.15</span>';
    html += '            <span class="color-common">14:32</span>';
    html += '         </div>';
    html += '         <textarea name="" id="" cols="30" rows="10" class="comments color-link"></textarea>';
    html += '         <a href="javascript:void(0)" class="sumbit" onclick="sumbit(this,event)">发送</a>';
    html += '     </div>';
    html += '  </div>';
    html += '</div>';
    $("#main-list").append(html);
}

//图片描述字数控制
function check(){
    var maxChars = 40;//最多字符数
    var txt = $("#description").val(),
        len = txt.length;
    console.log(len);
    if (len > maxChars)
        $("#description").val(txt.substring(0,maxChars));
}


