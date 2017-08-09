

    wx.config({
        debug: false,
        appId: $("#appid").val(),
        timestamp: $("#timestamp").val(),
        nonceStr: $("#noncestr").val(),
        signature: $("#signature").val(),
        jsApiList: ["onMenuShareTimeline","onMenuShareAppMessage","checkJsApi"]
    }),
    wx.ready(function(){
        wx.checkJsApi({
            jsApiList: ["onMenuShareTimeline"],
            success: function(){}
        }),
            wx.onMenuShareTimeline({
                title: $("#share_title").val(),
                link: $("#share_link").val(),
                imgUrl: $("#share_img").val(),
                trigger: function(){},
                success: function(){
                    if($("#jump_link").val()){
                        window.location.href = $("#jump_link").val();
                    }
                },
                cancel: function(){},
                fail: function(){}
            }),
            wx.onMenuShareAppMessage({
                title: $("#share_title").val(),
                desc: $("#share_desc").val(),
                link: $("#share_link").val(),
                imgUrl: $("#share_img").val(),
                trigger: function(){},
                success: function(){
                    if($("#jump_link").val()){
                        window.location.href = $("#jump_link").val();
                    }
                },
                cancel: function(){},
                fail: function(){}
            })
    }),
    wx.error(function(){

    });
