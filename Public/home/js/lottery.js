/*
 * author by:王高飞
 * date:2016-09-01 13:39:25
 */
$(function () {
      new ntScroll("divMainHeha");
      new ntScroll("ullist");
      new ntScroll("activityinfo");
});
      
var lotteryItems = $("#divLotteryItems").children();
var is_run = true;
function stopLottery(){
    is_run = false;
    $("#divLotteryPoints").removeClass("animing");
    $('#divLotteryPoints').find('div').removeClass("active");
    // window.location.reload();
}
function startLottery() {
    var i = i || 5;
    var currentIdx = $("#divLotteryItems").find(".active").index() || 0,
        lotteryCount = 0,
        itemIdx = currentIdx,
        time = 50;

    $("#divLotteryPoints").addClass("animing");

    function _temp() {
        if(!is_run){return false;}
        currentIdx++;

        var lastItemDom;

        if (currentIdx > 7) {
            currentIdx = 0;

            lastItemDom = lotteryItems.eq(7);
        } else {
            lastItemDom = lotteryItems.eq(currentIdx - 1);
        }

        lastItemDom.removeClass("active");

        lotteryItems.eq(currentIdx).addClass("active");

        lotteryCount++;

        if (time < 300000 && is_run) {
            setTimeout(_temp, time);

            if (lotteryCount > 22)
                time += 10;
        } else {
            if (itemIdx == i) {
                //if(state == 93){
                //    tools.addCard(ret.data.card);
                //}else{
                //    tools.alert(msg,function () {
                //        location.href = url;
                //    });
                //}
                //
                //$("#divLotteryPoints").removeClass("animing");
            } else {
                time += 10;
                setTimeout(_temp, time);
                itemIdx++;
                if (itemIdx > 7) {
                    itemIdx = 0;
                }
            }
        }
    }

    setTimeout(_temp, time);
}


