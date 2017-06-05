/*
 * author by:王高飞
 * date:2016-06-07 13:06:20
 */

/*
 * 自写轻量级仿jQuery前端框架。
 * $(function(){});此写法即为绑定load事件。
 * 其余实现了基本的功能，如addClass，removeClass等。
 * 基本用法和jQuery差不多，像parent、children、next等函数会有细微差别。
 * 需要注意的是：animate函数和jQuery的animate函数区别很大，本框架基于css3动画，jQuery基于js动画。
 * 其次本框架无ajax函数，需要ajax函数的请用tools.ajax。
 */
(function () {
    window.$ = function $() {
        var doms = [];
        for (var i = 0; i < arguments.length; i++) {
            if ($.getType(arguments[i]) === "String") {
                if (arguments[i].indexOf("#") === 0) {
                    var dom = document.getElementById(arguments[i].replace("#", ""));

                    if (dom)
                        doms.push(dom);
                } else {
                    $.each(document.querySelectorAll(arguments[i]), function () {
                        doms.push(this);
                    });
                }
            } else if ($.isFunction(arguments[i])) {
                $.bindEvent(window, "load", arguments[i]);
            } else if ($.isDom(arguments[i])) {
                doms.push(arguments[i]);
            }
        }

        return new domClass(doms);
    };

    function domClass(aryDom) {
        if (!aryDom || !aryDom.length)
            aryDom = [];

        var me = this;

        me.each = function (callback) {
            $.each(aryDom, callback);
            return me;
        };

        me.hasClass = function (clsName) {
            if (aryDom.length) {
                if (aryDom[0].classList)
                    return aryDom[0].classList.contains(clsName);
                else if (dom.className)
                    return new RegExp('(\\s|^)' + clsName + '(\\s|$)').test(dom.className);
            }
            return false;
        };

        me.addClass = function (clsName) {
            return me.each(function () {
                var _dom = this;

                if (!$(_dom).hasClass(clsName)) {
                    if (_dom.classList)
                        _dom.classList.add(clsName);
                    else if (_dom.className)
                        _dom.className += " " + clsName;
                    else if (_dom.className !== undefined)
                        _dom.className = clsName;
                }
            });
        };

        me.removeClass = function (clsName) {
            return me.each(function () {
                var _dom = this;

                if (_dom.classList)
                    _dom.classList.remove(clsName);
                else if (_dom.className)
                    _dom.className = _dom.className.replace(new RegExp('(\\s|^)' + clsName + '(\\s|$)'), '');
            });
        };

        me.children = function (isAll) {
            var doms = [];
            me.each(function (i) {
                if (!i || isAll) {
                    var _dom = this;
                    if (_dom) {
                        $.each(_dom.children, function () {
                            doms.push(this);
                        });
                    }

                    return isAll ? true : false;
                }
            });

            return new domClass(doms);
        };

        me.next = function (isLoop, isAll) {
            var doms = [];
            me.each(function (i) {
                if (!i || isAll) {
                    var _dom = this;
                    if ($.isDom(_dom)) {
                        if (_dom.nextElementSibling) {
                            doms.push(_dom.nextElementSibling);
                        } else if (isLoop) {
                            var _firstDom = _dom.parentNode.children[0];
                            if (_dom != _firstDom)
                                doms.push(_firstDom);
                        }
                    }

                    return isAll ? true : false;
                }
            });
            return new domClass(doms);
        };

        me.prev = function (isLoop, isAll) {
            var doms = [];
            me.each(function (i) {
                if (!i || isAll) {
                    var _dom = this;
                    if ($.isDom(_dom)) {
                        if (_dom.previousElementSibling) {
                            doms.push(_dom.previousElementSibling);
                        } else if (isLoop) {
                            var _lastDom = _dom.parentNode.children[_dom.parentNode.children.length - 1];
                            if (_dom != _lastDom)
                                doms.push(_lastDom);
                        }
                    }

                    return isAll ? true : false;
                }
            });
            return new domClass(doms);
        };

        me.siblings = function (isAll) {
            var doms = [];
            me.each(function (i) {
                if (!i || isAll) {
                    var _dom = this;
                    if (_dom) {
                        $(_dom.parentElement).children().each(function () {
                            if (this != _dom)
                                doms.push(this);
                        });
                    }

                    return isAll ? true : false;
                }
            });

            return new domClass(doms);
        };

        me.eq = function (idx) {
            return aryDom.length > idx ? $(aryDom[idx]) : new domClass();
        };

        me.parent = function (isAll) {
            var doms = [];
            me.each(function (i) {
                if (!i || isAll) {
                    var _dom = this;
                    if (_dom && _dom.parentNode)
                        doms.push(_dom.parentNode);

                    return isAll ? true : false;
                }
            });

            return new domClass(doms);
        };

        me.bind = function (type, fun, useCapture) {
            return me.each(function () {
                var _dom = this;
                $.bindEvent(_dom, type, fun, useCapture);
            });
        };

        me.append = function (dom) {
        	if(!dom)
        		return me;
            return me.each(function () {
                var _dom = this;
                if (_dom && $.isFunction(_dom.appendChild)) {
                    if (dom instanceof domClass) {
                        dom.each(function () {
                            _dom.appendChild(this);
                        });
                    } else if(typeof dom === "string"){
                        var tempDom = document.createElement("div");

                        insertDom = document.createDocumentFragment();

                        tempDom.innerHTML = dom;

                        $.each(tempDom.childNodes, function () {
                            insertDom.appendChild(this.cloneNode(true));
                        }, false);

                        _dom.appendChild(insertDom);
                    } else {
                        _dom.appendChild(dom);
                    }
                }
            });
        };

        me.appendTo = function (dom) {
            if (dom === undefined)
                dom = $(document.body);
            else if ($.getType(dom) === "String" || $.isDom(dom))
                dom = $(dom);

            return dom.append(me);
        };

        me.prepend = function (dom, preDom) {
            return me.each(function () {
                var _dom = this;
                if (_dom && $.isFunction(_dom.insertBefore)) {
                    if (preDom instanceof domClass) {
                        preDom.each(function () {
                            _insertBefore(dom, this, _dom);
                        });
                    } else {
                        preDom = preDom ? preDom : _dom.childNodes.length > 0 ? _dom.childNodes[0] : null;

                        _insertBefore(dom, preDom, _dom);
                    }
                }
            });

            function _insertBefore(dom, preDom, parentDom) {
                if (dom instanceof domClass) {
                    dom.each(function () {
                        parentDom.insertBefore(this, preDom);
                    });
                } else {
                    parentDom.insertBefore(dom, preDom);
                }
            }
        };

        me.before = function (dom) {
            return me.each(function () {
                var _dom = this;
                if (_dom && _dom.parentElement)
                    $(_dom.parentElement).prepend(dom, _dom);
            });
        };

        me.after = function (dom) {
            return me.each(function () {
                var _dom = this;
                if (_dom && _dom.parentElement) {
                    var nextDoms = $(_dom).next().doms;

                    if (nextDoms.length)
                        $(_dom.parentElement).prepend(dom, nextDoms[0]);
                    else
                        $(_dom.parentElement).append(dom);
                }
            });
        };

        me.val = function (val) {
            if (val === undefined) {
                if (aryDom.length && aryDom[0])
                    return aryDom[0].value;

                return undefined;
            }

            return me.each(function () {
                var _dom = this;

                _dom.value = val;
            });
        };

        me.prependTo = function (dom) {
            if (dom === undefined)
                dom = $(document.body);
            else if ($.getType(dom) === "String" || $.isDom(dom))
                dom = $(dom);

            return dom.prepend(me);
        };

        me.attr = function (name, val) {
            name = name === "class" ? "className" : name;

            if (val === undefined) {
                if (aryDom.length && aryDom[0]) {
                    if (aryDom[0][name] !== undefined)
                        return aryDom[0][name];

                    if ($.isFunction(aryDom[0].getAttribute))
                        return aryDom[0].getAttribute(name);
                }

                return undefined;
            }

            return me.each(function () {
                var _dom = this;

                if (_dom && _dom[name] !== undefined)
                    _dom[name] = val;
                else if (_dom && $.isFunction(_dom.setAttribute))
                    _dom.setAttribute(name, val);
            });
        };

        me.removeAttr = function (name) {
            return me.each(function () {
                var _dom = this;
                if ($.isFunction(_dom.removeAttribute))
                    _dom.removeAttribute(name);
                else if (_dom)
                    _dom[name] = undefined;
            });
        };

        me.html = function (html) {
            if (html === undefined) {
                if (aryDom.length && aryDom[0])
                    return aryDom[0].innerHTML;

                return undefined;
            }

            return me.each(function () {
                var _dom = this;

                if (_dom)
                    _dom.innerHTML = html;
            });
        };

        me.text = function (text) {
            if (text === undefined) {
                if (aryDom.length && aryDom[0])
                    return aryDom[0].innerText;

                return undefined;
            }

            return me.each(function () {
                var _dom = this;

                if (_dom)
                    _dom.innerText = text;
            });
        };

        me.find = function (selector) {
            var doms = [];
            me.each(function () {
                var _dom = this;

                if (_dom && $.isFunction(_dom.querySelectorAll)) {
                    $.each(_dom.querySelectorAll(selector), function () {
                        doms.push(this);
                    });
                }
            });

            return new domClass(doms);
        };

        me.css = function (name, val) {
            if ($.getType(name) === "String") {
                if (val === undefined) {
                    if (aryDom.length && $.isDom(aryDom[0]))
                        return document.defaultView.getComputedStyle(aryDom[0], null)[name];

                    return undefined;
                }

                return me.each(function () {
                    var _dom = this;

                    if ($.isDom(_dom))
                        _dom.style[name] = val;
                });
            }

            $.each(name, function (key, ary) {
                me.css(key, ary[key]);
            });

            return me;
        };

        me.remove = function () {
            return me.each(function () {
                var _dom = this;
                if (_dom && _dom.parentNode)
                    _dom.parentNode.removeChild(_dom);
            });
        };

        me.show = function (display) {
            return me.css("display", display || "block");
        };

        me.hide = function () {
            return me.css("display", "none");
        };

        me.animate = function (animName, timing, duration) {
            var count = animName.count || 1,
                delay = animName.delay || 0,
                direction = animName.direction || "normal",
                fillMode = animName.fillMode || "both";

            if ($.getType(animName) === "String") {
                if ($.getType(timing) === "Number") {
                    var temp = timing;
                    timing = duration;
                    duration = temp;
                }

                timing = timing || "ease";
                duration = duration || .3;
            } else {
                timing = animName.timing;
                duration = animName.duration;
                animName = animName.name;
            }

            return me.css({
                animationTimingFunction: timing,
                animationDuration: duration + "s",
                animationName: animName,
                animationDelay: delay + "s",
                animationIterationCount: count,
                animationDirection: direction,
                animationFillMode: fillMode,
                webkitAnimationTimingFunction: timing,
                webkitAnimationDuration: duration + "s",
                webkitAnimationName: animName,
                webkitAnimationDelay: delay + "s",
                webkitAnimationIterationCount: count,
                webkitAnimationDirection: direction,
                webkitAnimationFillMode: fillMode
            });
        };

        me.clearAnimate = function () {
            return me.css({
                animation: "none",
                webkitAnimation: "none"
            });
        };

        me.transition = function (property, timing, duration) {
            property = property || "all";

            var delay = property.delay || 0;

            if ($.getType(property) === "String") {
                if ($.getType(timing) === "Number") {
                    var temp = timing;
                    timing = duration;
                    duration = temp;
                }

                timing = timing || "ease";
                duration = duration || .3;
            } else {
                timing = property.timing;
                duration = property.duration;
                property = property.name;
            }

            return me.css({
                transitionTimingFunction: timing,
                transitionDuration: duration + "s",
                transitionProperty: property,
                transitionDelay: delay + "s",
                webkitTransitionTimingFunction: timing,
                webkitTransitionDuration: duration + "s",
                webkitTransitionProperty: property,
                webkitTransitionDelay: delay + "s"
            });
        };

        me.clearTransition = function () {
            return me.css({
                transition: "none",
                webkitTransition: "none"
            });
        };

        me.click = function (fun) {
            if ($.isFunction(fun))
                return me.bind("click", fun);

            return me.each(function () {
                var _dom = this;
                if ($.isDom(_dom)) {
                    var event = document.createEvent('HTMLEvents');
                    event.initEvent("click", true, true);
                    _dom.dispatchEvent(event);
                }
            });
        };

        me.height = function (height) {
            if (height !== undefined) {
                height = height != 0 && !isNaN(height) ? height + "px" : height;
                return me.css("height", height);
            }

            if (me.doms.length && $.isDom(me.doms[0]))
                return me.doms[0].offsetHeight;

            return 0;
        };

        me.width = function (width) {
            if (width !== undefined) {
                width = width != 0 && !isNaN(width) ? width + "px" : width;
                return me.css("width", width);
            }

            if (me.doms.length && $.isDom(me.doms[0]))
                return me.doms[0].offsetWidth;

            return 0;
        };

        me.size = function (width, height) {
            if (width !== undefined && height !== undefined)
                return me.width(width).height(height);

            return { width: me.width(), height: me.height() };
        };

        me.offsetTop = function () {
            if (me.doms.length && $.isDom(me.doms[0]))
                return me.doms[0].offsetTop;

            return 0;
        };

        me.offsetLeft = function () {
            if (me.doms.length && $.isDom(me.doms[0]))
                return me.doms[0].offsetLeft;

            return 0;
        };

        me.offset = function () {
            return { left: me.offsetLeft(), top: me.offsetTop() };
        };

        me.index = function () {
            var meIndex = -1;
            if (me.doms.length && $.isDom(me.doms[0])) {
                $(me.doms[0].parentElement).children().each(function (i) {
                    if (this === me.doms[0]) {
                        meIndex = i;
                        return false;
                    }
                });
            }

            return meIndex;
        }

        me.doms = aryDom;
    }

    $.getType = function (obj) {
        return Object.prototype.toString.call(obj).replace("[object ", "").replace("]", "");
    };

    $.isDom = function (obj) {
        return obj && obj.nodeType === 1;
    };

    $.trim = function (str) {
        if ($.getType(str) === "String")
            return str.replace(/^ +/ig, "").replace(/ +$/ig, "");

        return str;
    };

    $.each = function (ary, callback) {
        if (!$.isEmpty(ary)) {
            if (ary.length) {
                for (var i = 0; i < ary.length; i++) {
                    var result = callback.call(ary[i], i, ary);

                    if (result === false)
                        break;
                }
            } else {
                for (var key in ary) {
                    var result = callback.call(ary[key], key, ary);

                    if (result === false)
                        break;
                }
            }

            return ary;
        }
    };

    $.isEmpty = function (obj) {
        var meType = $.getType(obj);

        if (meType === "String")
            return $.trim(obj) === "";

        if (meType === "Boolean" || meType === "Number")
            return false;

        for (var key in obj) {
            return false;
        }

        return true;
    };

    $.isFunction = function (fun) {
        return $.getType(fun) === "Function";
    };

    $.bindEvent = function (dom, type, fun, useCapture) {
        if (dom && $.isFunction(dom.addEventListener)) {
            $.each(type.split(","), function () {
                dom.addEventListener(this.toString(), fun, useCapture);
            });
        }

        return dom;
    };

    $.remToPx = function (rem) {
        if (rem && !isNaN(rem)) {
            var htmlFontSize = $("html").css("fontSize");

            if (htmlFontSize)
                return parseFloat(htmlFontSize) * rem;

            return rem;
        }

        return 0;
    }

    $.delay = function (callback, timer) {
        return setTimeout(callback, timer || 40);
    };
})();

