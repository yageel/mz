var fixedHeight, req = window.requestAnimationFrame || window.webkitRequestAnimationFrame, cancelReq = window.cancelAnimationFrame || window.webkitCancelAnimationFrame;

$(function () {
    fixedHeight = window.innerHeight;
});

/**
 * Created by assassin on 2017/3/23.
 */
function reqCallback() {
    if (window.innerHeight == fixedHeight){
        document.documentElement.scrollTop = document.body.scrollTop = window.screenY = 0;

        if (document.activeElement)
            document.activeElement.blur();
    } else {
        req(reqCallback);
    }
}


$("input").bind("focus", function () {
    if (!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/))
        setTimeout(function () { req(reqCallback); }, 600);
}).bind("blur", function () {
    reqCallback();
});


$(function () {
    new ntScroll("joinContent");
});

function change(){
    if($("#yes").attr("checked")){
        $("#no").attr("checked",false);
        $("#unlawful_sel").val(1);
    };
}
function changeNo(){
    $("#yes").attr("checked",false);
    $("#unlawful_sel").val(0);
}

//判断元素是否在数组中
function contains(arr, obj) {
    var i = arr.length;
    while (i--) {
        if (arr[i] === obj) {
            return true;
        }
    }
    return false;
};

// function check(){
//     if(!/^[\u4e00-\u9fa5]+$/gi.test(document.getElementById("uname").value))
//         alert("只能输入汉字");
//     else
//         alert("提交成功");
// }

//上传头像
function chooseImage1() {
    //不仅阻止了事件往上冒泡，而且阻止了事件本身
    wx.chooseImage({
        count: 1, // 默认9
        success: function (res) {
            wx.uploadImage({
                localId: res.localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                isShowProgressTips: 1, // 默认为1，显示进度提示
                success: function (res2) {
                    $('#service_id').val('');
                    // $('#preview').attr('src',res.localIds[0]);
                    // serverImageID = res.serverId; // 返回图片的服务器端ID
                    tools.ajax(tools.url("xqb", "upload_img"),
                        {
                            imageid:res2.serverId
                        },function (ret) {
                            tools.closeLoading();
                            if(ret.state ==10){
                                $('#service_id').val(ret.data);
                                $('#preview').attr('src',"__PUBLIC__/../"+ret.data);
                                var singupList = tools.session("singuplist") || "[]";
                                singupList = JSON.parse(singupList);
                                singupList.push({ img: "__PUBLIC__/../"+ret.data,service_id: ret.data});
                                tools.session("singupList", JSON.stringify(singupList));
                            }else{
                                tools.alert(ret.msg);
                            }

                        });
                    // $('#service_id').val(res2.serverId);
                }
            });

        }
    });
    return false;
};
//根据出生日期判断分组 调用groupBytime('2006-10-1')即可得到分组类别名称
// $("#bir").bind("propertychange",function(){
//     var bir = $.trim($("#bir").val());
//     tools.alert("1")
//     if(bir.length != 10){
//         console.log(bir.length);
//         tools.alert("请输入类似于1993-12-25格式的出生日期！");
//     }
// })
$('#bir').bind('keyup', function() {
    var bir = $.trim($("#bir").val()),
        arr = bir.split("-"),
        arr1 = arr[0],
        arr2 = arr[1],
        arr3 = arr[2];

    if(bir.length == 10){
        if(arr1.length != 4 || arr2.length != 2 || arr3.length != 2){
            tools.alert("请输入类似于1993-12-25格式的出生日期！");
            $("#bir").val("");
            return false;
        }
        groupBytime(bir);
    }
});
    // arr = bir.split("-");

        //     arr = bir.split("-"),
        //     arr1 = arr[0],
        //     arr2 = arr[1],
        //     arr3 = arr[2];
        // groupBytime(arr);
