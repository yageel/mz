(function () {
    tools.guideInit = function (key, steps) {
        if (!tools.storage(key)) {
            var guide = $(document.createElement("div")).addClass("abs100").css("zIndex", 999);

            setStep(guide, steps, 0);
            
            guide.appendTo();

            tools.storage(key, true);
        }
    };

    function setStep(guide, steps, idx) {
        var step = steps[idx];

        step.location = step.location || "center center";
        step.imgSize = step.imgSize || "auto auto";

        guide.css({
            background: "rgba(0,0,0,.4) url(" + siteConfig.PUBLIC + "/images/guide/" + step.img + ") no-repeat " + step.location,
            backgroundSize: step.imgSize
        });

        $.each(step.buttons, function () {
            var me = this,
                button = $(document.createElement("i"));

            button.css({
                width: me.width || "100%",
                height: me.height || "100%",
                left: me.left || "0",
                top: me.top || "0",
                position: "absolute"
            });

            button.click(function () {
                if ($.isFunction(me.click)) {
                    if (me.click(guide) !== false)
                        nextStep(guide, steps, idx);
                } else {
                    nextStep(guide, steps, idx);
                }
            });

            guide.append(button);
        });
    }

    function nextStep(guide, steps, idx) {
        if (idx < steps.length - 1)
            setStep(guide.html(""), steps, idx + 1);
        else
            guide.remove();
    }
})();