/*
 * 工具类。
 * tools.loading ：显示 loading 框。
 * tools.closeLoading ：关闭 loading 框。
 * tools.toast ：显示一个提示框，默认是√图标和已完成。
 * tools.dialog ：显示一个弹窗，返回该弹窗的下标，tools.alert和tools.confirm都是基于此方法实现的。
 * tools.closeDialog ：关闭一个弹窗。
 * tools.alert ：弹出一个提醒框。
 * tools.confirm ：弹出一个信息确认框框。
 * tools.ajax ：发送一个ajax请求。
 * tools.isMobile ：验证指定的字符串是否符合手机号码格式。
 * tools.isNumber ：验证指定的字符串是否是数字格式。
 * tools.sendData ：发送事件监听数据。
 * tools.sendServer ：发送事件监听数据（后台统计）。
 * tools.session ：设置浏览器session数据（此数据关闭浏览器即会丢失）。
 * tools.eachSession ：循环所有session项。
 * tools.storage ：设置浏览器localStorage数据。
 * tools.removeStorage ：清除localStorage数据项。
 * tools.preLoad ：预加载一组图片。
 * tools.getCityID ：获取当前链接所代表的公众号ID。
 * tools.getFromType ：获取当前链接的来源类型。
 * tools.date ：时间辅助方法，返回一个自定义时间对象。
 * tools.fillString ：填充字符串。
 * tools.queryPars ：获取查询地址参数。
 * tools.configShare ：重新配置分享参数。
 * tools.wxReady ：微信配置完成后的回调函数。
 * tools.addCard ：添加微信卡券。
 * tools.url ：传入控制器和动作以及参数可以生成对应的后台地址。
 * tools.isBind ：获取当前用户是否绑定了手机。
 */

