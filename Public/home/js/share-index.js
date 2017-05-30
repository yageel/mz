/**
 * Created by assassin on 2016/11/21.
 */
var mainScroll;
$(function() {
    var pageIndex = 1,
        pageCount = $("#searchCommentContent").attr("data-page"),
        pageSize = 20;

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    window.mainScroll = new ntScroll("searchCommentContent", {
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;

            tools.ajax(tools.url("active", "get_join_list"), {
                id: $('#active_id').val(),
                p: pageIndex,
                key: $("#keyword").val()
            }, function (ret){
                console.log(ret + '测试');
                if (info.type === "refresh")
                    $("#searchCommentContent").find("div.item").remove();
                var html = '',
                    contentList = ret.data.list,
                    username = '',
                    userimg = '',
                    len = contentList.length;
                for(var i=0;i<len;i++){
                    username = contentList[i]["user"]["wx_name"];
                    userimg = contentList[i]["user"]["wx_pic"];
                    total_vote = contentList[i]["total_vote"];
                    is_vote = contentList[i]["is_vote"];
                    pic_url = contentList[i]["pic_url"];
                    title = contentList[i]["title"];
                    id = contentList[i]["id"];


                    html += '<div class="item">';
                    html += '  <div class="list-head wrap1">';
                    html += '    <div class="list-img">';
                    html += '      <img class="headimg" src="' + userimg + '" />';
                    html += '    </div>'
                    html += '    <div class="list-info wrap-content">'
                    html += '      <h3>' + username + '</h3>';
                    html += '      <p class="description color-link">' + title + '</p>';
                    html += '    </div>'
                    html += '   </div>'
                    // /active/share_detail/id/
                    html += '   <div class="bg" onclick="gotoLink(this, \'活动页-点击详情\', '+id+')" data-href="'+tools.url('active','share_detail',{id:id})+'">';
                    html += '      <img src="' + pic_url +'" class="contain">';
                    html += '   </div>';
                    html += '   <div class="list-vote wrap1">';
                    html += '      <div class="vote-num color-link">';
                    html += '        <span data-vid="'+ id +'">' + total_vote + '票</span>'
                    html += '      </div>';
                    html += '      <div class="voteBtn wrap-content">';
                    html += '        <a class="'+(is_vote?'vote-btn1':'vote-btn') +'" data-id="'+ id +'"   '+(is_vote?'':'onclick="vote('+id+')"')+' href="javascript:void(0)">' + (is_vote?'已投票':'投票') + '</a>';
                    html += '      </div>';
                    html += '   </div>';
                    html += '</div>';
                }
                $("#searchCommentContent").append(html);


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
});
