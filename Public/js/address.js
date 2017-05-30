/*
 * author by:王高飞
 * date:2016-07-15 17:38:54
 */

new ntScroll("addressContent");

function deleteItem(addressID) {
    tools.sendData("地址管理页-点击删除", addressID);

    tools.confirm("本操作不可逆，是否继续？", "删除地址", function (id, type) {
        if (type === "ok") {
            tools.sendData("地址管理页-确定删除", addressID);

            tools.ajax(tools.url("user", "deladdress"), {
                id: addressID
            }, function (ret) {
                $("#liItem" + addressID).remove();

                tools.toast(ret.msg);
            });
        }
    });
}

function selectedItem(tag, id) {
    tools.sendData("地址管理页-点击设置默认地址", id);

    tools.ajax(tools.url("user", "setdefault", {
        id: id
    }), function () {
        $("#addressContent").find("a.active").removeClass("active");
        $(tag).addClass("active");
    }, {
        loading: "正在设置"
    });
}

function gotoLink(tag, name, id) {
    tools.sendData(name, id);

    //tools.loading("链接跳转中");
    location.href = $(tag).attr("data-href");
}

tools.sendData("加载地址管理页");