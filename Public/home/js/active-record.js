/**
 * Created by assassin on 2017/4/19.
 */

//下拉刷新
var mainScroll;

$(function() {
    var pageIndex = 1,
        pageCount = $("#activeRecord").attr("data-page");

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    window.mainScroll = new ntScroll("activeRecord",{
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;
            tools.ajax(tools.url("mvkt", "ajax_active_record"), {
                p: pageIndex
            }, function (ret){
                console.log(ret);
                /*
                 * 开发时请修改此方法，改成ajax方式请求，这里是模拟网络请求过程。
                 * 后台返回数据格式如下：
                 *{
                 *  state: 200,                               状态值，为200则代表数据请求正常
                 *  msg: '没有更多了',                         状态值不为200时的错误提示信息
                 *  data: {
                 *    pageCount: 10                           总页数
                 *    list: [{
                 *      img: '../images/temp/temp8.jpg',      用户头像
                 *      nickname: '陈大壮',                    用户昵称
                 *      start_time: '2017-02-13 17:50:21',     开台时间
                 *      status: '0',(待审核)                     开台状态(分为待审核、1进行中和3已参加三张),
                 *      id:"1"                                标识唯一开台id
                 *    }]
                 *  }
                 *}
                 */
                if (info.type === "refresh")
                    $("#activeRecord").find("div.line").remove();

                var html = '',
                    pageCount = ret.data.pageCount,
                    contentList = ret.data.list,
                    len = contentList.length;

                console.log(pageCount);
                console.log(contentList);

                for(var i=0;i<len;i++){
                    username = contentList[i]["owner"];
                    userimg = contentList[i]["img"];
                    data_time = contentList[i]["start_time"];
                    id = contentList[i]["id"];
                    status = contentList[i]["status"];

                   var href = contentList[i]["href"];


                    html += '<div class="line">';
                    html += '    <div class="item wrap">';
                    html += '       <span><img src="'  + userimg + '" class="item__bg"></span>';
                    html += '       <div class="wrap-content">';
                    html += '           <p>';
                    html += '               <span class="c1">' + username + '</span>';
                    html += '               <span class="small">的开台</span>';
                    html += '           </p>';
                    html += '           <p class="small">' + data_time + '</p>';
                    html += '           <p class="small">' + status + '</p>';
                    html += '       </div>';
                    html += '       <p class="details">';
                    html += '           <a href="'+href+'">查看详情</a>';
                    html += '       </p>';
                    html += '       <em class="logo"></em>';
                    html += '   </div>';
                    html += '</div>';
                }
                console.log(html);
                $("#activeRecord").append(html);

                mainScroll[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);

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

//编辑
$("#edit").click(function(){
    var personinfo = document.getElementById("personinfo");
    var nickname=$("#username").text(); //用户昵称
    var desc=$("#signature").text();  //用户签名

    if($(this).text() == "编辑"){
        $(this).text("确定");
        $(".replace").show();
        $("#username").html("<input type='text' class='c2' id='input1' value='"+nickname+"'>");
        $("#signature").html("<input type='text' class='c2' id='input2' value='"+desc+"'>");
        //给图片添加chooseOpenTableImage()方法
        personinfo.addEventListener("click",chooseOpenTableImage,false);
    }else if($(this).text() == "确定"){
        $(".replace").hide();
        var val1 = $("#input1").val(),
            val2 = $("#input2").val(),
            username = document.getElementById("username"),
            signature = document.getElementById("signature"),
            input1 = document.getElementById("input1"),
            input2 = document.getElementById("input2");
            //ajax保存修改数据
            tools.ajax(tools.url("mvkt", "edit_info"),
            {
                name:val1,
                desc:val2,
                imageid:$('#service_id').val()
            },function (ret) {
                tools.closeLoading();
                if(ret.state ==10){
                    username.innerText = val1;
                    signature.innerText = val2;
                    $("#edit").text("编辑");
                    personinfo.removeEventListener("click",chooseOpenTableImage,false);
                }
            });
        return;
    }
});

//上传头像
function chooseOpenTableImage() {
    //不仅阻止了事件往上冒泡，而且阻止了事件本身
    wx.chooseImage({
        count: 1, // 默认9
        success: function (res) {
            wx.uploadImage({
                localId: res.localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                isShowProgressTips: 1, // 默认为1，显示进度提示
                success: function (res2) {
                    tools.ajax(tools.url("mvkt", "img_upload"),
                        {
                            imageid:res2.serverId
                        },function (ret) {
                            tools.closeLoading();
                            if(ret.state ==10){
                                $('#service_id').val("__PUBLIC__/../"+ret.data);
                                $('#preview').attr('src',"__PUBLIC__/../"+ret.data);

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