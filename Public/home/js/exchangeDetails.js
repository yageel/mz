/*
 * author by:王高飞
 * date:2016-06-12 11:04:43
 */

$(function(){
    var pageIndex = 1,
        pageCount = $("#exchangeDetailsItems").attr("data-page"),
        pageSize = 15;

    pageCount = tools.isNumber(pageCount) ? parseInt(pageCount) : 0;

    new ntScroll("exchangeDetailsContent");

    window.detailsScroll = new ntScroll("exchangeDetailsItems",{
        pullRefresh:true,
        pullMore:true,
        pullHandler:function(info){
            if(info.type === "more")
                pageIndex++;
            else
                pageIndex = 1;


            tools.ajax(tools.url("user",url_api),{
                p:pageIndex,
                pageSize:pageSize
            },function(data){
                detailsScroll[info.type === "more"?"setPullMoreResult":"setPullRefreshResult"](true);
                PageCount = data.data.total_pages;

                var exchangeDetailsItems = $("#exchangeDetailsItems"),
                    fragment = document.createDocumentFragment();

                if(info.type === "refresh")
                    exchangeDetailsItems.find("div.dividing-line").remove();

                $.each(data.data.list,function(){
                    var Div = document.createElement("div");
                    Div.className = "dividing-line";

                    with(this){
                        var detailHtml='';
                        if(goods.goods_type ==1){
                            detailHtml="<span class='color-remarks'>查看详情</span>";
                        }

                        var html = '<a href="'+jump_url+'" class="wrap color-link"><span class="goods-pre"><img src="'+goods.goods_pic+'"></span><span class="wrap-content"><i class="text-overhide">'+ goods.goods_name +'</i><i class="text-overhide">' + create_date +'</i><i class="text-overhide">'+status+'</i></span>'+detailHtml+'</a>';
                        //console.log(html);

                        Div.innerHTML = html;
                    }

                    fragment.appendChild(Div);
                });

                exchangeDetailsItems.find("div.pull-down").before(fragment);

                detailsScroll.refresh();
                detailsScroll.haveMore(pageIndex < pageCount);

                if(info.type === "refresh"){
                    $.delay(function(){
                        detailsScroll.setPullRefreshState(false);
                    },800);
                }else {
                    detailsScroll.setPullMoreState(false, false);
                }
            },  {
                    loading:false,
                        errorCallback:function(){
                        pageCount = 0;

                        detailsScroll[info.type === "more"?"setPullMoreResult":"setPullRefreshResult"](false);

                        $.delay(function(){
                            detailsScroll[info.type === "more"?"setPullMoreState":"setPullRefreshState"](false);
                            detailsScroll.haveMore(pageIndex < pageCount);
                        },800);
                    },
                    type:"get"
                });
        }
    });
    if(pageCount < 2)
        detailsScroll.haveMore(false);

    tools.sendData("加载兑换记录页");
});

function gotoLink(tag,id) {
    var text = $(tag).find("span.wrap-content").text();
    if (!text)
        text = $(tag).text();

    text = $.trim(text);
if(id === 1){
    tools.sendData("个人中心页-中奖记录详情页-点击" + text);
}else{
    tools.sendData("个人中心页-活动记录页-点击" + text);
}


    location.href = $(tag).attr("data-href");
}