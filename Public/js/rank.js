/**
 * Created by assassin on 2017/3/24.
 */
$(function(){
    new ntScroll("infoContent4");
})

//少儿组
$("#child").click(function(){
     $("#active_id").val("A组");
    $(this).prev().addClass("checked");
    $(this).removeClass("checked");
    $("#childLists").show();
    $("#aContent").hide();
    $("#cContent").hide();
    $("#dContent").hide();
    $("#bContent").show();
});

//成人组
$("#adult").click(function(){
    $("#active_id").val("成人");
    $(this).next().addClass("checked");
    $(this).removeClass("checked");
    $("#childLists").hide();
    $("#bContent").hide();
    $("#cContent").hide();
    $("#dContent").hide();
    $("#aContent").show();
});

//A组
$("#btnA").click(function(){
    $("#active_id").val("A组");
    $(this).siblings().find(".bg").addClass("checked");
    $(this).find(".bg").removeClass("checked");
    $("#aContent").hide();
    $("#cContent").hide();
    $("#dContent").hide();
    $("#bContent").show();
});

//B组
$("#btnB").click(function(){
    $("#active_id").val("B组");
    $(this).siblings().find(".bg").addClass("checked");
    $(this).find(".bg").removeClass("checked");
    $("#aContent").hide();
    $("#bContent").hide();
    $("#dContent").hide();
    $("#cContent").show();
});

//C组
$("#btnC").click(function(){
    $("#active_id").val("C组");
    $(this).siblings().find(".bg").addClass("checked");
    $(this).find(".bg").removeClass("checked");
    $("#aContent").hide();
    $("#bContent").hide();
    $("#cContent").hide();
    $("#dContent").show();
});

//搜索
//思路:通过搜索图标触发onclikc事件，然后调后台接口。回调函数里面先移除所有dom，再根据返回数据拼接dom。

$("#search").click(function(){
    var txtVal = $("#txtSearch").val(),
        len = txtVal.length;
    if(!len){
        location.href =tools.url("xqb", "index");
        return;
    }

    tools.ajax(tools.url("xqb","ajax_join_list"),{
        p:1,
        key:txtVal
    },function(ret){console.log(ret);
        if(ret.data.total ==0){
            tools.alert("没有找到相对应的选手!");
            return;
        }
        $("div.group").remove();//移除导航
        $("#infoContent").find("div.problemContent1").remove();
        var html = '',
            contentList = ret.data.list,
            username = '',
            userimg = '',
            votenum=0,
            soundid='',
            id=0,
            usertype,
            len = contentList.length;
        for(var i=0;i<len;i++){
            username = contentList[i]["name"];
            userimg = contentList[i]["imageid"];
            votenum = contentList[i]["pollsnum"];
            usertype = contentList[i]["usertype"];
            id = contentList[i]["id"];
            soundid = "__PUBLIC__/../"+contentList[i]["soundid"];

            html += '<div class="problemContent1 wrap">';
            html += '    <div class="mr">';
            html += '      <img class="headpic" src="__PUBLIC__/../' + userimg + '" />';
            html += '    </div>';
            html += '    <div class="wrap-content info">';
            html += '       <span class="num">' + id + '</span>';
            html += '       <span>' + username + '</span>';
            html += '       <span id="voteAdd'+ id +'" class="fr">' + votenum + '票</span>';
            if(usertype == 0){
                html += "<p class=\"record color1\" onclick=\"listItemClick('"+soundid+"',this)\">";
            }else{
                html += "<p class=\"record color2\" onclick=\"listItemClick('"+soundid+"',this)\">";
            }
            html += '<img src="/Public/images/signUp/oval.png" class="oval">';
            html += '</p>'
            html += '    </div>';
            html += '    <div class="choose">';
            html += '       <img src="/Public/images/choose.png" class="chooseImg" onclick=\"chooseVote(this)\">';
            html += '    </div>';
            html += '</div>';
        }

        $("#aContent").hide();
        $("#eContent").show();
        $("#infoContent4").html(html);

    });
});


//下拉刷新
var mainScroll,
    mainScroll1,
    mainScroll2,
    mainScroll3;

