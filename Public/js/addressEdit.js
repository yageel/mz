/*
 * author by:王高飞
 * date:2016-07-15 17:38:54
 */

//new ntScroll("addressEditContent");

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

function submitData() {
    var name = $.trim($("#txtName").val()),
        phone = $.trim($("#txtPhone").val()),
        address = $("#lblAddress").text(),
        detailAddress = $.trim($("#txtAddress").val()),
        isDefault = $("#lblDefault").hasClass("selected")?1:2;

    if (!name) {
        tools.alert("请输入姓名！");
        return;
    }

    if (!phone) {
        tools.alert("请输入手机号！");
        return;
    }

    if (!tools.isMobile(phone)) {
        tools.alert("输入的手机号格式不正确！");
        return;
    }

    if (!address.indexOf("请选择")) {
        tools.alert("请选择省份市区！");
        return;
    }

    if (!detailAddress) {
        tools.alert("请输入详细地址！");
        return;
    }

    tools.sendData("地址编辑页-点击保存地址");

    tools.ajax(tools.url("user", "addressedit"), {
        id: $('#hidID').val(),
        real_name: name,
        phone: phone,
        address: address,
        detail_address: detailAddress,
        default: isDefault
    }, function (ret) {
        tools.alert(ret.data.data, function () {
            history.go(-1);
        });
    });
}

$("#divSelect").bind("animationend,webkitAnimationEnd", function () {
    if ($(this).hasClass("show"))
        $(this).removeClass("show");
    else if ($(this).hasClass("hide"))
        $(this).css("left", "100%").removeClass("hide");
});

initProvince();
provinceScroll.refresh();

tools.sendData("加载地址编辑页", tools.queryPars("id")); 