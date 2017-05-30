(function () {
    wx.config({
        appId: $("#hidAppid").val(),
        timestamp: $("#hidTimestamp").val(),
        nonceStr: $("#hidNoncestr").val(),
        signature: $("#hidSignature").val(),
        jsApiList: [
            /*
             * 所有要调用的 API 都要加到这个列表中
             * 这里以图像接口为例
             */
            "chooseImage",
            "uploadImage",
            'openLocation',
            'getLocation',
            'addCard',
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'onMenuShareQQ',
            'onMenuShareWeibo',
            'onMenuShareQZone'

        ]
    });

    wx.ready(function () {
        $.tools.configShare({
            title: $("#hidShareTitle").val(),
            desc: $("#hidShareDesc").val(),
            link: $("#hidShareLink").val(),
            imgUrl: $("#hidShareImage").val(),
            type: 'link',
            success: function () {

            }
        });
    });

    function _queryPars(name) {
        var result = new RegExp("\\b" + name + "[\\\\\\/]([^\\\\\\/]+?)([\\\\\\/?&]|\\.html|\\b)").exec(location.href);

        if (result)
            return result[1];

        result = new RegExp("\\b" + name + "=([^&]*)\\b").exec(location.search);

        if (result)
            return result[1];

        return null;
    }

    var isWebkitTransitionEnd = (function () {
        return !("transitionEnd" in document.createElement("div").style);
    })(), isShowLeftNav = false, cityID, fromType;

    $.tools = {
        merges: function () {
            if (arguments.length) {
                if (arguments.length == 1)
                    return arguments[0];

                var result = arguments[0];

                for (var i = 1; i < arguments.length; i++) {
                    if (arguments[i].length != 0) {
                        result = $.merge(result, arguments[i]);
                    } else {
                        result = $.merge(result, [{}]);
                    }
                }

                return result;
            }
        },
        configShare: function (config) {
            wx.onMenuShareAppMessage(config);
            wx.onMenuShareTimeline(config);
            wx.onMenuShareQQ(config);
            wx.onMenuShareWeibo(config);
            wx.onMenuShareQZone(config);
        },

        query: function () {
            var result = null;

            for (var i = 0; i < arguments.length; i++) {
                if (result)
                    result = $.tools.merges(result, $(arguments[i]));
                else
                    result = $(arguments[i]);
            }

            return result;
        },
        queryString: function (name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"),
                r = window.location.search.substr(1).match(reg);

            if (r != null)
                return decodeURIComponent(r[2]);

            return null;
        },
        alert: function (content, callback, title) {
            var btnText, closeTime, pars;

            if ($.type(callback) === "string") {
                var temp = callback;
                callback = title;
                title = temp;
            }

            if ($.type(callback) == "object") {
                pars = callback;
                title = title || callback.title;
                callback = pars.callback;
            } else if ($.type(title) == "object") {
                pars = title;
                title = callback.title;
                callback = callback || pars.callback;
            }

            if (pars) {
                btnText = pars.btnText;
                closeTime = pars.closeTime;
            }

            btnText = btnText || "确定";
            closeTime = closeTime || 0;

            layer.open({
                title: title,
                content: (content || "undefined").replace(/\n/ig, "<br/>"),
                btn: [btnText],
                time: closeTime,
                yes: function (index, layero) {
                    var isClose = true;

                    if ($.isFunction(callback))
                        isClose = callback.call(this, index, layero);

                    if (isClose !== false)
                        layer.close(index);
                },
                closeBtn: false
            });
        },
        confirm: function (content, callback, cancelCallback, title) {
            var okBtnText, cancelBtnText, pars;

            if ($.type(callback) == "string") {
                var temp = callback;
                callback = cancelCallback;
                cancelCallback = title;
                title = temp;
            }

            if ($.type(cancelCallback) == "string") {
                var temp = cancelCallback;
                cancelCallback = title;
                title = temp;
            }

            if ($.type(callback) == "object")
                pars = callback;

            if (pars) {
                okBtnText = pars.okBtnText;
                cancelBtnText = pars.cancelBtnText;
                title = pars.title;
            }

            okBtnText = okBtnText || "确定";
            cancelBtnText = cancelBtnText || "取消";

            layer.open({
                title: title,
                content: (content || "undefined").replace(/\n/ig, "<br/>"),
                btn: [okBtnText, cancelBtnText],
                yes: function (index, layero, e) {
                    var isClose = true;

                    if ($.isFunction(callback))
                        isClose = callback.call(this, index, layero);

                    if (isClose !== false)
                        layer.close(index);
                },
                no: function (index, layero) {
                    var isClose = true;

                    if ($.isFunction(cancelCallback))
                        isClose = cancelCallback.call(this, index, layero);

                    if (isClose !== false)
                        layer.close(index);
                },
                closeBtn: false
            });
        },
        loading: function (text) {
            var loadingDom = $("#divLoadingMain");

            text = text || "数据加载中";

            if (!loadingDom.length)
                $("<div/>").addClass("loading").prop("id", "divLoadingMain").html("<i></i><span>" + text + "</span>").appendTo("body");
            else
                loadingDom.show().children("span").text(text);
        },
        closeLoading: function () {
            $("#divLoadingMain").hide();
        },
        ajax: function (url, data, success, error, loading) {
            var type = "post";

            if ($.isFunction(data)) {
                loading = error;
                error = success;
                success = data;
            }

            if ($.type(success) === "object") {
                error = success.error;
                loading = success.loading;
                type = success.type;
                success = success.success;
            }

            if (loading !== false)
                $.tools.loading(loading);

            $.ajax({
                url: url,
                data: data,
                success: function (data) {
                    try {
                        var jsonData = eval("(" + data + ")"),
                            isContinue = true;

                        if ($.isFunction(success))
                            isContinue = success(jsonData) !== false;

                        if (isContinue) {
                            switch (jsonData.state) {
                                case -2:
                                    $.tools.alert(jsonData.msg, function () {
                                        location.href = $.tools.url("user", "login");
                                    });
                                    break;
                                case -1:
                                    location.href = $.tools.url("user", "login");
                                    break;
                                case 0:
                                    $.tools.alert("服务器处理出错，请稍后再试！");
                                    break;
                                case 2:
                                    $.tools.alert(jsonData.msg);
                                    break;
                                case 3:
                                    location.href = jsonData.data;
                                    break;
                                case 4:
                                    $.tools.alert(jsonData.msg, function () {
                                        location.href = jsonData.data;
                                    });
                                    break;
                                case 5:
                                    location.reload(true);
                                    break;
                                case 6:
                                    location.href = document.referrer ? document.referrer : $.tools.url("index", "index");;
                                    break;
                                case 7:
                                    $.tools.alert(jsonData.msg, function () {
                                        location.reload(true);
                                    });
                                    break;
                            }
                        }
                    } catch (e) {
                        if ($.isFunction(error))
                            error("服务器返回数据不正确，请稍后再试！");
                        else
                            $.tools.alert("服务器返回数据不正确，请稍后再试！");
                    }

                    $.tools.closeLoading();
                },
                error: function () {
                    $.tools.closeLoading();

                    if ($.isFunction(error))
                        error("数据请求出错，请稍后再试！");
                    else
                        $.tools.alert("数据请求出错，请稍后再试！");
                },
                dataType: "text",
                type: "post"
            });
        },
        url: function (controller, action, pars) {
            if ($.type(action) === "object") {
                pars = action;
                action = "index";
            }

            action = action || "index";
            pars = pars || {};
            pars.type = pars.type || "route";

            var url = "/index.php?s=/" + controller + "/" + action + "/type/" + $.tools.getCityID() + "/from/" + $.tools.getFromType(),
                urlPars = "";

            if (pars.type === "general") {
                delete pars.type;

                url += ".html";

                $.each(pars, function (key) {
                    urlPars += "&" + key + "=" + encodeURIComponent(this.toString());
                });

                if (urlPars)
                    url += urlPars;
            } else {
                delete pars.type;

                $.each(pars, function (key) {
                    urlPars += "/" + key + "/" + encodeURIComponent(this.toString());
                });

                url += urlPars + ".html";
            }

            return url;
        },
        getCityID: function () {
            if (cityID === undefined) {
                cityID = _queryPars("type");

                if (cityID !== null)
                    cityID = parseInt(cityID);
            }

            return cityID || 1;
        },
        getFromType: function () {
            if (fromType === undefined) {

                fromType = _queryPars("from");

                if (fromType !== null)
                    fromType = parseInt(fromType);
            }

            return fromType || 1;
        },
    };

    $.fn.css3 = function (name, val) {
        var me = this;

        if ($.type(name) === "object") {
            $.each(name, function (key) {
                me.css3(key, name[key]);
            });
        } else {
            me.css(name, val).css("webkit" + name[0].toLocaleUpperCase() + name.substr(1), val);
        }
    };

    $.fn.transitionEnd = function (handle) {
        if (isWebkitTransitionEnd)
            this.bind("transitionend", handle);
        else
            this.bind("webkitTransitionEnd", handle);
    };


    $("#divShowNav").click(function () {
        if (!isShowLeftNav) {
            $.merge($("#divRightContent"), $("#divLeftNav")).css3("transform", "translate3d(230px, 0, 0)");
            $("#divContentMark").css({
                zIndex: 10,
                background: "rgba(0,0,0,.2)"
            });
            $("#divBackNav").css({
                zIndex: 11,
                opacity: 1
            });
            $("#divShowNav").css("opacity", 0);
        }
    });

    $.merge($("#divContentMark"), $("#divBackNav")).click(function () {
        $.merge($("#divRightContent"), $("#divLeftNav")).css3("transform", "translate3d(0, 0, 0)");
        $("#divContentMark").css("background", "rgba(0,0,0,0)");
        $("#divBackNav").css("opacity", 0);
        $("#divShowNav").css("opacity", 1);
    }).transitionEnd(function (e) {
        if (e.target.id === "divContentMark") {
            if (isShowLeftNav) {
                $(this).css("zIndex", -1);
                $("#divBackNav").css("zIndex", -1);
            }

            isShowLeftNav = !isShowLeftNav;
        }
    });

    $(window).bind("touchstart", function () { });
})();