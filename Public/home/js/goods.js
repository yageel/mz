/*
 * author by:王高飞
 * date:2016-09-01 13:39:25
 */
var mainScroll;
$(function () {
    mainScroll = new ntScroll("lotteryContent");
    $('#getmore').click(function(){
        tools.ajax(tools.url("goods",$('#hidType').val()+"_api"),{
            pageIndex:(parseInt($('#getmore').attr('data-page'))+1),
            goods_class: goods_class
        },function(data){
            $('#getmore').attr('data-page',data.data.page);

            $('#main-list').append(data.data.html);
            mainScroll.refresh();

            if(data.data.page>=data.data.totalpage){
                $('#pageblock').remove();
            }

        });
    });
    tools.sendData("加载兑换记录页");
});

function gotoLink(tag, title, id) {

    tools.sendData(title, id);

    location.href = $(tag).attr("data-href");
}
