/**
 * Created by assassin on 2017/4/17.
 */
$(function(){
    new ntScroll("activityDetails");
})

    //查看地图
    document.querySelector('#openPositioin').onclick = function (event) {
        var lat=parseFloat($("#latitude").val());
        var long=parseFloat($("#longitude").val());
        var addr=$(".address").val();
        //tools.alert(lat+"|"+long+"|"+addr);
        wx.ready(function () {

            wx.openLocation({
                latitude: lat, // 纬度，浮点数，范围为90 ~ -90
                longitude:long , // 经度，浮点数，范围为180 ~ -180。
                name: 'helens酒吧', // 位置名
                address:addr , // 地址详情说明
                scale: 15, // 地图缩放级别,整形值,范围从1~28。默认为最大
                infoUrl: '' // 在查看位置界面底部显示的超链接,可点击跳转
            });


        });

    }


//倒计时
var endTime = $("#start_time").val();
$(function(){
    countDown(endTime,"#countdown");
});

function countDown(time,id){
    var day_elem = $(id).find('.day');
    var hour_elem = $(id).find('.hour');
    var minute_elem = $(id).find('.minute');
    var end_time = new Date(time).getTime(),//月份是实际月份-1
        sys_second = (end_time-new Date().getTime())/1000;
    var timer = setInterval(function(){
        if (sys_second > 1) {
            sys_second -= 1;
            var day = Math.floor((sys_second / 3600) / 24);
            var hour = Math.floor((sys_second / 3600) % 24);
            var minute = Math.floor((sys_second / 60) % 60);
            day_elem && day_elem.text(day);//计算天
            hour_elem.html(hour<10?"0"+hour:hour);//计算小时
            minute_elem.html(minute<10?"0"+minute:minute);//计算分钟
        } else {
            clearInterval(timer);
        }
    }, 1000);
}

//分享
$("#share").click(function(){
        $("#shadow").show();
        $("#arrow").show();
})

$("#shadow").click(function(){
    $(this).hide();
    $("#arrow").hide();
})

