/**
 * Created by assassin on 2017/4/13.
 */
var scroll = new ntScroll("opendatails2");
var isRecording = false,
    currentAudioServerID,
    currentAudioLocalID,
    finishTime=0,
    timer,
    //游戏，酒水取值
    gamesHtml='',
    wineHtml='',
    //开台地址
    helensAddress="";

//阻止事件冒泡
function stop(e){
    e.stopPropagation();
}

$("#time","#province","#city","#bar","#constellation","#num","#sex","#man","#woman").bind("touchstart,touchend,touchmove",stop,false);

//开台日期
var openTime = document.getElementById("time"),
    currentTime = new Date(),
    year = currentTime.getFullYear(),
    month = currentTime.getMonth() + 1,
    day = currentTime.getDate(),
    nowTime = month+"月"+day+"日";

function convert(d){
    var month1 = d.getMonth()+1,
        month = month1 < 10 ? "0"+month1 : month1,
        day1 = d.getDate(),
        day = day1 < 10 ? "0"+day1 : day1;
    return month+"月"+day+"日";
}

function futureWeek(value){
    var regx = /^(\d{1,2})月(\d{1,2})日$/,
        date = regx.exec(value),
        now = new Date('2017/'+date[1]+"/"+date[2]).getTime(),
        weeks = [];
    for(var i=1;i<=7;i++){
        var d = new Date(now + i*24*60*60*1000);
        weeks.push(convert(d));
    }
    return weeks;
}

futureWeek(nowTime);
openTime.options[1].text = futureWeek(nowTime)[0];
openTime.options[2].text = futureWeek(nowTime)[1];
openTime.options[3].text = futureWeek(nowTime)[2];
openTime.options[4].text = futureWeek(nowTime)[3];
openTime.options[5].text = futureWeek(nowTime)[4];
openTime.options[6].text = futureWeek(nowTime)[5];
openTime.options[7].text = futureWeek(nowTime)[6];

var default_sex=$("#default_sex").val();
if(default_sex =="男"){
    man();
}else{
    woman();
}

//选择男女
function man(){
    $("#man").addClass("checkbox-green");
    $("#man").attr("dataid",1);
    $("#woman").attr("dataid","");
    if($("#man").attr("checked")){
        $("#woman").attr("checked",false);
        $("#woman").removeClass("checkbox-green");
    }else{
        $("#man").attr("checked","checked");
    }
}

function woman(){
    $("#woman").attr("dataid",2);
    $("#man").attr("dataid","");
    $("#man").attr("checked",false);
    $("#man").removeClass("checkbox-green");
    $("#woman").addClass("checkbox-green");
    $("#woman").attr("checked","checked");
}

//台主留言限制在30字以内
function OnInput(){
    var messageVal = $("#message").val();
    if(messageVal.length > 30){
        tools.alert("留言不得超过30个字!");
    }
}

//开台(传值)
$("#open").click(function(){
    var manId = $("#man").attr("dataid"),
        womanId = $("#woman").attr("dataid"),
        demoVal = $("#demo3").val(),
        demoArr = demoVal.split(""),
        demoArr1 = demoArr.splice(5,3),
        newDemoVal = demoArr.join(""),
        imgSrc = $("#preview").attr("src");

    //判断用户是否添加开台头像照片
    // if(!imgSrc){
    //     tools.alert("请添加开台头像照片~");
    //     return false;
    // }

    if(manId === null && womanId === null){
        tools.alert("请选择性别!");
        return false;
    }

    if(!demoVal){
        tools.alert("请选择开台具体时间!");
        return false;
    }

    if(!currentAudioServerID){
        tools.alert("请上传录音!");
        return false;
    }

    var messageText = $("#message").val(),
        constellation = document.getElementById("constellation"),
        num = document.getElementById("num"),
        sex = document.getElementById("sex"),
        index1 = constellation.selectedIndex,
        index2 = num.selectedIndex,
        index3 = sex.selectedIndex,
        index4 = time.selectedIndex,
        constellationText = constellation.options[index1].text,
        numText = num.options[index2].text,
        timeText = time.options[index4].text;

    var arr = timeText.split(""),
        arr1 = arr.splice(2,1,"-"),
        arr2 = arr.splice(5,1),
        newStr = arr.join("");

    var inputList = document.getElementsByTagName("input"),
        len = inputList.length;
    for(var i=0;i<len;i++){
        if(inputList[i].getAttribute("dataid") && inputList[i].getAttribute("dataid") !== undefined){
            var id = inputList[i].getAttribute("dataid");
        }
    }

    // console.log(id);//性别
    // console.log(newDemoVal);//具体时间(19:30)
    // console.log(index4+" "+year+"-"+newStr); //时间(2017-04-19)
    // console.log(index1+' '+constellationText);//星座
    // console.log(index2+' '+numText);//开台人数


    gamesHtml=gamesHtml.substring(0,gamesHtml.length-1);
    // wineHtml=wineHtml.substring(0,wineHtml.length-1);

    if(!messageText){
        tools.alert("请填写台主留言!");
        return false;
    }
    var img=$('#service_id').val();
    if(!img){
        tools.alert("请上传开台头像!");
        return false;
    }
    var per_consume=$("#per_consume").val();
    if(parseFloat(per_consume) <=0){
        tools.alert("请输入正确的人均消费金额!");
        return false;
    }

    //提交后台保存开台信息
    tools.ajax(tools.url("mvkt", "add_active"),
        {
            image_id:img,
            soundid:currentAudioServerID,
            constellation:constellationText,
            games:gamesHtml,
            // wine:wineHtml,
            message:messageText,
            start_time:year+"-"+newStr,
            specific_time:newDemoVal,
            per_consume:per_consume,
            address_id:helensAddress,
            total_num:numText,
            sex_limit:$("#sex").val(),
            sex:id,
            sound_time:finishTime
        },function (ret) {
            console.log(ret);
            tools.closeLoading();
            if(ret.state ==200){
                location.href =tools.url("mvkt", "index");
            }else {
                // tools.alert(ret.msg);
            }
            return;
        });
});