function recordComplate(res) {
    clearInterval(timer);
    $("#startRecord").removeClass("active");

    currentAudioLocalID = res.localId;
    currentAudioServerID = undefined;

    uploadAudio();
    $("#changeAudioPlay").show();
    $("#recordTime").text(finishTime);
    tools.alert("录音完成！");
}
//结束录音
function stopRecord() {
    if (isRecording) {
        try{
            wx.stopRecord({
                success: recordComplate
            });
        } catch (e) {
            tools.alert(e.message);
        }
        isRecording = false;
    }
}

//上传录音
function uploadAudio() {
    wx.uploadVoice({
        localId: currentAudioLocalID,
        isShowProgressTips: 1,
        success: function (res) {
            currentAudioServerID = res.serverId;
        }
    });
}

(function () {
    var zIndex = 10000,
        toastTimeout,
        idIndex = 1,
        cityID,
        fromType,
        wxReady;
        bool = location.href.indexOf('showtest')>0?true:false;
    wx.config({
        debug: bool,
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
            'onMenuShareQZone',
            'startRecord',
            'stopRecord',
            'onVoiceRecordEnd',
            'playVoice',
            'pauseVoice',
            'onVoicePlayEnd',
            'stopVoice',
            'uploadVoice',
            'downloadVoice'
        ]
    });

    wx.ready(function () {
        wx.onVoiceRecordEnd({
            complete: recordComplate
        });

        wx.onVoicePlayEnd({
            success: function (res) {
                changeAudioPlayState();
            }
        });
        tools.configShare({
            title: $("#hidShareTitle").val(),
            desc: $("#hidShareDesc").val(),
            link: $("#hidShareLink").val(),
            imgUrl: $("#hidShareImage").val(),
            type: 'link',
            success: function () {
                tools.sendData("页面分享成功");
            }
        });

        if ($.isFunction(wxReady))
            wxReady();
    });

    window.tools = {
        loading: function (text) {
            if(text =='链接跳转中' || '页面跳转中' == text){
                return;
            }
            var loadingDom = $("#toast-loading");

            if (!loadingDom.doms.length) {
                loadingDom = $(document.createElement("div"));

                loadingDom.addClass("toast-container");

                loadingDom.attr("id", "toast-loading");

                var html = '<div class="toast"><div class="loading"><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b></div><p id="toast-loading-text">' + (text || "数据加载中") + '</p></div>';

                loadingDom.html(html);

                loadingDom.appendTo();
            } else {
                $("#toast-loading-text").text(text || "数据加载中");
            }

            loadingDom.css({
                zIndex: zIndex++,
                display: "block"
            });
        },
        closeLoading: function () {
            $("#toast-loading").hide();
        },
        toast: function (text, type, callback) {
            var loadingDom = $("#toast-main");

            if (!loadingDom.doms.length) {
                loadingDom = $(document.createElement("div"));

                loadingDom.addClass("toast-container");

                loadingDom.attr("id", "toast-main");

                var html = '<div class="toast"><i id="toast-ico"></i><p id="toast-text"></p></div>';

                loadingDom.html(html);

                loadingDom.appendTo();
            }

            loadingDom.css({
                zIndex: zIndex++,
                display: "block"
            });

            clearTimeout(toastTimeout);

            $("#toast-ico").attr("class", $.isFunction(type) ? "yes" : (type || "yes"));
            $("#toast-text").html(text || "已完成");

            callback = $.isFunction(type) ? type : callback;

            toastTimeout = $.delay(function () {
                loadingDom.css("display", "none");

                if ($.isFunction(callback))
                    callback();
            }, 1000);
        },
        dialog: function (pars) {
            var dialogIdx = idIndex++,
                dialogContainerDom = document.createElement("div"),
                dialogMainDom = document.createElement("div"),
                tempDom, dialogSize;

            pars.dialogClass = pars.dialogClass || "";

            dialogContainerDom.className = "dialog-container";

            dialogContainerDom.style.zIndex = zIndex++;

            dialogContainerDom.id = "dialog" + dialogIdx;

            dialogMainDom.className = "dialog";

            if (pars.dialogClass)
                dialogMainDom.className += " " + pars.dialogClass;

            if (!$.isEmpty(pars.width))
                dialogMainDom.style.width = pars.width;

            dialogContainerDom.appendChild(dialogMainDom);

            if (pars.title) {
                tempDom = document.createElement("div");
                tempDom.className = "dialog-title";
                tempDom.innerText = pars.title;

                dialogMainDom.appendChild(tempDom);
            }

            tempDom = document.createElement("div");
            tempDom.className = "dialog-content";
            $(tempDom).css(pars.contentStyle);
            tempDom.innerHTML = pars.content.replace(/\[meIdx\]/g, dialogIdx);

            dialogMainDom.appendChild(tempDom);

            if (!$.isEmpty(pars.btns)) {
                tempDom = document.createElement("div");
                tempDom.className = "dialog-buttons";

                dialogMainDom.appendChild(tempDom);

                $.each(pars.btns, function () {
                    var buttonConfig = this,
                        buttonDom = document.createElement("a");

                    buttonDom.href = "javascript:void(0)";
                    buttonDom.innerText = buttonConfig.text;
                    buttonDom.className = buttonConfig.clsName === undefined ? "" : buttonConfig.clsName;

                    buttonDom.addEventListener("click", function () {
                        var isClose = true;
                        if ($.isFunction(buttonConfig.click))
                            isClose = buttonConfig.click(dialogIdx) !== false;

                        if (isClose)
                            tools.closeDialog(dialogIdx);
                    });

                    tempDom.appendChild(buttonDom);
                });
            }

            $(dialogContainerDom).appendTo();

            dialogSize = $(dialogMainDom).size();

            $(dialogMainDom).css("margin", -dialogSize.height / 2 + "px 0 0 -" + dialogSize.width / 2 + "px");

            return dialogIdx;
        },
        closeDialog: function (idx) {
            $("#dialog" + idx).addClass("hide").bind("animationend,webkitAnimationEnd", function () {
                $("#dialog" + idx).remove();
            });
        },
        alert: function (text, title, callback) {
            var okText;

            if($.isFunction(title)){
                var temp = callback;
                callback = title;
                title = temp;
            } else if($.getType(title) == "Object"){
                var temp = title;
                title = temp.title;
                callback = callback || temp.callback;
                okText = temp.okText;
            }

            return tools.dialog({
                title: title || "操作提醒",
                content: text,
                dialogClass: "dialog-alert",
                contentStyle: {
                    padding: "5px 20px 0 20px",
                    color: "#888",
                    fontSize: "15px",
                    lineHeight: "150%"
                },
                btns: [{
                    text: okText || "确定",
                    click: callback,
                    clsName: "ok"
                }]
            });
        },
        confirm: function (text, title, callback, pars) {
            var callback = $.isFunction(title) ? title : callback;

            pars = pars || {};

            if ($.getType(title) === "Object")
                pars = title;

            if ($.getType(callback) === "Object")
                pars = callback;

            tools.dialog({
                title: $.isFunction(title) ? "确认操作" : (title || "确认操作"),
                content: text,
                dialogClass: "dialog-confirm",
                contentStyle: {
                    padding: "5px 20px 0 20px",
                    color: "#888",
                    fontSize: "15px",
                    lineHeight: "150%"
                },
                btns: [{
                    text: pars.cancelText || "取消",
                    click: function (idx) {
                        if ($.isFunction(callback))
                            return callback(idx, "cancel");
                    }
                }, {
                    text: pars.okText || "确定",
                    click: function (idx) {
                        if ($.isFunction(callback))
                            return callback(idx, "ok");
                    },
                    clsName: "ok"
                }]
            });
        },
        ajax: function (url, data, callback, pars) {
            if ($.isFunction(data)) {
                pars = callback;
                callback = data;
            }

            pars = pars || {};

            pars.loading = pars.loading === undefined ? "数据加载中" : pars.loading;
            pars.async = pars.async === undefined ? true : !!pars.async;
            pars.type = (pars.type || "post").toLocaleUpperCase();

            var xmlHttpReg = new XMLHttpRequest(),
                dataStr = '',
                meTimeout;

            $.each(data, function (key) {
                if (dataStr != '')
                    dataStr += '&';

                dataStr += key + "=" + encodeURIComponent(this.toString());
            });

            meTimeout = $.delay(function () {
                runError(-1, "您现在的网络不稳定，请重试！");

                meTimeout = undefined;

                dollowUp();
            }, pars.timeout || 10000);

            if (pars.loading)
                tools.loading(pars.loading);

            if (pars.type.toLocaleString() === "GET")
                url = url + (url.indexOf("?") > -1 ? "&" : "?") + dataStr;

            url = url + (url.indexOf("?") > -1 ? "&rd=" : "?rd=") + (new Date()).getTime();

            url = url.replace(/\[cityID\]/g, tools.getCityID()).replace(/\[fromType\]/g, tools.getFromType());

            xmlHttpReg.open(pars.type, url, pars.async);
            xmlHttpReg.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xmlHttpReg.send(pars.type !== "GET" ? dataStr : null);

            xmlHttpReg.onreadystatechange = doResult;

            function doResult() {
                if (meTimeout) {
                    if (xmlHttpReg.readyState == 4) {
                        if (xmlHttpReg.status == 200) {
                            var resultObj;
                            try {
                                resultObj = eval("(" + xmlHttpReg.responseText + ")");
                            } catch (e) {
                                runError(200, "^-^请重试一下吧(200)！^-^", xmlHttpReg.responseText);
                            }
                            if (resultObj) {
                                switch (resultObj.state) {
                                    case -1:
                                        tools.alert(resultObj.msg || "服务器错误，请重试！", resultObj.data || "操作提示");
                                        break;
                                    case 0:
                                        tools.confirm("未登录或登录超时，是否跳转登录！", resultObj.data || "操作提示", function (idx, type) {
                                            if (type == "ok")
                                                location.href = "/user/login.html?type=" + tools.getCityID() + "&gfrom=" + tools.getFromType();
                                        });
                                        break;
                                    case 2:
                                        location.href = resultObj.data;
                                        break;
                                    case 3:
                                        tools.addCard(resultObj.data);
                                        break;
                                    case 4:
                                        tools.alert(resultObj.msg, resultObj.data || "操作提示");
                                        break;
                                    case 5:
                                        tools.alert(resultObj.msg, function () {
                                            location.href = resultObj.data;
                                        });
                                        break;
                                    default:
                                        if ($.isFunction(callback))
                                            callback(resultObj);
                                        break;
                                }
                            }
                        } else {
                            runError(xmlHttpReg.status, "^-^请重试一下吧(500)！^-^ ！！", xmlHttpReg.responseText);
                        }

                        dollowUp();
                    }
                }
            }
            function runError(state, msg, data) {
                if ($.isFunction(pars.errorCallback)) {
                    pars.errorCallback({
                        state: state,
                        data: data,
                        msg: msg
                    });
                } else {
                    tools.alert(msg, "系统提示");
                }
            }
            function dollowUp() {
                if ($.isFunction(pars.dollowUpOver)) {
                    pars.dollowUpOver({});
                }
                if (meTimeout) {
                    clearTimeout(meTimeout);
                    meTimeout = undefined;
                }

                if (pars.loading)
                    tools.closeLoading();
            }
        },
        isMobile: function (text) {
            return /^1[34578][0-9]{9}$/.test(text);
        },
        isNumber: function (text) {
            return text && !isNaN(text);
        },
        sendData: function (name, id) {
            var label = action = name + "->type:" + tools.getCityID();

            if (id)
                label += "->id:" + id;

            _hmt.push(['_trackEvent', name, action, label]);
        },
        sendService: function (url) {
            new Image().src = url;
        },
        session: function (name, val) {
            if (val === undefined)
                return sessionStorage.getItem(name);

            sessionStorage.setItem(name, val);
        },
        eachSession: function (callback) {
            for (var i = 0; i < sessionStorage.length; i++) {
                var key = sessionStorage.key(i);
                callback.call(tools.session(key), key, i);
            }
        },
        storage: function (name, val) {
            if (val === undefined)
                return localStorage.getItem(name);

            localStorage.setItem(name, val);
        },
        removeStorage: function (name) {
            localStorage.removeItem(name);
        },
        preLoad: function (aryPath) {
            $.each(aryPath, function () {
                new Image().src = this;
            });
        },
        getCityID: function () {
            //if (cityID === undefined)
            {
                cityID = _queryPars("type");
                if (cityID)
                    cityID = parseInt(cityID);
            }

            return $.isEmpty(cityID) ? $('input[id="global_type"]').val() : cityID;
        },
        getFromType: function () {
            // if (fromType === undefined)
            {
                fromType = _queryPars("gfrom");
                if (fromType)
                    fromType = parseInt(fromType);
            }

            return $.isEmpty(fromType) ? $('input[id="global_from"]').val() : fromType;
        },
        date: function (date) {
            if (tools.isNumber(date))
                date = parseInt(date);
            else if ($.getType(date) === "String")
                date = date.replace(/-/g, "\/").replace(/\\/g, "\/");

            if (!date)
                date = new Date();
            else if ($.getType(date) !== "Date")
                date = new Date(date);

            return new _dateClass(date);
        },
        fillString: function (str, strLen, fillChar) {
            str += '';

            fillChar = fillChar || "0";
            strLen = strLen || 2;

            for (var i = str.length; i < strLen; i++) {
                str = fillChar + str;
            }

            return str;
        },
        queryPars: function (name) {
            return _queryPars(name);
        },
        configShare: function (config) {
            wx.onMenuShareAppMessage(config);
            wx.onMenuShareTimeline(config);
            wx.onMenuShareQQ(config);
            wx.onMenuShareWeibo(config);
            wx.onMenuShareQZone(config);
        },
        wxReady: function (ready) {
            wxReady = ready;
        },
        addCard: function (cardList, success, cancel, fail) {
            
            wx.addCard({
                cardList: cardList,
                success: success || function (res) {
                    tools.toast("添加成功");
                },
                cancel: cancel || function (res) { },
                fail: fail || function (res) { }
            });
        },
        url: function (controller, action, pars) {
            if ($.getType(action) === "Object") {
                pars = action;
                action = "index";
            }

            action = action || "index";
            pars = pars || {};
            pars.type = pars.type || "route";

            var url = "/index.php?s=/" + controller + "/" + action + "/type/" + tools.getCityID() + "/gfrom/" + tools.getFromType(),
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
        isBind: function () {
            return tools.isNumber($("#lblUserMobile").text());
        }
    };

    function _dateClass(date) {
        var me = this;

        me.getFullYear = function () {
            return date.getFullYear();
        };

        me.getYear = function () {
            return date.getYear();
        };

        me.getDate = function () {
            return date.getDate();
        };

        me.getMonth = function () {
            return date.getMonth();
        };

        me.getMinutes = function () {
            return date.getMinutes();
        };

        me.getHours = function () {
            return date.getHours();
        };

        me.getMilliseconds = function () {
            return date.getMilliseconds();
        };

        me.getSeconds = function () {
            return date.getSeconds();
        };

        me.getTime = function () {
            return date.getTime();
        };

        me.addDay = function (day) {
            return me.addHours(24 * day);
        };

        me.addHours = function (hours) {
            return me.addMinutes(60 * hours);
        };

        me.addMinutes = function (minutes) {
            return me.addSeconds(60 * minutes);
        };

        me.addSeconds = function (seconds) {
            return new _dateClass(new Date(seconds * 1000 + me.getTime()));
        };

        me.format = function (format) {
            var hour = me.getHours(),
                hh = me.getHours() > 12 ? me.getHours() - 12 : me.getHours();

            format = format || "yyyy-MM-dd HH:mm:ss";

            return format.replace(/yyyy/ig, tools.fillString(me.getFullYear(), 4))
                .replace(/yyy/g, tools.fillString(me.getYear(), 3))
                .replace(/MM/g, tools.fillString(me.getMonth() + 1))
                .replace(/dd/g, tools.fillString(me.getDate()))
                .replace(/HH/g, tools.fillString(hour))
                .replace(/hh/g, tools.fillString(hh))
                .replace(/mm/g, tools.fillString(me.getMinutes()))
                .replace(/ss/g, tools.fillString(me.getSeconds()))
                .replace(/fff/g, tools.fillString(me.getMilliseconds(), 3))
        };
    }

    function _queryPars(name) {
        var result = new RegExp("\\b" + name + "[\\\\\\/]([^\\\\\\/]+?)([\\\\\\/?&]|\\.html|\\b)").exec(location.href);

        if (result)
            return result[1];

        result = new RegExp("\\b" + name + "=([^&]*)\\b").exec(location.search);

        if (result)
            return result[1];

        return null;
    }
})();

