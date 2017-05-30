/*
 * author by:王高飞
 * date:2016-06-20 20:28:31
 */

$(function () {
    new ntScroll("contentText");
    window.inputItemsScroll = new ntScroll("inputItems");
});

(function () {
    var inputState = true;
    window.changeInputState = function () {
        var btn = document.getElementById("btnChangeInputState"),
            contentInputs = document.getElementById("contentInputs");

        btn.style.transform = btn.style.webkitTransform = "rotate(" + (inputState ? "180" : "0") + "deg) translate3d(0,0,0)";
        contentInputs.style.transform = contentInputs.style.webkitTransform = "translate3d(0," + (inputState ? "100%" : "0") + ",0)";

        inputState = !inputState;
    }
})();

/*
 * 文本框获取焦点时。
 */
function textboxFocus(tag) {
    inputItemsScroll.scrollYTo(-$(tag).parent().parent().index() * 45);
}

/*
 * 提交留资信息。
 */
function submitData() {
    if ($("#btnSubmit").hasClass("button-disable"))
        return;

    var formData = {},
        allowSubmit = true;

    $("#inputItems").find("input[type='text']").each(function () {
        var $me = $(this),
            isMust = $me.attr("data-must") === "1",
            name = $me.attr("name"),
            val = $.trim($me.val());

        if (isMust && !val) {
            tools.alert($me.parent().prev().prev().text() + "项不能为空！");
            return allowSubmit = false;
        }

        formData[name] = val;
    });

    if (allowSubmit) {
        var adID = $("#hidAdID").val(),
            adMetaID = $("#hidAdMetaID").val();

        tools.sendData("留资页-点击提交", adID);

        tools.ajax(tools.url("index", "addataform", {
            adid: adID,
            admetaid: adMetaID
        }), formData, function () {
            tools.alert("提交成功！", function () {
                $("#btnSubmit").text("信息已提交").addClass("button-disable");
                $("#inputItems").find("input").attr("disabled", "disabled");
                changeInputState();
            });

            var disable = tools.session("getInfoDisable"),
                id = tools.session("getInfoID"),
                stepIdx = tools.session("getInfoStepIdx"),
                text = tools.session("getInfoText");

            if (disable !== undefined && id !== undefined && tools.isNumber(stepIdx) && text !== undefined) {
                stepIdx = parseInt(stepIdx) + 1;
                tools.session("otherItemBtn" + id, escape(text) + "&" + disable + "&" + stepIdx);
            }
        });
    }
}

tools.sendData("加载留资页", tools.queryPars("adid"));