function startLottery_new(i,ret) {
    var i = i || 5;

    var currentIdx = $("#divLotteryItems").find(".active").index() || 0,
        lotteryCount = 0,
        itemIdx = currentIdx,
        time = 50;

    $("#divLotteryPoints").addClass("animing");

    function _temp() {
        currentIdx++;

        var lastItemDom;

        if (currentIdx > 7) {
            currentIdx = 0;

            lastItemDom = lotteryItems.eq(7);
        } else {
            lastItemDom = lotteryItems.eq(currentIdx - 1);
        }

        lastItemDom.removeClass("active");

        lotteryItems.eq(currentIdx).addClass("active");

        lotteryCount++;

        if (time < 300) {
            setTimeout(_temp, time);

            if (lotteryCount > 22)
                time += 10;
        } else {
            if (itemIdx == i) {

                if(ret.state == 98){
                    // 实物中奖
                    $('.dialog3 p.title').text(ret.msg);
                    $('.dialog1 a.submit').attr("href",ret.url);
                    $('.dialog3').show();
                    $('#dialog').show();
                    $("#express_theid").val(ret.express_id); //收货地址express_id
                    $("#record_id").val(ret.url);

                }else if(ret.state == 99){
                    //M币 红包
                    $('.dialog1 p.title').text(ret.msg);
                    $('.dialog1 a.submit').attr("href",ret.url);
                    $('.dialog1').show();
                    $('#dialog').show();

                }else if(ret.state == 93){
                    gret = ret;
                    // 微信卡券
                    $('.dialog1 p.title').text(ret.msg);
                    $('.dialog1 a.submit').attr("href","javascript:getCard()");

                    $('.dialog1').show();
                    $('#dialog').show();

                }else if(ret.state == 91){
                    //没有抽中奖品
                    tools.alert(ret.msg,function(){
                        window.location.href = ret.url;
                    });
                }


                //if(state == 93){
                //    tools.addCard(ret.data.card);
                //}else{
                //    tools.alert(msg,function () {
                //        location.href = url;
                //    });
                //}

                $("#divLotteryPoints").removeClass("animing");
                return true;
            } else {
                time += 10;
                setTimeout(_temp, time);
                itemIdx++;
                if (itemIdx > 7) {
                    itemIdx = 0;
                }
            }
        }
    }
    setTimeout(_temp, time);
}
var gret, _ticket;
function getCard(){
    tools.addCard(gret.data.card)
}
//完成注册
function lottery_register(){
	 $("#dialog").css("display","block");
	 $("#registerContent").css("display","block");
}
function lottery(_id) {
   
    tools.sendData("活动抽奖页-点击抽奖");
    startLottery();//+'&ticket='+_ticket  , 'ticket':_ticket
    tools.ajax(tools.url('active','dolottery'),{'id':_id},function (ret) {

        // startLottery(ret.data.index,ret.msg,ret.url,ret.state,ret);
        $("#divLotteryPoints").removeClass("animing");
        stopLottery(ret.data.index);

        //if(ret.state == 98){
        //// 实物中奖
        //	$('.dialog3 p.title').text(ret.msg);
        //    $('.dialog1 a.submit').attr("href",ret.url);
        //    $('.dialog3').show();
        //    $('#dialog').show();
        //    $("#express_theid").val(ret.express_id); //收货地址express_id
        //   $("#record_id").val(ret.url);
        //
        //}else if(ret.state == 99){
        ////M币 红包
        //    $('.dialog1 p.title').text(ret.msg);
        //    $('.dialog1 a.submit').attr("href",ret.url);
        //    $('.dialog1').show();
        //    $('#dialog').show();
        //
        //}else if(ret.state == 93){
        //    gret = ret;
        //// 微信卡券
        //	$('.dialog1 p.title').text(ret.msg);
        //    $('.dialog1 a.submit').attr("href","javascript:getCard()");
        //
        //    $('.dialog1').show();
        //    $('#dialog').show();
        //
        //}else{
        //	//没有抽中奖品
        //	tools.alert(ret.msg);
        //}
        startLottery_new(ret.data.index, ret);
        return;

    },{
        errorCallback: function (errorMsg) {
            tools.alert(errorMsg.msg);
            stopLottery(5);
        },
        dollowUpOver:function() {
             stopLottery(5);
    }
    });
}
//更新收货地址
function update_express(){
	var theId=$("#express_theid").val();
	var mobile=$("#mobile").val();
	var username=$("#username").val();
	var address=$("#address").val();
	var record_id= $("#record_id").val();
    var remark = $('#remark').val();
    if(!mobile){
        tools.alert("请填写电话号码!");
        return;
    }
    if(!username){
        tools.alert("请填写姓名!");
        return;
    }
    if(!address){
        tools.alert("请填写收货地址!");
        return;
    }
	if (!tools.isMobile(mobile)) {
        tools.alert("输入的手机号格式不正确!");
        return;
    }

	tools.ajax(tools.url("active", "update_express"),{
		'theId':theId,
		'username':username,
		'phone':mobile,
		'detailAddress':address,
        'remark':remark
		
   },function (data) {
	   if(data.state==1){
		   location.href=record_id;
	   }
       
   });
}

/*点击页面任何地方都隐藏内容*/
$("#dialog").click(function () {
  $('#registerContent').css("display","none");
  $("#dialog").hide();
})


/*退出按钮*/
$(function(){
    $(".cancel").click(function(){
        // alert(111);
        $('.dialog1').css("display","none");
        $('.dialog2').css("display","none");
        $('.dialog3').css("display","none");
        $('#dialog').css("display","none");
    })
});

$(function(){
    var timeoutIdx;
    $("input[type=text]").bind("blur,focus",function(e){
        if(e.type == "blur"){
            timeoutIdx = setTimeout(function(){
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
                window.scrollTop = 0;
            },50)
        }else{
            if(timeoutIdx){
                clearTimeout(timeoutIdx);
                timeoutIdx = null;
            }
        }
    })
})	

tools.sendData("加载活动抽奖页");