/*
 * 自写轻量级仿iScroll插件(ntScroll)。
 * 支持下拉刷新和上拉加载更多。
 */
(function () {
    var allCache = {},
        cacheIndex = 1,
        startTime,
        container,
        startPoint,
        cache,
        utils = {
            addEvent: function (dom, type, handler) {
                utils.each(type.split(","), function (type) {
                    dom.addEventListener(type, handler);
                });
            },
            each: function (obj, callback) {
                for (var key in obj) {
                    callback(obj[key]);
                }
            },
            setScrollY: function (dom, y) {
                dom.style.webkitTransform = dom.style.transform = "translate3d(0, " + y + "px, 0)";
            },
            springbackYAnim: function (dom, y, endY, meCache) {
                var speed = (y - endY) / 8;

                _anim();

                function _anim() {
                    if (meCache.touchStart)
                        return;

                    speed -= speed / 9;

                    y -= speed;

                    if (Math.abs(Math.abs(y) - Math.abs(endY)) < 0.8) {
                        y = endY;
                        meCache.animFrameID = undefined;
                    }

                    utils.setScrollY(dom, y);
                    meCache.scrollY = y;

                    if (y != endY)
                        meCache.animFrameID = utils.animFrame(_anim);
                    else if (endY > meCache.minScroll || endY < meCache.maxScroll)
                        utils.springbackYAnim(dom, endY, endY > meCache.minScroll ? meCache.minScroll : meCache.maxScroll, meCache);
                }
            },
            scrollYAnim: function (dom, y, speed, meCache) {
                _anim();

                function _anim() {
                    speed -= speed / 30;

                    y -= speed;

                    var isSpringback = false;

                    if (y > meCache.minScroll || y < meCache.maxScroll) {
                        if (Math.abs(speed) > 1) {
                            var endY = y - speed * 5;

                            if (speed < 0 && endY > meCache.minScroll + 40)
                                endY = meCache.minScroll + 40;
                            else if (speed > 0 && endY < meCache.maxScroll - 40)
                                endY = meCache.maxScroll - 40;

                            utils.springbackYAnim(dom, y, endY, meCache);
                        }

                        isSpringback = true;
                    }

                    utils.setScrollY(dom, y);
                    meCache.scrollY = y;

                    if (!isSpringback && Math.abs(speed) > 0.1)
                        meCache.animFrameID = utils.animFrame(_anim);
                }
            },
            css: function (dom, name) {
                if (window.getComputedStyle) {
                    name = name.replace(/([A-Z])/g, "-$1");
                    name = name.toLowerCase();
                    return document.defaultView.getComputedStyle(dom, null)[name];
                }

                return null;
            },
            init: function (dom, meCacheID, pars) {
                var meCache = allCache[meCacheID];

                if (!meCache) {
                    meCache = allCache[meCacheID] = {};
                    utils.addEvent(dom.parentElement, "touchstart", utils.touchHandler);
                    meCache.pars = pars;

                    if (meCache.pars && meCache.pars.pullRefresh) {
                        var refreshDom = document.createElement("div");
                        refreshDom.className = "pull-up wrap";
                        refreshDom.innerHTML = '<div><div id="' + meCacheID + 'RefreshIcon" class="pull-down-icon"><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><em></em></div><p id="' + meCacheID + 'RefreshText">下拉刷新</p></div>';

                        if (dom.childNodes.length)
                            dom.insertBefore(refreshDom, dom.childNodes[0]);
                        else
                            dom.appendChild(refreshDom);
                    }

                    if (meCache.pars && meCache.pars.pullMore) {
                        meCache.haveMore = true;

                        var moreDom = document.createElement("div");
                        moreDom.id = meCacheID + "More";
                        moreDom.className = "pull-down wrap";
                        moreDom.innerHTML = '<div><div id="' + meCacheID + 'MoreIcon" class="pull-up-icon"><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b><em></em></div><p id="' + meCacheID + 'MoreText">上拉加载更多</p></div>';

                        dom.appendChild(moreDom);
                    }
                }

                meCache.id = meCacheID;
                meCache.containerHeight = dom.parentElement.offsetHeight - parseInt(utils.css(dom.parentElement, "paddingTop") || 0) - parseInt(utils.css(dom.parentElement, "paddingBottom") || 0);
                meCache.height = dom.offsetHeight;
                meCache.maxScroll = meCache.containerHeight - meCache.height;
                meCache.minScroll = meCache.isPullRefresh ? 40 : 0;
                meCache.scrollY = meCache.scrollY || 0;

                meCache.maxScroll = meCache.maxScroll > 0 ? 0 : meCache.maxScroll;

                if (meCache.isPullMore)
                    meCache.maxScroll -= 40;

                if (meCache.scrollY < meCache.maxScroll)
                    utils.setScrollY(dom, meCache.maxScroll);
            },
            touchHandler: function (e) {
                switch (e.type) {
                    case "touchstart":
                        if (!container) {
                            var meContainer = this.children ? this.children[0] : this,
                                meCacheID = utils.attr(meContainer, "data-nt-scroll");

                            startPoint = { x: e.changedTouches[0].pageX, y: e.changedTouches[0].pageY };

                            if (meCacheID && allCache[meCacheID] && !allCache[meCacheID].disable) {
                                startTime = (new Date()).getTime();
                                container = meContainer;
                                cache = allCache[meCacheID];

                                if (cache.animFrameID) {
                                    utils.cancelAnimFrame(cache.animFrameID);
                                    cache.animFrameID = undefined;
                                }

                                cache.touchStart = true;
                            }
                        }
                        break;
                    case "touchmove":
                        if (container) {
                            if (e.changedTouches[0].pageY <= 2)
                                utils.touchEndHandler(e);
                            else
                                utils.scrollYHandle(e);
                        }
                        break;
                    case "touchcancel":
                        e.cancelable = true;
                    default:
                        if (startPoint) {
                            var offsetY = e.changedTouches[0].pageY - startPoint.y,
                                offsetX = e.changedTouches[0].pageX - startPoint.x;

                            // $("#divDebug").text(offsetX + offsetY);

                            //alert(offsetY + "" + offsetX);

                            if (Math.abs(offsetX) < 6 && Math.abs(offsetY) < 6) {
                                if (document.activeElement && document.activeElement != e.target)
                                    document.activeElement.blur();

                                if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") {
                                    e.target.focus();

                                    var boxType = (e.target.type || "text").toLowerCase();

                                    //让光标定位到最末位
                                    if (boxType === "text" || boxType === "password" || boxType === "number") {
                                        try {
                                            var len = e.target.value.length;
                                            if (document.selection) {
                                                var sel = e.target.createTextRange();
                                                sel.moveStart('character', len);
                                                sel.collapse();
                                                sel.select();
                                            } else if (typeof e.target.selectionStart == 'number' && typeof e.target.selectionEnd == 'number') {
                                                e.target.selectionStart = e.target.selectionEnd = len;
                                            }
                                        } catch (es) { }
                                    }
                                } else {
                                    e.target && e.target.click && e.target.click();
                                }
                            } else if (container) {
                                utils.touchEndHandler(e);
                            }
                        }

                        if (cache)
                            cache.touchStart = undefined;

                        startPoint = startTime = container = cache = undefined;
                        break;
                }
                if(boxType != 'radio' && boxType != undefined){
                    e.preventDefault();
                }

            },
            touchEndHandler: function (e) {
                var y = endY = utils.scrollYHandle(e);

                if (cache.pars && cache.pars.pullRefresh && !cache.isPullRefresh && y > 0)
                    utils.setPullState(y > 40 ? 1 : 0, cache, container, false);

                if (cache.pars && cache.pars.pullMore && !cache.isPullMore && cache.haveMore && y < cache.maxScroll)
                    utils.setPullState(y < cache.maxScroll - 40 ? 3 : 2, cache, container, false);

                if (y < cache.maxScroll) {
                    endY = cache.maxScroll;
                } else if (y > cache.minScroll || cache.height <= cache.containerHeight) {
                    endY = cache.minScroll;
                } else {
                    var currentTime = (new Date()).getTime(),
                        speed = (cache.scrollY - y) / (currentTime - startTime) * 10;

                    if (Math.abs(speed) > 5)
                        utils.scrollYAnim(container, y, speed, cache);
                }

                cache.scrollY = y;

                if (cache)
                    cache.touchStart = undefined;

                if (endY != y)
                    utils.springbackYAnim(container, y, endY, cache);

                startPoint = startTime = container = cache = undefined;
            },
            scrollYHandle: function (e) {
                var offsetY = e.changedTouches[0].pageY - startPoint.y;

                if (offsetY + cache.scrollY > cache.minScroll) {
                    if (offsetY > 0) {
                        if (cache.scrollY >= cache.minScroll)
                            offsetY = cache.scrollY + offsetY / 3;
                        else
                            offsetY = cache.minScroll + (cache.scrollY + offsetY - cache.minScroll) / 3;
                    } else {
                        offsetY = cache.scrollY + offsetY;
                    }
                } else if (offsetY + cache.scrollY < cache.maxScroll) {
                    if (offsetY < 0) {
                        if (cache.scrollY <= cache.maxScroll)
                            offsetY = cache.scrollY + offsetY / 3;
                        else
                            offsetY = cache.maxScroll + (cache.scrollY + offsetY - cache.maxScroll) / 3;
                    } else {
                        offsetY = cache.scrollY + offsetY;
                    }
                } else {
                    offsetY += cache.scrollY;
                }

                utils.setScrollY(container, offsetY);

                if (cache.pars && cache.pars.pullRefresh && !cache.isPullRefresh)
                    utils.setPullInfo(offsetY > 40 ? 1 : 0, cache);

                if (cache.pars && cache.pars.pullMore && !cache.isPullMore && cache.haveMore)
                    utils.setPullInfo(offsetY < cache.maxScroll - 40 ? 6 : 5, cache);

                return offsetY;
            },
            setPullState: function (state, meCache, dom, isSpringbackY) {
                var endY, handleType, isSpringbackY = isSpringbackY === undefined ? true : isSpringbackY;

                if (meCache.isPullRefresh && state === 0) {
                    meCache.minScroll = endY = 0;
                    meCache.isPullRefresh = false;
                    state = 0;
                } else if (!meCache.isPullRefresh && state === 1) {
                    meCache.minScroll = endY = 40;
                    handleType = "refresh";
                    meCache.isPullRefresh = true;
                    state = 2;
                } else if (meCache.isPullMore && state === 2) {
                    meCache.maxScroll = endY = meCache.maxScroll + 40;
                    meCache.isPullMore = false;
                    state = 5;
                } else if (!meCache.isPullMore && state === 3) {
                    meCache.maxScroll = endY = meCache.maxScroll - 40;
                    handleType = "more";
                    meCache.isPullMore = true;
                    utils.haveMore(dom, meCache, true);
                    state = 7;
                }

                if (handleType && meCache.pars.pullHandler)
                    meCache.pars.pullHandler({ type: handleType });

                if (endY !== undefined && isSpringbackY)
                    utils.springbackYAnim(dom, meCache.scrollY, endY, meCache);

                utils.setPullInfo(state, meCache);
            },
            setPullInfo: function (state, meCache) {
                var refreshIcon = document.getElementById(meCache.id + "RefreshIcon"),
                    refreshText = document.getElementById(meCache.id + "RefreshText"),
                    moreIcon = document.getElementById(meCache.id + "MoreIcon"),
                    moreText = document.getElementById(meCache.id + "MoreText"),
                    moreDom = document.getElementById(meCache.id + "More");

                switch (state) {
                    case 0:
                        refreshIcon.className = "pull-down-icon";
                        refreshText.innerText = "下拉刷新";
                        break;
                    case 1:
                        refreshIcon.className = "pull-up-icon";
                        refreshText.innerText = "松开刷新";
                        break;
                    case 2:
                        refreshIcon.className = "pull-loading-icon loading";
                        refreshText.innerText = "刷新中..";
                        break;
                    case 3:
                        refreshIcon.className = "pull-error-icon iconfont";
                        refreshText.innerText = "刷新失败";
                        break;
                    case 4:
                        refreshIcon.className = "pull-yes-icon iconfont";
                        refreshText.innerText = "刷新成功";
                        break;
                    case 5:
                        moreIcon.className = "pull-up-icon";
                        moreText.innerText = "上拉加载更多";
                        break;
                    case 6:
                        moreIcon.className = "pull-down-icon";
                        moreText.innerText = "松开加载更多";
                        break;
                    case 7:
                        moreIcon.className = "pull-loading-icon loading";
                        moreText.innerText = "加载更多中..";
                        break;
                    case 8:
                        moreIcon.className = "pull-error-icon iconfont";
                        moreText.innerText = "加载更多失败";
                        break;
                }
            },
            attr: function (dom, name, val) {
                if (val === undefined)
                    return dom.getAttribute ? dom.getAttribute(name) : null;

                dom.setAttribute(name, val);
            },
            animFrame: function (callback) {
                var animFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame;
                return animFrame(callback);
            },
            cancelAnimFrame: function (animFrameID) {
                var cancelAnimFrame = window.cancelAnimationFrame || window.webkitCancelAnimationFrame;
                cancelAnimFrame(animFrameID);
            },
            initCacheID: function (dom) {
                var meCacheID = utils.attr(dom, "data-nt-scroll");
                if (!meCacheID) {
                    meCacheID = "ntScroll" + cacheIndex++;
                    utils.attr(dom, "data-nt-scroll", meCacheID);
                }

                return meCacheID;
            },
            haveMore: function (dom, meCache, isHave) {
                meCache.haveMore = !!isHave;

                if (meCache.pars && meCache.pars.pullMore) {
                    if (meCache.haveMore) {
                        dom.parentElement.className = dom.parentElement.className.replace(new RegExp('(\\s|^)pull-not-more(\\s|$)'), '');

                        document.getElementById(meCache.id + "More").style.display = "block";
                    } else {
                        document.getElementById(meCache.id + "More").style.display = "none";

                        if (dom.parentElement.className.indexOf("pull-not-more") === -1)
                            dom.parentElement.className += " pull-not-more";
                    }
                }
            },
            scrollYTo: function (dom, meCache, y) {
                if (y > meCache.minScroll)
                    y = meCache.minScroll;
                else if (y < meCache.maxScroll)
                    y = meCache.maxScroll;

                utils.setScrollY(dom, y);
                utils.scrollY = y;
            },
            disable: function (dom, meCache, disable) {
                meCache.disable = !!disable;

                if (meCache.disable) {
                    utils.scrollYTo(dom, meCache.scrollY);

                    if (meCache.touchStart !== undefined)
                        meCache.touchStart = undefined;

                    startPoint = startTime = container = cache = undefined;
                }
            }
        };

    window.ntScroll = function (dom, pars) {
        var me = this,
            meCacheID;

        if (typeof dom === "string")
            dom = document.getElementById(dom);

        meCacheID = utils.initCacheID(dom);

        me.refresh = function () {
            utils.init(dom, meCacheID, pars);
        };

        me.setPullRefreshState = function (state) {
            utils.setPullState(state ? 1 : 0, allCache[meCacheID], dom);
        };

        me.setPullRefreshResult = function (state) {
            utils.setPullInfo(state ? 4 : 3, allCache[meCacheID]);
        };

        me.setPullMoreState = function (state, isAnim) {
            utils.setPullState(state ? 3 : 2, allCache[meCacheID], dom, isAnim);
        };

        me.setPullMoreResult = function (state) {
            utils.setPullInfo(state ? 5 : 8, allCache[meCacheID]);
        };

        me.haveMore = function (isHave) {
            utils.haveMore(dom, allCache[meCacheID], isHave);
        };

        me.scrollYTo = function (y) {
            utils.scrollYTo(dom, allCache[meCacheID], y);
        };

        me.disable = function (disable) {
            utils.disable(dom, allCache[meCacheID], disable);
        }

        if (!allCache[meCacheID])
            me.refresh();
    };

    utils.addEvent(window, "touchmove,touchend,touchcancel,touchstart", utils.touchHandler);
})();

