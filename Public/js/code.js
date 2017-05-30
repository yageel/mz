function submitData(){
	var phone = $.trim($("#txtMobile").val()),
		pwd = $.trim($("#txtPwd").val());

	if (!phone) {
        tools.alert("请输入手机号!");
        return;
    }

    if (!tools.isMobile(phone)) {
        tools.alert("输入的手机号格式不正确!");
        return;
    }
    if(!pwd){
    	tools.alert("请输入验证码!");
    	return;
    }

     tools.ajax(tools.url("user", "addressedit"), {
        phone: Pwd,
        pwd: usertel,
    }, function (data) {
        tools.alert(data.data, function () {
            history.go(-1);
        });
    });
}