$(function() {
    var pageIndex = 1,
        pageCount = $("#infoContent").attr("data-page");

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    window.mainScroll = new ntScroll("infoContent",{
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;
            var thetype = $("#active_id").val();
            tools.ajax(tools.url("xqb", "ajax_join_list"), {
                thetype:thetype,
                p: pageIndex
            }, function (ret){
                if (info.type === "refresh")
                    $("#aContent").find("div.problemContent1").remove();
                var html = '',
                    contentList = ret.data.list,
                    username = '',
                    userimg = '',
                    votenum=0,
                    soundid='',
                    id=0,
                    len = contentList.length;
                for(var i=0;i<len;i++){
                    username = contentList[i]["name"];
                    userimg = contentList[i]["imageid"];
                    votenum = contentList[i]["pollsnum"];
                    id = contentList[i]["id"];
                    soundid = "__PUBLIC__/../"+contentList[i]["soundid"];

                    html += '<div class="problemContent1 wrap">';
                    html += '    <div class="mr">';
                    html += '      <img class="headpic" src="__PUBLIC__/../' + userimg + '" />';
                    if(pageIndex <=1){
                    	if(i == 0){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num1.png" class="pm" />';
                        }else if(i == 1){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num2.png" class="pm" />';
                        }else if(i == 2){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num3.png" class="pm" />';
                        }
                    	
                    }
                    html += '    </div>';
                    html += '    <div class="wrap-content info">';
                    html += '       <span class="num">' + id + '</span>';
                    html += '       <span>' + username + '</span>';
                    html += '       <span id="voteAdd'+ id +'" class="fr">' + votenum + '票</span>';
                    if(thetype == "成人"){
                        html += "<p class=\"record color1\" onclick=\"listItemClick('"+soundid+"',this)\">";
                    }else{
                        html += "<p class=\"record color2\" onclick=\"listItemClick('"+soundid+"',this)\">";
                    }
                    html += '<img src="/Public/images/signUp/oval.png" class="oval">';
                    html += '</p>'
                    html += '    </div>';
                    html += '    <div class="choose">';
                    html += '       <img src="/Public/images/choose.png" class="chooseImg" onclick=\"chooseVote(this)\">';
                    html += '    </div>';
                    html += '</div>';
                }
                $("#adultContent").append(html);

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

$(function() {
    var pageIndex = 1,
        pageCount = $("#infoContent1").attr("data-page");

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    window.mainScroll1 = new ntScroll("infoContent1",{
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;
            var thetype = $("#active_id").val();
            console.log(thetype);
            tools.ajax(tools.url("xqb", "ajax_join_list"), {
                thetype:thetype,
                p: pageIndex
            }, function (ret){
                if (info.type === "refresh")
                    $("#bContent").find("div.problemContent1").remove();
                var html = '',
                    contentList = ret.data.list,
                    username = '',
                    userimg = '',
                    votenum=0,
                    soundid='',
                    id=0,
                    len = contentList.length;
                for(var i=0;i<len;i++){
                    username = contentList[i]["name"];
                    userimg = contentList[i]["imageid"];
                    votenum = contentList[i]["pollsnum"];
                    id = contentList[i]["id"];
                    soundid = "__PUBLIC__/../"+contentList[i]["soundid"];

                    html += '<div class="problemContent1 wrap">';
                    html += '    <div class="mr">';
                    html += '      <img class="headpic" src="__PUBLIC__/../' + userimg + '" />';
                    if(pageIndex <=1){
                    	if(i == 0){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num1.png" class="pm" />';
                        }else if(i == 1){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num2.png" class="pm" />';
                        }else if(i == 2){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num3.png" class="pm" />';
                        }
                    	
                    }
                    html += '    </div>';
                    html += '    <div class="wrap-content info">';
                    html += '       <span class="num">' + id + '</span>';
                    html += '       <span>' + username + '</span>';
                    html += '       <span id="voteAdd'+ id +'" class="fr">' + votenum + '票</span>';
                    if(thetype == "成人"){
                        html += "<p class=\"record color1\" onclick=\"listItemClick('"+soundid+"',this)\">";
                    }else{
                        html += "<p class=\"record color2\" onclick=\"listItemClick('"+soundid+"',this)\">";
                    }
                    html += '<img src="/Public/images/signUp/oval.png" class="oval">';
                    html += '</p>'
                    html += '    </div>';
                    html += '    <div class="choose">';
                    html += '       <img src="/Public/images/choose.png" class="chooseImg" onclick=\"chooseVote(this)\">';
                    html += '    </div>';
                    html += '</div>';
                }
                    $("#childA").append(html);

                mainScroll1[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);
                pageCount = ret.data.total_page;

                mainScroll1.refresh();
                mainScroll1.haveMore(pageIndex < pageCount);

                if (info.type === "refresh") {
                    $.delay(function () {
                        mainScroll1.setPullRefreshState(false);
                    }, 800);
                } else {
                    mainScroll1.setPullMoreState(false, false);
                }
            },{
                loading: false,
                errorCallback: function () {
                    pageCount = 0;

                    mainScroll1[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](false);

                    $.delay(function () {
                        mainScroll1[info.type === "more" ? "setPullMoreState" : "setPullRefreshState"](false);
                        mainScroll1.haveMore(pageIndex < pageCount);
                    }, 800);
                },
                type: "get"
            });
        }
    });
});

$(function() {
    var pageIndex = 1,
        pageCount = $("#infoContent2").attr("data-page");

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    window.mainScroll2 = new ntScroll("infoContent2",{
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;
            var thetype = $("#active_id").val();
            console.log(thetype);
            tools.ajax(tools.url("xqb", "ajax_join_list"), {
                thetype:thetype,
                p: pageIndex
            }, function (ret){
                if (info.type === "refresh")
                    $("#cContent").find("div.problemContent1").remove();
                var html = '',
                    contentList = ret.data.list,
                    username = '',
                    userimg = '',
                    votenum=0,
                    soundid='',
                    id=0,
                    len = contentList.length;
                for(var i=0;i<len;i++){
                    username = contentList[i]["name"];
                    userimg = contentList[i]["imageid"];
                    votenum = contentList[i]["pollsnum"];
                    id = contentList[i]["id"];
                    soundid = "__PUBLIC__/../"+contentList[i]["soundid"];

                    html += '<div class="problemContent1 wrap">';
                    html += '    <div class="mr">';
                    html += '      <img class="headpic" src="__PUBLIC__/../' + userimg + '" />';
                    if(pageIndex <=1){
                    	if(i == 0){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num1.png" class="pm" />';
                        }else if(i == 1){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num2.png" class="pm" />';
                        }else if(i == 2){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num3.png" class="pm" />';
                        }
                    	
                    }
                    html += '    </div>';
                    html += '    <div class="wrap-content info">';
                    html += '       <span class="num">' + id + '</span>';
                    html += '       <span>' + username + '</span>';
                    html += '       <span id="voteAdd'+ id +'" class="fr">' + votenum + '票</span>';
                    if(thetype == "成人"){
                        html += "<p class=\"record color1\" onclick=\"listItemClick('"+soundid+"',this)\">";
                    }else{
                        html += "<p class=\"record color2\" onclick=\"listItemClick('"+soundid+"',this)\">";
                    }
                    html += '<img src="/Public/images/signUp/oval.png" class="oval">';
                    html += '</p>'
                    html += '    </div>';
                    html += '    <div class="choose">';
                    html += '       <img src="/Public/images/choose.png" class="chooseImg" onclick=\"chooseVote(this)\">';
                    html += '    </div>';
                    html += '</div>';
                }
                    $("#childB").append(html);

                mainScroll2[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);
                pageCount = ret.data.total_page;

                mainScroll2.refresh();
                mainScroll2.haveMore(pageIndex < pageCount);

                if (info.type === "refresh") {
                    $.delay(function () {
                        mainScroll2.setPullRefreshState(false);
                    }, 800);
                } else {
                    mainScroll2.setPullMoreState(false, false);
                }
            },{
                loading: false,
                errorCallback: function () {
                    pageCount = 0;

                    mainScroll2[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](false);

                    $.delay(function () {
                        mainScroll2[info.type === "more" ? "setPullMoreState" : "setPullRefreshState"](false);
                        mainScroll2.haveMore(pageIndex < pageCount);
                    }, 800);
                },
                type: "get"
            });
        }
    });
});

$(function() {
    var pageIndex = 1,
        pageCount = $("#infoContent3").attr("data-page");

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    window.mainScroll3 = new ntScroll("infoContent3",{
        pullMore: true,
        pullRefresh: true,
        pullHandler: function (info) {
            if (info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;
            var thetype = $("#active_id").val();
            console.log(thetype);
            tools.ajax(tools.url("xqb", "ajax_join_list"), {
                thetype:thetype,
                p: pageIndex
            }, function (ret){
                if (info.type === "refresh")
                    $("#dContent").find("div.problemContent1").remove();
                var html = '',
                    contentList = ret.data.list,
                    username = '',
                    userimg = '',
                    votenum=0,
                    soundid='',
                    id=0,
                    len = contentList.length;
                for(var i=0;i<len;i++){
                    username = contentList[i]["name"];
                    userimg = contentList[i]["imageid"];
                    votenum = contentList[i]["pollsnum"];
                    id = contentList[i]["id"];
                    soundid = "__PUBLIC__/../"+contentList[i]["soundid"];

                    html += '<div class="problemContent1 wrap">';
                    html += '    <div class="mr">';
                    html += '      <img class="headpic" src="__PUBLIC__/../' + userimg + '" />';
                    if(pageIndex <=1){
                    	if(i == 0){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num1.png" class="pm" />';
                        }else if(i == 1){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num2.png" class="pm" />';
                        }else if(i == 2){
                            html += '        <img src="__PUBLIC__/../Public/images/signUp/num3.png" class="pm" />';
                        }
                    	
                    }
                    
                    html += '    </div>';
                    html += '    <div class="wrap-content info">';
                    html += '       <span class="num">' + id + '</span>';
                    html += '       <span>' + username + '</span>';
                    html += '       <span id="voteAdd'+ id +'" class="fr">' + votenum + '票</span>';
                    if(thetype == "成人"){
                        html += "<p class=\"record color1\" onclick=\"listItemClick('"+soundid+"',this)\">";
                    }else{
                        html += "<p class=\"record color2\" onclick=\"listItemClick('"+soundid+"',this)\">";
                    }
                    html += '<img src="/Public/images/signUp/oval.png" class="oval">';
                    html += '</p>'
                    html += '    </div>';
                    html += '    <div class="choose">';
                    html += '       <img src="/Public/images/choose.png" class="chooseImg" onclick=\"chooseVote(this)\">';
                    html += '    </div>';
                    html += '</div>';
                }
                    $("#childC").append(html);

                mainScroll3[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](true);
                pageCount = ret.data.total_page;

                mainScroll3.refresh();
                mainScroll3.haveMore(pageIndex < pageCount);

                if (info.type === "refresh") {
                    $.delay(function () {
                        mainScroll3.setPullRefreshState(false);
                    }, 800);
                } else {
                    mainScroll3.setPullMoreState(false, false);
                }
            },{
                loading: false,
                errorCallback: function () {
                    pageCount = 0;

                    mainScroll3[info.type === "more" ? "setPullMoreResult" : "setPullRefreshResult"](false);

                    $.delay(function () {
                        mainScroll3[info.type === "more" ? "setPullMoreState" : "setPullRefreshState"](false);
                        mainScroll3.haveMore(pageIndex < pageCount);
                    }, 800);
                },
                type: "get"
            });
        }
    });
});

//选择框点击事件
var arrIdLists = [];
function chooseVote(tag){
    // tools.alert("1");
    var numId = parseInt($(tag).parent().prev().find(".num").text());
    if($(tag).hasClass('active')){
        if(numId>0){
            for(var i=0;i<arrIdLists.length;i++){
                if(arrIdLists[i] === numId){
                    var deleteArr = arrIdLists.splice(i,1);
                    // console.log(deleteArr);
                }
            };
        }
        $(tag).removeClass('active');
        $(tag).attr('src','/Public/images/choose.png');
    }else{
        if(numId>0){
            arrIdLists.push(numId);
        }
        $(tag).attr('src','/Public/images/choose-checked.png');
        $(tag).addClass('active');
    }
    // console.log(arrIdLists);
}


//投票
function vote(){
    console.log(arrIdLists);
    var vote_num=parseInt($("#vote_num").val());//剩余投票数

    if(arrIdLists.length <= 0){
        tools.alert("请选择您要进行投票的作品!");
        return;
    }

    if(vote_num <=0){
        tools.alert("每天有3次投票机会，今天可用票数已用完，明天再来吧~");
        $(".chooseImg").attr('src','/Public/images/choose.png');
        return;
    }

    if(arrIdLists.length > vote_num){
        tools.alert("您当前最多只能投"+vote_num+"票!");
        return;
    }
    var idlist = arrIdLists.join("|");


    tools.loading("加载中...");
    tools.ajax(tools.url("xqb", "ajax_vote"),
        {
            idlist:idlist
        },function (ret) {
            tools.closeLoading();
            if(ret.state ==10){
                var len = ret.data.vote_ids.length,
                    voteIds = ret.data.vote_ids;
                for(var i=0;i<len;i++){
                    var voteNum = parseInt($("#voteAdd"+voteIds[i]).text())+1;
                    $("#voteAdd"+voteIds[i]).text(voteNum+"票");
                }
                var success_vote=parseInt(ret.data.vote_num);
                console.log(success_vote);
                var less_vote=vote_num-success_vote;
                $("#vote_num").val(less_vote);//更新剩余投票数
                arrIdLists = [];
            }
            tools.alert(ret.msg);
            $(".chooseImg").attr('src','/Public/images/choose.png');
            return;
        });
};
