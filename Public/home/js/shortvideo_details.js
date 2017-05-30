(function () {
    var resolution = $("#divVideoContainer").attr("data-resolution").split('x'),
        winWidth = $(window).width(), winHeight = $(window).height(),
        aspectRatio, player, videoHeight;

    if (resolution.length != 2 || !/^\d+$/.test(resolution[0]) || !/^\d+$/.test(resolution[1])) {
        layer.open({
            content: '视频分辨率获取失败！',
            btn: '确认'
        });

        return;
    } else {
        resolution[0] = parseInt(resolution[0]);
        resolution[1] = parseInt(resolution[1]);
    }

    aspectRatio = resolution[1] / resolution[0];

    videoHeight = winWidth * aspectRatio;

    if (videoHeight > winHeight)
        videoHeight = winHeight;

    player = new qcVideo.Player("divVideoContainer", {
        "file_id": $("#hidFileID2").val(),
        "app_id": $("#hidAppID2").val(),
        "width": winWidth,
        "height": videoHeight,
        "auto_play": resolution[1] > resolution[0] ? 1 : 0,     //是否自动播放
        "disable_full_screen": 0,                               //是否禁用全屏
        "disable_drag": 1,                                      //是否禁止拖动时间轴
        "hide_h5_setting": true                                 //是否禁止H5的设置按钮
    }, {
        playStatus: function(status){
            if ('ready' == status){
                $('div[component="bottom_container"]').remove();
            } else if(status =="playing"){
                var vedioId=$("#vedioId").val();
                if(!vedioId){
                    return;
                }
                $.tools.ajax($.tools.url("shortvideo", "ajax_update_palynum"), {
                    id: vedioId
                },{
                    loading: false,
                    success: function (data) {
                        console.log(data);
                    }
                });
            }
        }
    });

    //视频收藏
    $(".favorite-item").click(function(){
        var tag = $(this),
            action=parseInt(tag.attr("data-action")),//是否收藏
            theid=tag.attr("data-id"),
            type = tag.attr("data-type");

        var theAction=0;
        if(action ==1){
            //取消收藏
            theAction=2;
        } else {
            theAction=1;
        }

        //后台处理收藏操作
        $.tools.ajax($.tools.url("shortvideo", "ajax_collection"), {
            id: theid,
            action: theAction
        },{
            loading: false,
            success: function (data) {
                if (data.state == 200) {
                    tag.toggleClass("active");

                    if(theAction ==1){
                        //点赞
                        tag.attr("data-action",1);
                    }else{
                        tag.attr("data-action",0);
                    }

                }else{
                    $.tools.alert(data.msg);
                }
            }
        });
    });

    window.goodClick = function (tag) {
        tag = $(tag);

        var type = tag.attr("data-type"),
            action=parseInt(tag.attr("data-action")),//默认点赞
            theid=tag.attr("data-id"),
            target=tag.attr("data-target"); //视频点赞  评论点赞

        var favorite_nums=parseInt(tag.find("label").text());
        var theAction=0;
        if(action ==1){
            //取消点赞
            theAction=2;
            --favorite_nums;
        }else{
            theAction=1;
            ++favorite_nums;
        }

        //后台处理点赞操作
        $.tools.ajax($.tools.url("shortvideo", "ajax_favorite"), {
            id: theid,
            target:target,
            action:theAction
        }, {
            loading: false,
            success: function (data) {
                if (data.state == 200) {
                    if (target === "comment") {
                        tag.find("label").text(favorite_nums);//重新赋值赞数
                    }

                    tag.toggleClass("active");

                    if (theAction == 1) {
                        //点赞
                        tag.attr("data-action", 1);
                    } else {
                        tag.attr("data-action", 0);
                    }
                } else {
                    $.tools.alert(data.msg);
                }
            }
        });
    }

    $("#btnShowCommentInput").click(function () {
        $("#txtCommentContent").focus();
    });

    //发送评论信息
    $('#sendComment').bind('click',function(event){
        var content=$('#txtCommentContent').val();
        var vid=$("#videoID").val();
		if(content.length <=0){
			$.tools.alert("请输入评论内容");
			return;
		}

        $.tools.ajax($.tools.url("shortvideo", "ajax_add_comment"), {
            id: vid,
            content: content
        },{
            loading: false,
            success: function (data) {
                if (data.state == 200) {
                    $(data.data.html).prependTo(".comment-list").find(".good-item").click(function () {
                        goodClick(this);
                    });
                    $('#txtCommentContent').val('').blur();
                    $("div").remove(".first_comment");
                }else{
                    $.tools.alert(data.msg);
                    return;
                }
            }
        });
    });

    $("#divShare").click(function () {
        $("#divShareMark").show();
    });

    $("#divShareMark").click(function () {
        $(this).hide();
    });
})();