//上传作品
$("#upload").click(function(){
    var nameVal = $.trim($("#name").val()),
        sex = $.trim($("#sex").val()),
        nation = $.trim($("#nation").val()),
        career = $.trim($("#career").val()),
        tel = $.trim($("#tel").val()),
        bir = $.trim($("#bir").val()),
        card = $.trim($("#card").val()),
        school = $.trim($("#school").val()),
        address = $.trim($("#address").val()),
        applicant = $.trim($("#applicant").val());
    var sexArr = ["男","女","男孩","女孩"];
    var unlawful=$("#unlawful_sel").val();
    var service_id = $.trim($('#service_id').val());//头像
    //判断出生日期写法为1993-12-25格式;(分别判断 1993/25/25 三个长度)
    var arr = bir.split("-"),
        arr1 = arr[0],
        arr2 = arr[1],
        arr3 = arr[2];

    if(!nameVal){
        tools.alert("请填写姓名!");
        return false;
    };

    if(!/^[\u4e00-\u9fa5]+$/gi.test(nameVal)){
        tools.alert("姓名只能输入汉字!");
        return false;
    };

    if(nameVal.length > 6){
        tools.alert("请输入符合规则的姓名!");
        return false;
    };

    if(!sex){
        tools.alert("请填写性别!");
        return false;
    };

    if(sex.length > 2){
        tools.alert("请输入符合要求的性别!");
        return false;
    };

    if(!contains(sexArr,sex)){
        tools.alert("请输入符合要求的性别!");
        return false;
    };

    if(!nation){
        tools.alert("请填写民族!");
        return false;
    };

    if(nation.length > 10){
        tools.alert("请填写符合规范的民族!");
        return false;
    }

    if(!career){
        tools.alert("请填写职业!");
        return false;
    };

    if(!/^[\u4e00-\u9fa5]+$/gi.test(career)){
        tools.alert("请用中文描述您的职业!");
        return false;
    };

    if (!tel) {
        tools.alert("请输入手机号！");
        return false;
    };

    // if (!tools.isMobile(tel)) {
    //     tools.alert("输入的手机号码格式不正确！");
    //     return false;
    // };

    if(!bir){
        tools.alert("请输入出生日期！");
        return false;
    };

    if(bir.length > 10 || bir.length < 10){
        tools.alert("请输入类似于1993-12-25格式的出生日期！");
        return false;
    };

    if(arr1.length != 4 || arr2.length != 2 || arr3.length != 2){
        tools.alert("请输入类似于1993-12-25格式的出生日期！");
        return false;
    };

    //出生日期在2011年12月31日之后的禁止参赛
    if(arr1 > 2011){
        tools.alert("本次大赛参赛对象年龄为6岁以上70岁以下！");
        return false;
    }

    if(!card){
        tools.alert("请输入您的身份证号！")
        return false;
    };
    // if(card.length < 15 || card.length > 18){
    //     tools.alert("请输入符合规则的身份证号！");
    //     return false;
    // }

    if(!school){
        tools.alert("请填写工作单位或学校!");
        return false;
    };

    if(school.length > 30){
        tools.alert("您输入的工作单位或学校过长 !");
        return false;
    };

    if(!address){
        tools.alert("请填写报名所在地!");
        return false;
    };
    if(address.length > 50){
        tools.alert("您输入的报名所在地过长 !");
        return false;
    };

    if(!applicant){
        tools.alert("请填写报名人或监护人!");
        return false;
    };

    if(!/^[\u4e00-\u9fa5]+$/gi.test(applicant)){
        tools.alert("只能输入汉字!");
        return false;
    };
    if (service_id.length < 1) {
        tools.alert('请选择上传头像~');
        return;
    }
    //保存基本资料

    // var thesrt="name:"+nameVal+"|tel:"+tel+"|nation:"+nation+"|job:"+career+"|work_unit:"+school+"|address:"+address+"|birthday:"+bir+"|guardian:"+applicant+"|idcard:"+card+"|sex:"+sex+"|unlawful:"+unlawful+"|imageid:"+service_id;
    // console.log(thesrt);return;
    tools.ajax(tools.url("xqb", "join_info_post"),
        {
            name:nameVal,
            tel:tel,
            nation:nation,
            job:career,
            work_unit:school,
            address:address,
            birthday:bir,
            guardian:applicant,
            idcard:card,
            sex:sex,
            unlawful:unlawful,
            imageid:service_id
        },function (ret) {
            tools.closeLoading();
            if(ret.state ==10){
                location.href =tools.url("xqb", "voice_upload");
            }else{
                tools.alert(ret.msg);
            }
            return;
        });
})

//活动规则
$("#rules").click(function(){
    setStepY(-pageTops[2],container.doms[0]);
});

//返回
$("#back").click(function(){
    setStepY(-pageTops[1],container.doms[0]);
});

//根据出生日期判断分组  $string1格式 var stringTime = "2014-07-10";
function groupBytime(stringTime) {
    var birtime=date_to_time(stringTime);//生日时间撮
    var time1=date_to_time("2001-01-01");
    var time2=date_to_time("2004-12-31");
    var time3=date_to_time("2005-01-01");
    var time4=date_to_time("2007-12-31");
    var time5=date_to_time("2008-01-01");
    var time6=date_to_time("2011-12-31");
    var groupVal = $("#group");

    var usertype='';
    if(birtime <time1){
        usertype='成人组';//成人
        groupVal.text(usertype);
    }else{
        //2001-1-1 - 2004-12-31  青年A组
        //2005-1-1 - 2007-12-31  B组
        //2008-1-1 - 2011-12-31  C组
        if(birtime >=time1 && birtime <=time2){
            usertype='青少年A组';
            groupVal.text(usertype);
        }else if(birtime >=time3 && birtime <=time4){
            usertype='青少年B组';
            groupVal.text(usertype);
        }else if(birtime >=time5 && birtime <=time6){
            usertype='青少年C组';
            groupVal.text(usertype);
        }
    }
    return usertype;
}
//日期转时间撮
function date_to_time(string1) {
    var timestamp1 = Date.parse(new Date(string1));
    timestamp1 = timestamp1 / 1000;
    return timestamp1;
}

//页面session
var singupList2 = tools.session("singupList") || "[]";
singupList2 = JSON.parse(singupList2);
//上传头像
if(singupList2.length >0){
    $('#preview').attr('src',singupList2[0].img);
    $('#service_id').val(singupList2[0].service_id);
}
