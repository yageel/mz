/*
 * author by:王高飞
 * date:2016-12-05 10:36:47
 */

(function () {
    var resBasePath = "/Public/images/redRain/",
        allTexture = {}, width = document.body.clientWidth * 2,
        stage, renderer, height = document.body.clientHeight * 2,
        allObject = [], startTime, keySprites = {}, maxObjectCount = 15,
        totalCountdownSecond = 3, currentCountdownIndex,
        totalSecond = 15, gameState, goldScore = redScore = goodsScore = 0,
        raf = window.requestAnimationFrame || window.webkitRequestAnimationFrame || function (callback) { setTimeout(callback, 10); },
        actUId = $("#redrain_actuuid").val(),
        actSubId = $("#redrain_actsubid").val();
    var actSource = $("#redrain_source").val();
    var draw_session="redMoney"+actSubId;
    // 加载所有的图片资源
    loadAllImage(function () {
        $("#divSpinner").remove();

        // 创建pixi的stage
        stage = new PIXI.Stage(0x000000);

        // 创建一个渲染
        renderer = PIXI.autoDetectRenderer(width, height);

        // 添加游戏到页面
        document.body.appendChild(renderer.view);

        //若当前session里面有值，就跳到预告页;没值就执行运行游戏的方法。
        if (tools.session(draw_session)) {
            var sessionData = JSON.parse(tools.session(draw_session));

            if (sessionData.gotoLink)
                location.href = sessionData.gotoLink;
            else
                tools.alert(sessionData.msg);
        } else {
            // 开始游戏运作
            startGame();
        }
    });

    // 添加游戏对象（现金或M币）
    function addGameObj() {
        var rdNumber = Math.random(),
            obj = new gameObject(rdNumber > .80 ? "goods" : rdNumber > .6 ? "red" : "gold");

        allObject.push(obj);

        stage.addChild(obj.icon);
    }

    // 开始游戏运作
    function startGame() {
        allGold = [];

        keySprites = {};

        currentCountdownIndex = 0;

        if (userSubscribe == 0 && tools.queryPars("from") === "1") {
            gameState = 3;

            tools.ajax(tools.url("redrain", "get_user_subscribe"), function (ret) {
                tools.closeLoading();

                if (ret.data.userSubscribe != 1) {
                    tools.alert("请关注我们微信号才能参与红包雨活动~", function () {
                        tools.sendData('摇一摇红包页-点击确认关注按钮-城市ID：, 点击确认关注按钮', '点击确认关注按钮');
                        BeaconAddContactJsBridge.invoke('jumpAddContact');
                    });
                } else {
                    gameState = 0;
                }
            });

        } else if (userSubscribe == 0 && tools.queryPars("from") != "1") {
            gameState = 3;
            tools.alert("请关注我们微信号才能参与红包雨活动~~~~", function () {
                var jumpurl = $("#redrain_cityinfo").val();
                location.href = jumpurl;
            });


        } else {
            gameState = 0;
        }

        // 设置开始时间
        startTime = new Date().getTime();

        // 添加游戏背景图
        addFullSprite("gameBg");

        for (var i = 0; i < maxObjectCount; i++) {
            addGameObj();
        }

        // 添加点击效果图
        addEffect();

        // 添加游戏计时栏
        addCountdown(startTime);

        // 添加游戏得分栏
        addScore();

        // 添加倒计时遮罩层
        keySprites.mask = addFullSprite("mask");

        // 添加倒计时提示
        addStartCountdownTime(totalCountdownSecond - currentCountdownIndex);

        // 添加提示文字
        addPrompt();

        // 刷新游戏
        animate();
    }

    // 游戏开始倒计时结束
    function countdownOver() {
        gameState = 1;

        startTime = new Date().getTime();
    }

    // 游戏计时结束
    function timeOver() {
        tools.ajax(tools.url("redrain", "redrain_gameresult"), {
            uuid: actUId,
            id: actSubId
        }, function (ret) {
            if (ret.state == 10) {
                var sessionData;
                //游戏结束弹框文案
                var gameText="恭喜您一共获得" +ret.data.red_money +"元红包!快去提现吧~";
                if(wishcard_show ==1){
                	gameText="恭喜您一共获得" +ret.data.red_money +"元红包!收集完三张吉利祝福卡片即可换取汽车使用权大奖！每天都可以参与一次哦~";
                }
                if (actSource == 1) {
                    sessionData = { msg: gameText, gotoLink: tools.url("active", "lottery", { id: actUId }) };
                } else {
                    var adid = $("#redrain_adid").val();

                    if (tools.queryPars("from") === "1")
                        sessionData = { msg: gameText, gotoLink: tools.url("index", "index", { item: adid }) };
                    else
                        sessionData = { msg: gameText, gotoLink: tools.url("index", "index", { item: adid }) };
                }

                tools.session(draw_session, JSON.stringify(sessionData));
                //卡牌赋值
                var thank_pic=ret.data.wishcard_img;
                var wishcard_id=ret.data.wishcard_id;
                
                //游戏结果
                tools.alert(sessionData.msg, function () {
                    if(wishcard_show ==1){
                      //显示卡牌
                    	$("#wrapContent").css("zIndex", "10000");
                        $("#container").addClass("container");
                        //页面绘制完，计算高度
                        //浏览器高度
                        var h = document.body.offsetHeight;
                        //内容区域高度
                        var totalH = $("#mainContent").height();
                        //当内容区域高度大于浏览器区域高度时，重置图片的高度等于(以前图片的高度-distanceH)
                        if(totalH > h){
                            var distanceH = totalH - h;
                            var containerH = $("#container").find(".year").height() - distanceH;
                            $("#container").find(".year").height(containerH);
                        }
                    	 var containerDom = document.getElementById("container"),
                             img = containerDom.getElementsByClassName("year");

    		             function showBtn(){
    		                 $("#sureBtn").show();
    		           	 }
                        function toggleActive(){
                            $("#container").find(".year").attr("src",thank_pic);
                            updata_wishcard();
                        }
    		             containerDom.addEventListener("animationend",toggleActive);
    		             containerDom.addEventListener("animationend",showBtn);
    		             containerDom.addEventListener("webkitAnimationEnd",toggleActive);
    		             containerDom.addEventListener("webkitAnimationEnd",showBtn);
                    }else{
                    	//跳到结算页
                        location.href = tools.url("redrain", "redrain_settlement", { uuid: actUId, id: actSubId });
                    }
                });
              //卡牌小图更新
             	function updata_wishcard(){
             		if(wishcard_id >0){
                    	var cardnum=$("#"+wishcard_id).find("span").text();
                        ++cardnum;
                        $("#"+wishcard_id).find("span").text(cardnum);
                        $("#"+wishcard_id).find("div.iconImg").removeClass("gray");
                        $("#"+wishcard_id).find("p.num").removeClass("num-disappear");
                    }
                 	return;
             	}
            } else {
                tools.session(draw_session, JSON.stringify({ msg: ret.msg, gotoLink: "" }));

                tools.alert(ret.msg);
            }
        });
    };

    // 添加游戏点击效果图
    function addEffect() {
        if (keySprites.effect) {
            stage.removeChild(keySprites.effect);

            stage.addChild(keySprites.effect);

            return;
        }

        var sprite = keySprites.effect = new PIXI.Sprite(allTexture["effect"]);

        sprite.setAnim = function (point) {
            sprite.animType = "magnify";
            sprite.scale.x = sprite.scale.y = .5;
            sprite.visible = true;
            sprite.position.x = point.x;
            sprite.position.y = point.y;
        };

        sprite.updateAnim = function () {
            if (!sprite.visible)
                return;

            var zoomSpeed = .1;

            if (sprite.animType == "magnify") {
                sprite.scale.x = sprite.scale.y = sprite.scale.y + zoomSpeed;

                if (sprite.scale.x >= 1)
                    sprite.animType = "shrink";
            } else if (sprite.animType == "shrink") {
                sprite.scale.x = sprite.scale.y = sprite.scale.y - zoomSpeed;

                if (sprite.scale.x <= .5) {
                    sprite.animType = "";
                    sprite.visible = false;
                }
            }
        };

        setAnchor(sprite);

        sprite.visible = false;

        stage.addChild(sprite);
    }

    // 添加游戏提示文字
    function addPrompt() {
        var prompt = keySprites.prompt = new PIXI.Text("开始点击屏幕上的红包、金币和礼盒吧！");
        setAnchor(prompt);

        prompt.position.x = width / 2;
        prompt.position.y = height / 2 + allTexture["time1"].height + 50;
        prompt.style.fill = "#ffffff";
        prompt.style.font = "normal 28px Arial";

        stage.addChild(prompt);

        var prompt2 = keySprites.prompt2 = new PIXI.Text("");
        setAnchor(prompt2);

        prompt2.position.x = width / 2;
        prompt2.position.y = height / 2 + allTexture["time1"].height + 90;
        prompt2.style.fill = "#ffffff";
        prompt2.style.font = "normal 28px Arial";

        stage.addChild(prompt2);
    }

    // 游戏对象类
    var gameObject = (function () {
        var randomX = width * .3,
            startX = width * .7,
            endY = height * .4,
            minSpeed = 5,
            maxSpeed = 12,
            dynamicMinSpeed = 3,
            dynamicMaxSpeed = 20,
            auxiliaryMinSpeed = 1,
            auxiliaryMaxSpeed = 25,
            minAuxiliaryScale = .5,
            maxAuxiliaryScale = 1.2,
            maxOffsetX = 40,
            maxOffsetY = 40;

        // 设置游戏对象的随机位置
        function setRandomLocation(obj) {
            var isTopLocation = Math.random() > 0.5,
                x = y = dx = dy = 0,
                dynamicLocationOffset = obj.dynamic.width * .7,
                dynamicOffset = Math.random() * dynamicLocationOffset;

            if (isTopLocation) {
                x = Math.random() * randomX + startX;
                y = -obj.icon.height - Math.random() * maxOffsetY;

                var centerX = x + obj.icon.width / 2;

                dx = centerX + (Math.random() > .5 ? dynamicOffset : -dynamicOffset);
                dy = -(obj.dynamic.height - obj.icon.height);

                if (obj.auxiliary) {
                    var auxiliaryOffset = Math.random() * obj.auxiliary.width * 3;

                    obj.auxiliary.position.x = centerX + (Math.random() > .5 ? auxiliaryOffset : -auxiliaryOffset);
                    obj.auxiliary.position.y = -obj.auxiliary.height;
                }
            } else {
                x = dx = width + obj.icon.width + Math.random() * maxOffsetX;
                y = Math.random() * endY;

                var centerY = y + obj.icon.height / 2;

                dy = centerY + (Math.random() > .5 ? dynamicOffset : -dynamicOffset);

                if (obj.auxiliary) {
                    var auxiliaryOffset = Math.random() * obj.auxiliary.width * 3;

                    obj.auxiliary.position.y = centerY + (Math.random() > .5 ? auxiliaryOffset : -auxiliaryOffset);
                    obj.auxiliary.position.x = width;
                }
            }

            obj.icon.position.x = x;
            obj.icon.position.y = y;
            obj.dynamic.position.x = dx;
            obj.dynamic.position.y = dy;
        }

        // 随机给游戏对象一个速度
        function setRandomSpeed(obj) {
            obj.iconSpeed = minSpeed + Math.random() * (maxSpeed - minSpeed);
            obj.dynamicSpeed = dynamicMinSpeed + Math.random() * (dynamicMaxSpeed - dynamicMinSpeed);

            if (obj.auxiliary)
                obj.auxiliarySpeed = auxiliaryMinSpeed + Math.random() * (auxiliaryMaxSpeed - auxiliaryMinSpeed);
        }

        // 处理当前对象，若无引用则释放掉内存
        function dispose(obj) {
            if (obj.icon && (obj.icon.position.x < -obj.icon.width || obj.icon.position.y > height))
                removeObjet(obj);

            if (obj.auxiliary && (obj.auxiliary.position.x < -obj.auxiliary.width || obj.auxiliary.position.y > height)) {
                stage.removeChild(obj.auxiliary);

                obj.auxiliary = null;
            }

            if (obj.dynamic && (obj.dynamic.position.x < -obj.dynamic.width || obj.dynamic.position.y > height)) {
                stage.removeChild(obj.dynamic);

                obj.dynamic = null;
            }

            if (!obj.dynamic && !obj.auxiliary && !obj.icon) {
                for (var i = 0; i < allObject.length; i++) {
                    if (allObject[i] === obj) {
                        allObject.splice(i, 1);
                        return;
                    }
                }

                obj = null;
            }
        }

        // 移除一个游戏对象
        function removeObjet(obj) {
            stage.removeChild(obj.icon);

            obj.icon = null;

            addGameObj();

            stage.removeChild(keySprites.score);

            stage.addChild(keySprites.score);

            stage.removeChild(keySprites.countdown);

            stage.addChild(keySprites.countdown);

            addEffect();
        }

        return function (type) {
            var me = this,
                icon = me.icon = new PIXI.Sprite(allTexture[type]),
                dynamic = me.dynamic = new PIXI.Sprite(allTexture["dynamic" + parseInt(Math.random() * 4)]),
                auxiliary;

            // 给当前对象注册一个更新方法
            me.update = function () {
                if (icon) {
                    icon.position.x -= me.iconSpeed;
                    icon.position.y += me.iconSpeed;
                }

                if (dynamic) {
                    dynamic.position.x -= me.dynamicSpeed;
                    dynamic.position.y += me.dynamicSpeed;
                }

                if (auxiliary) {
                    auxiliary.position.x -= me.auxiliarySpeed;
                    auxiliary.position.y += me.auxiliarySpeed;
                }

                dispose(me);
            };

            // 绑定游戏对象的触摸事件
            icon.interactive = true;
            icon.touchstart = function (e) {
                //游戏开始
                if (gameState == 1) {
                    removeObjet(me);

                    keySprites.effect.setAnim(e.data.global);

                    //此处调后台接口，下面if判断写在ajax的回调函数中。
                    tools.ajax(tools.url("redrain", "redrain_winred"), {
                        raintype: type == "red" ? 1 : type == "gold" ? 2 : 3,
                        uuid: actUId,
                        id: actSubId
                    }, function (ret) {
                        if (ret.state == 10 && ret.data) {
                            if (type == "gold")
                                keySprites.score.updateGoldScore(++goldScore, true);
                            else if (type == "goods")
                                keySprites.score.updateGoodsScore(++goodsScore, true);
                            else
                                keySprites.score.updateRedScore(++redScore, true);
                        }else if(ret.state == 11 && ret.data){
                        	//同时生成礼品和卡牌
                        	 ++goodsScore;
                        	 keySprites.score.updateGoodsScore(++goodsScore, true);
                        }
                    }, {
                        loading: false
                    });
                }
            };

            // 让图标旋转45°
            icon.rotation = 0.7853981633974483;

            // 随机给一个附加的星星团
            if (Math.random() > .4) {
                auxiliary = me.auxiliary = new PIXI.Sprite(allTexture["auxiliary" + parseInt(Math.random() * 3)]);
                auxiliary.scale.x = auxiliary.scale.y = minAuxiliaryScale + Math.random() * (maxAuxiliaryScale - minAuxiliaryScale);

                stage.addChild(auxiliary);
            }

            stage.addChild(dynamic);
            stage.addChild(icon);

            setRandomSpeed(me);
            setRandomLocation(me);
        };
    })();

    // 添加游戏计时栏
    function addCountdown(now) {
        var countdown = keySprites.countdown = new PIXI.Sprite(allTexture["countdownBg"]),
            countdownLabel = new PIXI.Text("倒计时"),
            countdownText = new PIXI.Text("");

        countdownLabel.style.fill = "#ffffff";
        countdownLabel.style.font = countdownText.style.font = "bold 40px Arial";
        countdownLabel.anchor.y = countdownText.anchor.y = .5;
        countdownLabel.position.y = countdownText.position.y = countdown.height / 2;
        countdownLabel.position.x = 190;

        countdownText.style.fill = "#350374";
        countdownText.position.x = 330;

        countdown.update = function (now) {
            var differenceSecond = parseInt((now - startTime) / 1000),
            countdownSecond = totalSecond - differenceSecond

            if (countdownSecond < 10)
                countdownSecond = "00" + countdownSecond;
            else if (countdownSecond < 100)
                countdownSecond = "0" + countdownSecond;

            countdownText.setText(countdownSecond);

            return countdownSecond <= 0;
        };

        countdown.position.x = (width - countdown.width) / 2;
        countdown.position.y = 18;

        countdown.update(now);

        countdown.addChild(countdownLabel);
        countdown.addChild(countdownText);

        stage.addChild(countdown);
    }

    // 添加游戏得分栏
    function addScore() {
        var score = keySprites.score = new PIXI.Sprite(allTexture["scoreBg"]),
            scoreBg = new PIXI.Sprite(allTexture["scoreBg"]),
            scoreLabel = new PIXI.Text("当前获得奖励"),
            redScoreIcon = new PIXI.Sprite(allTexture["red"]),
            redScoreCircle = new PIXI.Sprite(allTexture["circle"]),
            redScoreText = new PIXI.Text(""),
            goodsScoreIcon = new PIXI.Sprite(allTexture["goods"]),
            goodsScoreCircle = new PIXI.Sprite(allTexture["circle"]),
            goodsScoreText = new PIXI.Text(""),
            goldScoreIcon = new PIXI.Sprite(allTexture["gold"]),
            goldScoreCircle = new PIXI.Sprite(allTexture["circle"]),
            goldScoreText = new PIXI.Text(""),
            maxIconHeight = 50,
            updateAnim = function (circle) {
                var zoomSpeed = .1;

                if (circle.animType == "magnify") {
                    circle.scale.x = circle.scale.y = circle.scale.y + zoomSpeed;

                    if (circle.scale.x >= .8)
                        circle.animType = "shrink";
                } else if (circle.animType == "shrink") {
                    circle.scale.x = circle.scale.y = circle.scale.y - zoomSpeed;

                    if (circle.scale.x <= 0) {
                        circle.animType = "";
                        circle.visible = false;
                    }
                }
            };

        setAnchor(goldScoreCircle);
        setAnchor(redScoreCircle);
        setAnchor(goodsScoreCircle);

        goodsScoreCircle.visible = redScoreCircle.visible = goldScoreCircle.visible = false;

        goldScoreText.position.x = 135;
        goldScoreText.style.fill = redScoreText.style.fill = goodsScoreText.style.fill = scoreLabel.style.fill = "#ffffff";
        goldScoreText.style.font = redScoreText.style.font = goodsScoreText.style.font = "bold 40px Arial";
        goldScoreText.anchor.y = goldScoreIcon.anchor.y = redScoreText.anchor.y = goodsScoreText.anchor.y = redScoreIcon.anchor.y = goodsScoreIcon.anchor.y = scoreLabel.anchor.y = .5;
        goodsScoreCircle.position.y = redScoreCircle.position.y = goldScoreCircle.position.y = goodsScoreText.position.y = goldScoreText.position.y = goldScoreIcon.position.y = redScoreText.position.y = redScoreIcon.position.y = goodsScoreIcon.position.y = scoreLabel.position.y = score.height / 2 + 8;

        goldScoreCircle.position.x = goldScoreText.position.x + 22;

        goldScoreIcon.scale.y = goldScoreIcon.scale.x = maxIconHeight / goldScoreIcon.height;
        goldScoreIcon.position.x = 75;

        redScoreText.position.x = 9;

        redScoreCircle.position.x = redScoreText.position.x + 22;

        goodsScoreIcon.scale.y = goodsScoreIcon.scale.x = maxIconHeight / goodsScoreIcon.height;
        goodsScoreIcon.position.x = 200;
        goodsScoreText.position.x = 255;
        goodsScoreCircle.position.x = goodsScoreText.position.x + 22;

        redScoreIcon.scale.y = redScoreIcon.scale.x = maxIconHeight / redScoreIcon.height;
        redScoreIcon.position.x = -85;

        scoreLabel.position.x = -295;
        scoreLabel.style.font = "bold 30px Arial";

        scoreBg.scale.x = width / score.width;
        scoreBg.position.x = -width / 2;

        score.anchor.x = .5;
        score.position.x = width / 2;
        score.position.y = height - score.height;

        score.updateRedScore = function (value, isAnim) {
            if (value < 10)
                value = "0" + value;

            if (isAnim) {
                redScoreCircle.animType = "magnify";
                redScoreCircle.scale.x = redScoreCircle.scale.y = 0;
                redScoreCircle.visible = true;
            }

            redScoreText.setText(value);
        };

        score.updateGoldScore = function (value, isAnim) {
            if (value < 10)
                value = "0" + value;

            if (isAnim) {
                goldScoreCircle.animType = "magnify";
                goldScoreCircle.scale.x = goldScoreCircle.scale.y = 0;
                goldScoreCircle.visible = true;
            }

            goldScoreText.setText(value);
        };

        score.updateGoodsScore = function (value, isAnim) {
            if (value < 10)
                value = "0" + value;

            if (isAnim) {
                goodsScoreCircle.animType = "magnify";
                goodsScoreCircle.scale.x = goodsScoreCircle.scale.y = 0;
                goodsScoreCircle.visible = true;
            }

            goodsScoreText.setText(value);
        };

        score.updateAnim = function () {
            updateAnim(goldScoreCircle);
            updateAnim(redScoreCircle);
            updateAnim(goodsScoreCircle);
        };

        score.updateRedScore(redScore);
        score.updateGoldScore(goldScore);
        score.updateGoodsScore(goodsScore);

        score.addChild(scoreBg);
        score.addChild(scoreLabel);
        score.addChild(redScoreCircle);
        score.addChild(goldScoreCircle);
        score.addChild(redScoreIcon);
        score.addChild(redScoreText);
        score.addChild(goldScoreIcon);
        score.addChild(goldScoreText);
        score.addChild(goodsScoreIcon);
        score.addChild(goodsScoreCircle);
        score.addChild(goodsScoreText);

        stage.addChild(score);
    }

    // 添加一个铺满全屏的对象到画布中
    function addFullSprite(key) {
        var sprite = new PIXI.Sprite(allTexture[key]);

        sprite.scale.y = sprite.scale.x = calculatingZoom(sprite.width, sprite.height);

        sprite.position.x = sprite.position.y = 0;

        stage.addChild(sprite);

        return sprite;
    }

    // 计算把图片填充到整个画面的最小缩放值
    function calculatingZoom(w, h) {
        var widthScale = width / w,
            heightScale = height / h;

        return widthScale > heightScale ? widthScale : heightScale;
    }

    // 设置对象的中心点
    function setAnchor(sprite, x, y) {
        x = x || .5;
        y = y || .5;

        sprite.anchor.x = x;
        sprite.anchor.y = y;

        return sprite;
    }

    // 更新场景
    function animate() {
        var now = new Date().getTime();

        keySprites.score.updateAnim();
        keySprites.effect.updateAnim();

        if (gameState != 3) {
            // 更新3-1开始倒计时
            updateStartCountdown(now);

            // 为1时代表游戏正式开始
            if (gameState == 1) {
                gameState = keySprites.countdown.update(now) ? 2 : 1;

                if (gameState == 2) {
                    timeOver();
                } else {
                    for (var i = 0; i < allObject.length; i++) {
                        allObject[i].update();
                    }
                }
            }
        }

        renderer.render(stage);

        raf(animate);
    }

    // 更新开始游戏倒计时图片
    function updateStartCountdown(now) {
        if (gameState)
            return;

        var differenceSecond = parseInt((now - startTime) / 1000);

        if (differenceSecond != currentCountdownIndex) {
            stage.removeChild(keySprites.time);
            currentCountdownIndex++;

            if (differenceSecond == totalCountdownSecond) {
                stage.removeChild(keySprites.mask);
                stage.removeChild(keySprites.prompt);
                stage.removeChild(keySprites.prompt2);

                keySprites.time = undefined;
                keySprites.mask = undefined;
                keySprites.prompt = undefined;
                keySprites.prompt2 = undefined;

                delete keySprites.time;
                delete keySprites.prompt;
                delete keySprites.prompt2;
                delete keySprites.mask;

                countdownOver();
            } else {
                addStartCountdownTime(totalCountdownSecond - currentCountdownIndex);
            }
        }
    }

    // 添加开始游戏倒计时图片
    function addStartCountdownTime(idx) {
        var time = keySprites.time = new PIXI.Sprite(allTexture["time" + idx]);

        setAnchor(time);

        time.position.x = width / 2;
        time.position.y = height / 2;

        stage.addChild(time);

        return time;
    }

    // 加载所有图片资源
    function loadAllImage(completeCallback) {
        var singleLoadCount = 5, isError, allowErrorCount = 3, completeCount = 0, currentLoadIndex, imageRes = [
            { path: redbg, key: "gameBg" },
            { path: goldicon, key: "gold" },
            { path: 'time3.png', key: "time3" },
            { path: 'time2.png', key: "time2" },
    	    { path: 'time1.png', key: "time1" },
    	    { path: 'countdownBg.png', key: "countdownBg" },
		    { path: 'scoreBg.png', key: "scoreBg" },
		    { path: 'mask.png', key: "mask" },
		    { path: redicon, key: "red" },
		    { path: 'dynamic0.png', key: "dynamic0" },
		    { path: 'dynamic1.png', key: "dynamic1" },
		    { path: 'dynamic2.png', key: "dynamic2" },
		    { path: 'dynamic3.png', key: "dynamic3" },
		    { path: 'auxiliary0.png', key: "auxiliary0" },
		    { path: 'auxiliary1.png', key: "auxiliary1" },
		    { path: 'auxiliary2.png', key: "auxiliary2" },
		    { path: 'effect.png', key: "effect" },
		    { path: giftbg, key: "goods" },
		    { path: 'circle.png', key: "circle" }
        ], loadCallback = function () {
            completeCount++;

            if (currentLoadIndex < imageRes.length - 1)
                loadImage(imageRes[++currentLoadIndex], false, loadCallback, errorCallback);

            allTexture[this.getAttribute("data-key")] = new PIXI.Texture(new PIXI.BaseTexture(this));

            if (completeCount == imageRes.length)
                completeCallback();
        }, errorCallback = function () {
            var errorCount = this.getAttribute("data-error") || "0";

            errorCount = parseInt(errorCount) + 1;

            if (errorCount < allowErrorCount && !isError) {
                loadImage(this, true, loadCallback, errorCallback);
                this.setAttribute("data-error", errorCount);
            } else if (!isError) {
                isError = true;
                tools.alert("图片加载失败，请刷新或关闭再打开页面重试！");
            }
        };

        for (var i = 0; i < singleLoadCount && i < imageRes.length; i++) {
            loadImage(imageRes[i], false, loadCallback, errorCallback);

            currentLoadIndex = i;
        }
    }

    // 加载单个图片资源
    function loadImage(obj, isAddRandom, loadCallback, errorCallback) {
        var url, imgDom, reset = function (callback, imgDom) {
            callback.call(imgDom);

            if (imgDom.parentElement)
                imgDom.parentElement.removeChild(imgDom);
        };

        if (obj.key) {
            if (!obj.path.indexOf("http://") || !obj.path.indexOf("https://") || !obj.path.indexOf("//"))
                url = obj.path;
            else
                url = resBasePath + obj.path;

            imgDom = document.createElement("img");
            imgDom.crossOrigin = "";
            imgDom.className = "img-load";
            imgDom.setAttribute("data-key", obj.key);

            imgDom.addEventListener("load", function () {
                reset(loadCallback, this);
            });

            imgDom.addEventListener("error", function () {
                reset(errorCallback, this);
            });

            document.body.appendChild(imgDom);
        } else {
            url = obj.src;
            imgDom = obj;
        }

        imgDom.src = getUrl(url, isAddRandom);
    }

    // 获取地址
    function getUrl(url, isAddRandom) {
        if (isAddRandom) {
            url = url.replace(/[&|?]rd=\d+/g, "");

            var splitChar = "?";

            if (url.indexOf("?") > 0)
                splitChar = "&";

            url += splitChar + "rd=" + new Date().getTime();
        }

        return url;
    }
})();