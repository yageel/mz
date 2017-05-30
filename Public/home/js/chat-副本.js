/**
 * Created by assassin on 2017/4/21.
 */
//下拉刷新
var mainScroll = new ntScroll("chatContent");
// var mainScroll;
var timer;
(function () {
    function getData() {
        var pageIndex = 1,
            pageCount = 5;

        // pageCount = 100000;
        var theId=$("#active_id").val();//活动id

                for(var i=0;i<len;i++) {
                    headimg = contentList[i]["headimg"];
                    content = contentList[i]["content"];
                    send_time = contentList[i]["send_time"];
                    id = contentList[i]["id"];
                    type = contentList[i]["type"];  //type为1是其他人评论
                    isImage = contentList[i]["isImage"]; //判断是否上传图片
                for(var i=0;i<len;i++) {
                    headimg = contentList[i]["headimg"];
                    content = contentList[i]["content"];
                    send_time = contentList[i]["send_time"];
                    id = contentList[i]["id"];
                    type = contentList[i]["type"];
                    isImage = contentList[i]["isImage"];
        if (pageIndex <= pageCount) {
alert(pageIndex+"|"+pageCount);
            tools.ajax(tools.url("mvkt", "chat_more"),
                {
                    p: pageIndex,
                    id:theId
                },function (data) {
                    pageIndex++;
                    pageCount = data.data.pageCount;
                    appendDom(data,theId);
                    mainScroll.refresh();
                });
        }


        //当前页数等于总页数时执行自动定时刷新聊天内容
        // function listenRecord(){
        //     var pageNow=parseInt(pageIndex),
        //         pageAll=parseInt(pageCount);
        //     tools.alert(pageNow+"|"+pageAll);
        //     if(pageIndex ==pageCount){
        //         alert(222);
        //     }
        //     timer = setTimeout(listenRecord, 2000);
        // }
        // listenRecord();
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
    function getScrollTop() {
        if (document.documentElement && document.documentElement.scrollTop)
            return document.documentElement.scrollTop;

        if (document.body)
            return document.body.scrollTop;

        return 0;
    }

    window.scroll = function(){
        if (!winHeight)
            winHeight = $(window).height();

        scrollHeight = getScrollHeight();

        if (!isLoading && winHeight + getScrollTop() >= scrollHeight - bottomOffset) {
            isLoading = true;

            getData();
        }
    }

   getData();
})();

//拼接dom
function appendDom(data,theId) {
    var html = '',
        contentList = data.data.list,
        len = contentList.length;
    var activeId=data.data.activeId;
    if(activeId !=theId){
        contentList="";
        len=0;
        pageCount=0;
    }

    for(var i=0;i<len;i++) {
        headimg = contentList[i]["headimg"];
        content = contentList[i]["content"];
        send_time = contentList[i]["send_time"];
        id = contentList[i]["id"];
        type = contentList[i]["type"];
        isImage = contentList[i]["isImage"];


        if (type == 1) {
            html += '<div class="content wrap">';
            html += '    <span><img class="headimg" src="' + headimg + '" /></span>';
            html += '    <div class="item">';
            html += '       <p>' + send_time + '</p>';
            if(!isImage){
                html += '       <div class="problemContent">' + content + '</div>';
            }else{
                html += '       <div><img src="' + isImage + '" class="upload"></div>'
            }
            html += '    </div>';
            html += '</div>';
        } else {
            html += '<div class="content wrap">';
            html += '    <div class="wrap-content"></div>';
            html += '    <div class="item mr">';
            html += '       <p class="tr">' + send_time + '</p>';
            if(!isImage){
                html += '       <div class="problemContent c1">';
                html += '           <span>' + content + '</span>';
                html += '       </div>';
            }else{
                html += '       <div><img src="' + isImage + '" class="upload"></div>'
            }
            html += '    </div>';
            html += '    <span>';
            html += '       <img class="headimg" src="' + headimg + '" />';
            html += '    </span>';
            html += '</div>';
        }
    }

    $("#chatContent").append(html);
}

//发送聊天内容
$("#send").click(function(){
    var commentVal = $("#comment").val();
    var theid=$("#active_id").val();
    if(!commentVal){
        tools.alert("请输入聊天内容!");
        return false;
    }

    // tools.loading("加载中...");
    tools.ajax(tools.url("mvkt", "chat_add"),{
        content:commentVal,
        id:theid
    },function (ret) {
        // tools.closeLoading();
        if(ret.state == 10){
            var html = "",
                userimg = ret.data.userimg, //用户头像
                datetime = ret.data.datetime;//时间

            html += '<div class="content wrap">';
            html += '    <div class="wrap-content"></div>';
            html += '    <div class="item mr">';
            html += '       <p class="tr">' + datetime + '</p>';
            html += '       <div class="problemContent c1">';
            html += '           <span>' + commentVal + '</span>';
            html += '       </div>';
            html += '    </div>';
            html += '    <span>';
            html += '       <img class="headimg" src="' + userimg + '" />';
            html += '    </span>';
            html += '</div>';

            $("#comment").val("");
            $("#chatContent").append(html);
            mainScroll.refresh();
        }
    });
})