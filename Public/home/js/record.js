/**
 * Created by assassin on 2017/3/24.
 */

$(function () {
    new ntScroll("uploadContent");
});

var isRecording = false;
var currentAudioServerID,
    currentAudioLocalID,
    finishTime=0,
    timer;
// currentAudioServerID = localStorage.getItem("currentAudioServerID");
//开始录音
function recordTime(){
    finishTime++;
    //开始录音计时
    $(".start_voice").show();
    $("#start_count_time").text(finishTime);
    timer = setTimeout("recordTime()", 1000);
}

$("#startRecord").click(function(){
    if (isRecording) {
            stopRecord();
        } else {
            if(currentAudioServerID){
                tools.alert("您当前已完成录音，请删除录音后重新进行录音！");
                return;
            }
            tools.confirm("是否确认开始录音!",function(i,type){
                if(type === "ok") {
                    $("#startRecord").attr("src","/Public/images/signUp/recording.png");
                    isRecording = true;
                    currentAudioServerID = "";
                    currentAudioLocalID = "";
                    wx.startRecord();
                    finishTime=0;
                    recordTime();
                    // $(this).addClass("active");

                }
        })
    }
})

//结束录音
function stopRecord() {
    if (isRecording) {
        try{
            wx.stopRecord({
                success: recordComplate
            });

        } catch (e) {
            tools.alert(e.message);
        }

        isRecording = false;
    }

}


//上传录音
function uploadAudio() {
    wx.uploadVoice({
        localId: currentAudioLocalID,
        isShowProgressTips: 1,
        success: function (res) {
            currentAudioServerID = res.serverId;

        }
    });
}

function recordComplate(res) {
    clearInterval(timer);
    $("#startRecord").attr("src","/Public/images/signUp/record.png");
    currentAudioLocalID = res.localId;
    currentAudioServerID = undefined;

    uploadAudio();
    $("#changeAudioPlay").show();
    $("#recordTime").text(finishTime);
    tools.alert("录音完成！");
}

//提交报名表
$("#submit").click(function(){
    var themeVal = $.trim($("#theme").val());

    if(!currentAudioServerID){
        tools.alert("请上传录音作品 !");
        return false;
    }
    if(!themeVal){
        tools.alert("请填写作品名称 !");
        return false;
    }
    if(!themeVal.length > 30){
        tools.alert("请填写30字以内的作品名称 !");
        return false;
    }

    tools.loading("加载中...");
    tools.ajax(tools.url("xqb", "ajax_voice_upload"),
        {
            title:themeVal,
            soundid:currentAudioServerID

        },function (ret) {
            console.log(ret);
            tools.closeLoading();
            if(ret.state ==10){
                $("#voteNumber").text(ret.data);
                $("#dialog").show();
                $("#numContent").show();
                //location.href =tools.url("xqb", "index");
            }else {
                tools.alert(ret.msg);
            }
            return;
    });
});

function sureBtn(){
    $("#dialog").hide();
    $("#numContent").hide();
    location.href =tools.url("xqb", "index");
}

function changeAudioPlayState() {
    var changeAudioPlay = $("#changeAudioPlay").removeClass("new-audio");
    if(!currentAudioLocalID){
        //没有上传录音直接退出
        return;
    }
    if (changeAudioPlay.hasClass("bofang")) {
        changeAudioPlay.removeClass("bofang");

        wx.stopVoice({
            localId: currentAudioLocalID
        });
    } else {
        changeAudioPlay.addClass("bofang");

        wx.playVoice({
            localId: currentAudioLocalID
        });
    }
}


//清除录音
function clear_voice() {
    tools.confirm("是否确认清除此段录音！",function(i, type){
        if(type === "ok"){
            currentAudioServerID="";
            currentAudioLocalID="";
            $("#changeAudioPlay").hide();
            $(".start_voice").hide();
            $("#start_count_time").text(0);
        }
    })
}
