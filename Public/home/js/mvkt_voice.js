/**
 * Created by assassin on 2017/4/13.
 */
var isRecording = false,
    currentAudioServerID,
    currentAudioLocalID,
    finishTime=0,
    timer;



//开始录音按钮
$("#talk").click(function(){

    record();
})

//录音
function recordTime(){
    finishTime++;

    timer = setTimeout("recordTime()", 1000);
}

function record(){
    if (isRecording) {
        stopRecord();
    } else {
        if(currentAudioServerID){
            tools.alert("您当前已完成录音！");
            return;
        }
        isRecording = true;
        currentAudioServerID = "";
        currentAudioLocalID = "";
        wx.startRecord();
        finishTime=0;
        recordTime();
    }
}

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
            alert(currentAudioServerID+"=====");

            tools.alert("录音完成！");

            //调用后台上传录音接口
            tools.ajax(tools.url("mvkt", "chat_voice"),
                {
                    id:$("#active_id").val(),
                    soundid:currentAudioServerID
                },function (ret) {
                    tools.closeLoading();
                    if(ret.state ==10){
                        append_voice(finishTime,ret.data);
                    }else{
                        tools.alert(ret.msg);
                    }

                });

        }
    })
};
alert('test0');
function recordComplate(res) {
    clearInterval(timer);
    currentAudioLocalID = res.localId;
    // currentAudioServerID = undefined;

    uploadAudio();
    // $(".send").show();
    // $("#start").text("重新录音");

    // clear_voice();
    return;
}

function changeAudioPlayState2() {
    $("#preloader_2").show();
    var changeAudioPlay = $("#changeAudioPlay");
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

//监听语音播放完毕接口
function changeAudioPlayState() {
    // $("#preloader_2").hide();
}

//清除录音
function clear_voice() {
    currentAudioServerID="";
    currentAudioLocalID="";
    isRecording=false;
    finishTime=0;


}