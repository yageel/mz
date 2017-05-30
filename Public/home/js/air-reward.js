var mainScroll;

$(function () {
    var pageIndex = 1,
        pageCount = $("#rewardContainer").attr("data-page"),
        pageSize = 20;

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    mainScroll = new ntScroll("rewardContainer", {
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;

            tools.ajax(tools.url("activeAirport", "record_api"),{
                p: pageIndex
            }, function (data){
                if (info.type === "refresh")
                     $("#rewardContainer").find("div.content-item").remove();
//                console.log(data);
                var len = data.data.length,
                    content = '',
                    rewardContainer = $("#rewardContainer");

                for(var i=0;i<len;i++){
                    content += data.data[i]["content"];
                }

                rewardContainer.append(content);

                document.getElementById("rewardContainer").appendChild(document.getElementById("ntScrollMore"));


                mainScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);
                pageCount = data.total_pages;
                

                //rewardContainer.find(".pull-down").before(rewardContainer);

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



// /*绘制DOM，分为三种情况*/
// function createItemHTML(item) {
//     var html = '',
//             commentItem = document.createElement("div");

//     with (item) {
//         commentItem.className = "comment-item";
//         // commentItem.setAttribute("onclick", "showMenu(this)");
//         // commentItem.setAttribute("data-id", commentId);

//         html += '   <div class="wrap">';
//         html += '        <div class="wrap-content"><img src ="' + wx_pic + '" /></div>';
//         html += '        <div class="wrap-nav">';
//         html += '               <span class="text-common">' + wx_name + '</span>';
//         html += '               <b class="big-text">' + time + '</b>';
//         html += '         </div>';
//         html += '   </div>';
//     }

//     commentItem.innerHTML = html;

//     return commentItem;
// }