/*
 * 定义常量、杂项，兼容性处理以及事件绑定等操作。
 */
(function () {
    //定义常量
    window.siteConfig = {
        PUBLIC: "http://public.millionmake.com/Public",              //静态资源前缀
        CONHOST: "http://pic.millionmake.com/Public"              //上传的资源前缀
    };

    //重置百度PV统计的链接
    window._hmt = [];
    _hmt.push(['_setAutoPageview', false]);
    var routeStr = tools.queryPars("s"),
        controller, action;

    if (!routeStr)
        routeStr = location.href.replace(location.origin, "");

    if (routeStr) {
        var routeAry = routeStr.split("/");
        if (routeAry.length > 3) {
            controller = routeAry[1];
            action = routeAry[2];
        } else {
            controller = "unknown";
            action = "unknown";
        }
    } else {
        controller = "unknown";
        action = "unknown";
    }
    //定义当前页面路径
    _hmt.push(['_trackPageview', '/' + controller + '/' + action]);
    //定义用户来源类型
    _hmt.push(['_setCustomVar', 1, "gfrom", tools.getFromType(), 3]);
    //定义用户来源城市
    _hmt.push(['_setCustomVar', 2, "city", tools.getCityID(), 3]);
    //定义用户是否登录
    _hmt.push(['_setCustomVar', 3, "logined", tools.isBind(), 3]);

    //绑定显示左侧菜单按钮的点击事件。
    $("#btnShowLeftMenu").bind("click", function (e) {
        $(this).addClass("rotate").removeClass("static");
        $("#leftMenu").removeClass("hide").addClass("show");
        $("#contentContainer").removeClass("show").addClass("hide");

        tools.sendData('点击侧面折叠栏按钮');

        e.stopPropagation();
    });

    //绑定主内容区域的点击以及动画完成事件。
    $("#contentContainer").bind("click", function () {
        if ($(this).hasClass("hide")) {
            $("#leftMenu").removeClass("show").addClass("hide");
            $(this).removeClass("hide").addClass("show");
        }
        $("#btnShowLeftMenu").removeClass("rotate").addClass("static");
    }).bind("animationend,webkitAnimationEnd", function () {
        $("#contentContainer").removeClass("show");
    });

    //左侧导航菜单点击事件
    window.leftMenuClick = function (tag, name) {
        tools.sendData(name);

        var href = $(tag).attr("data-href");

        if (href) {
            tools.loading("页面跳转中");

            location.href = href;
        }
    };
})();

document.write('<script src="//hm.baidu.com/hm.js?684aea316c731454b63892609871d442"></script>');