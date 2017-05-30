$(function () {
    new ntScroll("infoListContainer");
});
var flag = false;

/*抽奖信息滚动代码*/
 
 var ul = $("#trendDiv").bind("transitionend,webkitTransitionEnd", function () {
    ul.append(ul.children().eq(0)).clearTransition().css({
        transform: "translate3d(0, 0, 0)",
        webkitTransform: "translate3d(0, 0, 0)"
    });
});

var timeoutIdx = setInterval(function () {
    ul.transition("transform", 0.6).css({
        transform: "translate3d(0, -.5rem, 0)",
        webkitTransform: "translate3d(0, -.5rem, 0)"
    });
}, 3000);

new ntScroll("infoListContainer");

if($("#is_draw").val() == 1){
    $("#ggl").removeClass(".canvas");
}else{
    $(function(){
    var dom = $('#canGGK').doms[0],
        content = $("#content").width();

    offset = { left: dom.offsetLeft, top: dom.offsetTop };
    offset.left = offset.left - (content/2);
    while (dom.offsetParent && dom.offsetParent.tagName != "BODY") {
        dom = dom.offsetParent;

        offset.left += dom.offsetLeft;
        offset.top += dom.offsetTop;
    }
});


/*刮刮卡代码*/
var isClearing = false,
    isDisable = false,
    offset = { left: 0, top: 0 },
    context = $('#canGGK').doms[0].getContext("2d");

    $('#canGGK').attr("width", $('#canGGK').width()).attr("height", $('#canGGK').height());

drawGgkBg();

function drawGgkBg() {

    context.fillStyle = isDisable ? '#987E2D' : '#cccccc';
    context.fillRect(0, 0, context.canvas.width, context.canvas.height);
}

function clearCovering(e) {
    if (isDisable)
        return;

    e.stopPropagation();
    e.preventDefault();

    context.globalCompositeOperation = "destination-out";
    context.fillStyle = "rgb(255,123,172)";
    context.beginPath();

    context.arc(e.targetTouches[0].clientX - offset.left, e.targetTouches[0].clientY - offset.top, 10, 0, Math.PI * 2);
    context.fill();
    context.closePath();

    isClearing = true;
}



function openGgk() {
    context.globalCompositeOperation = "destination-out";
    context.fillStyle = "rgb(255,123,172)";
    context.beginPath();
    context.fillRect(0, 0, context.canvas.width, context.canvas.height);
    context.fill();

    tools.sendData("刮刮卡页-挂开覆盖层");

    /*修改-调接口*/
    var recordId= $("#is_record_id").val();
    

    if(!flag){
    	tools.ajax(tools.url("activeMsdt", "exchange_gua"),{
            recordId:recordId
       },function (data) {
           //console.log(data);
           if (data.state==1){
               tools.alert(data.data);
               flag = true;
           }
       });
    }
}    
     

$.bindEvent(window, "touchend", function () {
    if (isClearing) {
        var w = context.canvas.width, h = context.canvas.height;

        var imgData = context.getImageData(0, 0, w, h),
            pixles = imgData.data,
            tranPixLength = 0;

        for (var i = 0, j = pixles.length; i < j; i += 4) {
            if (pixles[i + 3] < 128)
                tranPixLength++;
        }

        if (tranPixLength / (pixles.length / 4) > 0.4)
            openGgk();

        isClearing = false;
    }
});
}


