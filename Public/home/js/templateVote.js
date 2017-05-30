/*
 * author by:王高飞
 * date:2016-07-04 14:21:10
 */

function selectedMe(tag) {
    var inputDom = $(tag).find("input");

    if (!inputDom.attr("disabled"))
        inputDom.attr("checked", !inputDom.attr("checked"));
}

function submitVote(tag) {
    if ($(tag).hasClass("button-disable"))
        return;

    var voteType, data = {};

    $("#templateVoteContent").find("input").each(function (i) {
        var $me = $(this);

        voteType = voteType || $me.attr("type");

        if ($me.attr("checked"))
            data[$me.attr("name")] = $me.val();
    });

    if ($.isEmpty(data)) {
        tools.alert(voteType === "radio" ? "请选择一个您认为对的选项！" : "请至少选择一个您认为对的选项！");
        return;
    }

    var adID = $("#hidAdID").val(),
        adMetaID = $("#hidAdMetaID").val();

    tools.sendData("投票页-点击提交", adID);

    tools.ajax(tools.url("index", "addataformvote", {
        adid: adID,
        admetaid: adMetaID
    }), data, function (data) {
        tools.alert("提交成功！");

        var disable = tools.session("voteDisable"),
            id = tools.session("voteID"),
            stepIdx = tools.session("voteStepIdx"),
            text = tools.session("voteText");

        if (disable !== undefined && id !== undefined && tools.isNumber(stepIdx) && text !== undefined) {
            stepIdx = parseInt(stepIdx) + 1;
            tools.session("otherItemBtn" + id, escape(text) + "&" + disable + "&" + stepIdx);
        }

        $(tag).text("您已提交").addClass("button-disable");
    });
}

$(function () {
    new ntScroll("templateVoteContent");

    $("#frameVideo").attr("src", $("#frameVideo").attr("data-src"));
});

tools.sendData("加载投票页", tools.queryPars("adid"));