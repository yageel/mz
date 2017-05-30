/*
 * author by:maofei
 * date:2016-10-13 
 */

 //领取优惠券
function ticket(tag) {
    var theId=$(tag).parent().find(".prize_ticketId").val();
	var isdh="prize_ticket"+theId;
	if(theId >0){
		tools.confirm("请消费时由店家员工点击“立即使用”，否则此优惠无效！", function (i, type) {
            if (type === "ok"){
                tools.ajax(tools.url("activeAirport", "exchang_prize"), {
				   recordId: theId
				   
				}, function (data) {
					if (data.state == 1) {
							//更新兑换成功
							$(tag).css("display","none");
							$("#"+isdh).css("display","block");
					}
				});
			}
        });
        return false;
	}else{
		return false;
	}
	
    
}

			