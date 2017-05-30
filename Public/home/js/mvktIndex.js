/**
 * Created by assassin on 2017/4/17.
 */
// $(function(){
//     new ntScroll("btnContent");
// });

$.bind(window,"touchstart,touchend,touchmove,touchcancel", function (e) {
    e.stopPropagation();
})

var audioDom;

//播放录音
function listItemClick(audio,tag) {
    $("#preloader_1").show();
    if (!audioDom) {
        audioDom = document.createElement("audio");
        document.body.appendChild(audioDom);
    }
    audioDom.src = audio;
    audioDom.play();
    audioDom.addEventListener('ended', function (){
        $("#preloader_1").hide();
    }, false);
}

//倒计时(循环遍历dom,取到3个不同的active_start_time开台时间,再分别加上对应的countDown方法)

    $(".main-item-list").find("div.wrap").each(function(){

        var endTime = $(this).find(".active_start_time").val();
        var countdown = $(this).find(".countdown");
        var appointment = $(this).find(".appointment");
        var actually = $(this).find(".actually").text();
        var count = $(this).find(".count").text();
        var num = $(this).find(".num");
        console.log(actually+" "+count);

        if(actually == count){
            appointment.attr("src","/Public/images/meinv/appointment-checked.png");
            num.addClass('c3');
        }


        countDown(endTime,countdown);
        //打开地理位置
        var longitude=parseFloat($(this).find(".longitude").val());
        var latitude=parseFloat($(this).find(".latitude").val());
        var address=$(this).find(".address").val();
        $(this).find(".info.ad").bind("click",function () {
            openPosition(latitude,longitude,address);
        });
    });

    function openPosition(lat,long,addr) {
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
    function countDown(time,countdown){
        var day_elem = countdown.find('.day');
        var hour_elem = countdown.find('.hour');
        var minute_elem = countdown.find('.minute');
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

//赴约跳转链接
function gotoDetail(tag){
    var href = $(tag).attr("data-href");
    console.log(href);
    if (href) {
        location.href = href;
    }
}

//

