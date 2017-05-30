/**
 * Created by assassin on 2017/4/17.
 */
//赴约
function appointment(tag){
    var currentVal = parseInt($("#current").text()),
        countVal = parseInt($("#count").text());
    if(currentVal < countVal){
        currentVal++;
        $("#current").text(currentVal);
        if(currentVal === countVal){
            $(tag).attr("src","/Public/images/meinv/appointment-checked.png");
            $(".num").addClass("c3");
        }
    }
}