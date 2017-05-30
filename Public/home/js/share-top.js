/**
 * Created by assassin on 2016/11/21.
 */


        var mainScroll;
        $(function () {
            var pageIndex = 1,
                pageCount = $("#rankCommentContent").attr("data-page"),
                pageSize = 20;

            pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

            mainScroll = new ntScroll("rankCommentContent", {
                pullMore: true,
                pullRefresh: true,
                pullHandler: function (info) {
                    if (info.type === "more")
                        pageIndex++;
                    else
                        pageIndex = 1;

                    tools.ajax(tools.url("active", "get_top_list"), {
                        id: $('#active_id').val(),
                        p: pageIndex
                    }, function (ret) {
                        console.log(ret);
                        if (info.type === "refresh")
                            $("#rankCommentContent").find("div.info").remove();

                        var html = '',
                            contentList = ret.data.list,
                            username = '',
                            userimg = '',
                            rowno = '',
                            total_vote = '',
                            len = contentList.length;
                        console.log(len);
                        for (var i = 0; i < len; i++) {
                            username = contentList[i]["user"]["wx_name"];
                            userimg = contentList[i]["user"]["wx_pic"];
                            total_vote = contentList[i]["total_vote"];
                            rowno = contentList[i]["rowno"];
                            id = contentList[i]["id"];

                            html += '<div class="info" onclick="gotoLink(this, \'活动页-点击详情\', ' + id + ')" data-href="' + tools.url('active', 'share_detail', {id: id}) + '">';
                            html += '  <div class="list">';
                            html += '      <img class="small-head" src="' + userimg + '" />';
                            html += '      <span class="user-name">' + username + '</span>';
                            html += '      <span class="user-vote">' + total_vote + '票</span>';
                            html += '      <h1 class="user-rank">' + rowno + '</h1>';
                            html += '   </div>';
                            html += '</div>';
                        }
                        $("#rankCommentContent").append(html);

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
        });


        /*点击关闭按钮*/
        $("#close").click(function () {
            $('#scoreContent').css("display", "none");
            $("#dialog").hide();
        })
        function searchScore() {
            $("#dialog").show();
            $('#scoreContent').show();
        }

        /*查看活动规则*/
        function rules() {
            if (!$("#rules").hasClass('on')) {
                $("#rules").addClass('on');
                //展开内容区域的时候，重新刷新加载滚动实例
                scroll.refresh();
                mainScroll.refresh();
                $("#search").text("点击加载 >");
            } else {
                $("#rules").removeClass('on');
                scroll.refresh();
                mainScroll.refresh();
                $("#search").text("收起加载 <");
            }
        };
