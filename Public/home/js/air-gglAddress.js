/*
 * author by:王高飞
 * date:2016年10月13日 16:47:02
 */

var provinceScroll = new ntScroll("ulProvince"),
    cityScroll = new ntScroll("ulCity"),
    countyScroll = new ntScroll("ulCounty");

function hideSelect() {
    $("#divSelect").addClass("hide");
}

function showSelect() {
    $("#divSelect").css("left", "0").addClass("show");
}

function initProvince() {
    var html = '';
    $.each(address_data, function (i) {
        html += '<li onclick="provinceItemClick(this, ' + i + ')">' + this.name + '</li>';
    });
    $("#ulProvince").html(html);
}

function initCity(cityIndex) {
    var html = '';
    $.each(address_data[cityIndex].city, function (i) {
        html += '<li onclick="cityItemClick(this, ' + cityIndex + ', ' + i + ')">' + this.name + '</li>';
    });
    $("#ulCity").html(html);
}

function initCounty(provinceIndex, cityIndex) {
    var html = '';
    $.each(address_data[provinceIndex].city[cityIndex].area, function (i) {
        html += '<li onclick="countyItemClick(this, ' + provinceIndex + ', ' + cityIndex + ', ' + i + ')">' + this.toString() + '</li>';
    });
    $("#ulCounty").html(html);
}

function provinceItemClick(tag, index) {
    initCity(index);

    showSelectSon(1);

    $("#divProvinceVal").text(address_data[index].name);

    cityScroll.refresh();

    $(tag).addClass("active").siblings().removeClass("active");
}

function cityItemClick(tag, provinceIndex, index) {
    initCounty(provinceIndex, index);

    showSelectSon(2);

    $("#divCityVal").text(address_data[provinceIndex].city[index].name);

    countyScroll.refresh();

    $(tag).addClass("active").siblings().removeClass("active");
}

function countyItemClick(tag, provinceIndex, cityIndex, index) {
    hideSelect();

    $("#divCountyVal").text(address_data[provinceIndex].city[cityIndex].area[index]);

    $("#lblAddress").text(address_data[provinceIndex].name + address_data[provinceIndex].city[cityIndex].name + address_data[provinceIndex].city[cityIndex].area[index]);

    $(tag).addClass("active").siblings().removeClass("active");
}

function showSelectSon(index, tag) {
    if (tag) {
        var text = $(tag).prev().text();
        if (text && text.indexOf("请选择") === 0)
            return;
    }

    $("#divProvince")[index == 0 ? "show" : "hide"]();
    $("#divCity")[index == 1 ? "show" : "hide"]();
    $("#divCounty")[index == 2 ? "show" : "hide"]();
}

function toggleDefault(tag) {
    if ($(tag).hasClass("selected"))
        $(tag).removeClass("selected");
    else
        $(tag).addClass("selected");
}

$("#divSelect").bind("animationend,webkitAnimationEnd", function () {
    if ($(this).hasClass("show"))
        $(this).removeClass("show");
    else if ($(this).hasClass("hide"))
        $(this).css("left", "100%").removeClass("hide");
});

initProvince();
provinceScroll.refresh();

/*表单验证*/
function submit(){
    var username = $.trim($("#txtName").val()),
        usertel = $.trim($("#txtPhone").val()),
        userAddress = $.trim($("#lblAddress").text()),
		recordId= $.trim($("#recordId").val()),
        detailAddress = $.trim($("#txtAddressDetail").val());

    if (!username) {
        tools.alert("请输入真实姓名!");
        return;
    }

    if (!usertel) {
        tools.alert("请输入电话号码!");
        return;
    }

    if (!tools.isMobile(usertel)) {
        tools.alert("输入的手机号格式不正确!");
        return;
    }

    if(!userAddress){
        tools.alert("请输入收货地址!");
        return;
    }

    if(!detailAddress){
        tools.alert("请输入详细收货地址!");
        return;
    }

     tools.ajax(tools.url("activeAirport", "record_express"), {
		recordId: recordId,
        username: username,
        usertel: usertel,
        userAddress: userAddress,
        detailAddress: detailAddress,
    }, function (data) {
        if (data.state == 1) {
			location.href = tools.url("activeAirport", "record_list");
		}else{
			tools.alert("保存收货信息失败，请重新操作!");
		}
		return;
    });
}