//增加标签
$("#add").click(function(){
    var tableGame = $("#tableGame").val(),
        html = "",
        valLen = tableGame.length,
        len = document.getElementById("game").getElementsByTagName("span").length;
    if(valLen > 6){
        tools.alert("添加的标签名称不能超过6个字");
        return false;
    }
    if(len >= 6){
        tools.alert("您最多只能添加6个标签~");
        $("#tableGame").val("");
        return false;
    }
    if(!tableGame){
        tools.alert("请输入您想添加的标签~");
    }else{
        html = '<span class="b4 btn">' + tableGame + '</span>';
        $("#game").append(html);
        $("#tableGame").val("");
        scroll.refresh();
        //保存添加的游戏内容
        var tmp=tableGame+"|";
        gamesHtml+=tmp;
    }
});

//增加酒水
// $("#add1").click(function(){
//     var tableDrink = $("#tableDrink").val(),
//         html = "",
//         len = document.getElementById("drink").getElementsByTagName("span").length;
//     if(len >= 3){
//         tools.alert("您最多只能添加3个酒水标签~");
//         $("#tableDrink").val("");
//         return false;
//     }
//     if(!tableDrink){
//         tools.alert("请输入您想要的酒水~");
//     }else{
//         html = '<span class="b5 btn">' + tableDrink + '</span>';
//         $("#drink").append(html);
//         $("#tableDrink").val("");
//         scroll.refresh();
//         var tmp=tableDrink+"|";
//         wineHtml+=tmp;
//     }
// });

//开始录音按钮
$("#start").click(function(){

    if($(this).text() =="重新录音"){
        // $(".luyin").show();
        // $("#preloader_1").show();
        clear_voice();
        $(this).text("开始录音");
        record();
    }else{
        $(this).text("结束录音");
        $(".luyin").hide();
        $("#preloader_1").hide();
        record();
    }
})

//录音
function recordTime(){
    finishTime++;
    //开始录音计时
    $("#start_count_time").text(finishTime+'"');
    timer = setTimeout("recordTime()", 1000);
}

function record(){
    if (isRecording) {
        stopRecord();
    } else {
        if(currentAudioServerID){
            tools.alert("您当前已完成录音，请删除录音后重新进行录音！");
            return;
        }
        tools.confirm("是否确认开始录音!",function(i,type){
            if(type === "ok") {
                $(".luyin").show();
                $("#preloader_1").show();
                isRecording = true;
                currentAudioServerID = "";
                currentAudioLocalID = "";
                wx.startRecord();
                finishTime=0;
                recordTime();
            }
        })
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

        }
    })
};
function recordComplate(res) {
    clearInterval(timer);

    currentAudioLocalID = res.localId;
    currentAudioServerID = undefined;

    uploadAudio();
    $(".send").show();
    $("#start").text("重新录音");
    tools.alert("录音完成！");
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
    $("#preloader_2").hide();
}
//清除录音
function clear_voice() {
    currentAudioServerID="";
    currentAudioLocalID="";
    isRecording=false;
    finishTime=0;
    $("#changeAudioPlay").removeClass("bofang");
    $(".send").hide();
    $("#start_count_time").text(0);

}



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
                                $('#service_id').val(ret.data);
                                var imgDom = document.createElement("img"),
                                    headerDom = document.getElementById("addImg"),
                                    pDom = document.createElement("p");
                                imgDom.setAttribute("id","preview");
                                imgDom.setAttribute("src","__PUBLIC__/../"+ret.data);
                                pDom.setAttribute("id","prompt");
                                pDom.innerText = "再次点击图片可更改开台头像~";
                                headerDom.appendChild(imgDom);
                                headerDom.appendChild(pDom);
                                // $('#preview').attr('src',"__PUBLIC__/../"+ret.data);
                                var w = $("#preview").width(),
                                    h = $("#preview").height(),
                                    newH;
                                newH = (h/w) * window.innerWidth;
                                $(".user-info").height(newH);
                                scroll.refresh();
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

//开台地址编辑
$("#province").bind("change",function () {
    var province=$(this).val();
    selectCity(province,0);
});
function selectCity(province,city) {
    tools.ajax(tools.url("mvkt", "ajax_citylist"),
        {
            name:province
        },function (ret) {
            console.log(ret);
            tools.closeLoading();
            if(ret.status ==200){
                $("#city").html(ret.data);
                //城市
                $("#city").val(city);
            }else {
                tools.alert(ret.msg);
            }
            return;
        });
}

$("#city").bind("change",function () {
    var city=$(this).val();
    selectBar(city,0);
});

function selectBar(city,id) {
    tools.ajax(tools.url("mvkt", "ajax_storelist"),
        {
            cityname:city
        },function (ret) {
            console.log(ret);
            tools.closeLoading();
            if(ret.status ==200){
                $("#bar").html(ret.data.html);
                //自动获取的门店
                if(id >0){
                    helensAddress=id;
                    $("#bar").val(id);
                }else{
                    helensAddress=ret.data.id;
                }

            }else {
                tools.alert(ret.msg);
            }
            return;
        });
}

$("#bar").bind("change",function () {
    helensAddress = $(this).val();
    
});