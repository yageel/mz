/**
 * Created by assassin on 2017/4/21.
 */
//下拉刷新
var mainScroll;
var timer;
(function () {
    function getData() {
        var pageIndex = 1,
            pageCount = 50000,
        realPageCount =$("#totalPage").val();

        // pageCount = 100000;
        var theId=$("#active_id").val();//活动id

        window.mainScroll = new ntScroll("chatContent",{
            pullMore: true,
            pullRefresh: true,
            pullHandler: function (info) {
                if (info.type === "more" && pageIndex<realPageCount)
                    pageIndex++;
                else if(info.type != "more")
                    pageIndex = 1;

                tools.ajax(tools.url("mvkt", "chat_more"), {
                    p: pageIndex,
                    id:theId
                }, function (data){
                    if (info.type === "refresh")
                        $("#chatContent").find("div.content").remove();
                    // pageCount = data.data.pageCount;
                    realPageCount=data.data.pageCount;
                    appendDom(data,theId);

                    mainScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);

                    mainScroll.haveMore(pageIndex < pageCount);
                    //-----------

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

        //当前页数等于总页数时执行自动定时刷新聊天内容
        function listenRecord(){
            var pageNow=parseInt(pageIndex),
                pageAll=parseInt(pageCount);
            tools.alert(pageNow+"|"+pageAll);
            if(pageIndex ==pageCount){
                alert(222);
            }
            timer = setTimeout(listenRecord, 2000);
        }
        // listenRecord();
    }

    function  appendDom(data,theId) {
        var html = '',
            contentList = data.data.list,
            len = contentList.length;

        var activeId=data.data.activeId;
        if(activeId !=theId){
            contentList="";
            len=0;
            pageCount=0;
        }

        var container = document.createDocumentFragment();

        for(var i=0;i<len;i++) {
            var id = contentList[i]["id"];
            //过滤重复的
            if($("#"+id).html()){
                continue;
            }
           var headimg = contentList[i]["headimg"],
            content = contentList[i]["content"],
            send_time = contentList[i]["send_time"],
            type = contentList[i]["type"],
            isImage = contentList[i]["isImage"], //isImage判断是否有上传照片
            theImage = contentList[i]["content"];//图片内容

           var tempDom = document.createElement("div");
           html = "";

            if (type == 1) {
                html += '    <span><img class="headimg" src="' + headimg + '" /></span>';
                html += '    <div class="item">';
                html += '       <p>' + send_time + '</p>';
                if(!isImage){
                    html += '       <div class="problemContent">' + content + '</div>';
                }else{
                    html += '       <div><img src="' + theImage + '" class="upload"></div>'
                }
                html += '    </div>';
            } else {
                html += '    <div class="wrap-content"></div>';
                html += '    <div class="item mr">';
                html += '       <p class="tr">' + send_time + '</p>';
                if(!isImage){
                    html += '       <div class="problemContent c1">';
                    html += '           <span>' + content + '</span>';
                    html += '       </div>';
                }else{
                    html += '       <div><img src="' + theImage + '" class="upload"></div>'
                }
                html += '    </div>';
                html += '    <span>';
                html += '       <img class="headimg" src="' + headimg + '" />';
                html += '    </span>';
            }

            tempDom.innerHTML = html;
            tempDom.className = "content wrap";
            tempDom.id=id;

            var imgs = tempDom.querySelectorAll("img");

            for (var j = 0; j < imgs.length; j++) {
                imgs[j].addEventListener("load", imgLoaded);
            }

            container.appendChild(tempDom);
        }

        function imgLoaded() {
            mainScroll.refresh();
        }

        $("#chatContent").append(container);
    }
    getData();
})();

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
                datetime = ret.data.datetime,//时间
                id=ret.data.id;


            html += '<div class="content wrap" id="'+id+'">';
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

//发送图片聊天
//上传头像
function sendChatImage() {
    var theid=$("#active_id").val();
    //不仅阻止了事件往上冒泡，而且阻止了事件本身
    wx.chooseImage({
        count: 1, // 默认9
        success: function (res) {
            wx.uploadImage({
                localId: res.localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                isShowProgressTips: 1, // 默认为1，显示进度提示
                success: function (res2) {
                    tools.ajax(tools.url("mvkt", "chat_img_upload"),
                        {
                            imageid:res2.serverId,
                            id:theid
                        },function (ret) {
                            if(ret.state ==10){
                                appendImg(ret.data);
                                $(".upload").bind('load',function(){
                                    mainScroll.refresh();
                                },false);
                            }else{
                                tools.alert(ret.msg);
                            }
                        });
                }
            });
        }
    });
    return false;
};

//拼接发布图片内容
function appendImg(data) {
    var img="__PUBLIC__/../"+data.theImage,
        html = "";
    html += '<div class="content wrap" id="'+data.id+'">';
    html += '    <div class="wrap-content"></div>';
    html += '    <div class="item mr">';
    html += '       <p class="tr">' + data.send_time + '</p>';
    html += '       <div><img src="' + img + '" class="upload" ></div>';
    html += '    </div>';
    html += '    <span>';
    html += '       <img class="headimg" src="' + data.headimg + '" />';
    html += '    </span>';
    html += '</div>';
    $("#chatContent").append(html);
}

