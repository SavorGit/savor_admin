function getExt(v){
    var split = v.split('.');
    var sl = split.length;
    var ext = split[sl-1].toLowerCase();
    return ext;
}
$(function(){
    $(document).on('keydown','[name="sitekeyword"],[name="shwkeywords"]',function(e) {
        if($(this).val().match(/,/g).length>6&&e.keyCode == 188) {
            e.preventDefault();
        }
    })
    function resizeScreen(){
        $("#screen option").each(function(){
            var v = $(this).attr("value");
            var w = $(this).data("width");
            var h = $(this).data("height");
            if($(window).width() <= h && v > 0 && v < 10){
                $(this).attr("landscape","false")
            }else{
                $(this).attr("landscape","true")
            }
            if($(window).width() <= w){
                $(this).addClass("hidden")
            }else{
                $(this).removeClass("hidden")
            }
        })
    }
    resizeScreen();
    var iPad = navigator.userAgent.match(/iPad/i) != null;
    if(iPad){
        $("#screen option").each(function(){
            var v = $(this).attr("value");
            if(v == 5 || v == 6){
                $(this).remove();
            }
        })
    }
    var iOS = /ipad|iphone|ipod/.test(navigator.userAgent.toLowerCase());
    var android = /android/.test(navigator.userAgent.toLowerCase());

    if(iOS){
        $("#web-view iframe").wrap("<div class='fix-ios-ifr'></div>");
    }
    if(!(iOS||android)) {
        $(document).on('mouseenter','[data-tip]',function() {
            $(this).tooltip('show');
        }).on('mouseleave',function() {
            $(this).tooltip('hide');
        })
    }
    $(window).resize(function(){
        resizeScreen();
    })
    $("#screen").on("change",function(){
        var v = $(this).val();
        $("#web-view").removeClass("landscape");
        if (v == 0){
            $(".screen-info").addClass("hidden");
            $(this).closest(".modal-content").attr("view","full");
            $("#web-view").removeAttr("style");
        }else{
            $(".screen-info").removeClass("hidden");
            var w = $(this).find(":selected").data("width");
            var h = $(this).find(":selected").data("height");
            $("#web-view").css({width:w+17,height:h});
            $(".screen-width").text(w);
            $(".screen-height").text(h);
            if (v > 0 && v < 10){
                var ld = $(this).find(":selected").attr("landscape");
                if(ld == "true"){
                    $(".screen-change").removeClass("hidden")
                }else{
                    $(".screen-change").addClass("hidden")
                }
                $(this).closest(".modal-content").attr("view","phone");
                
            }else if( v > 9 && v < 12){
                $(this).closest(".modal-content").attr("view","laptop");
                $(".screen-change").addClass("hidden")
            }
        }
        //console.log(v);
        var ifr = $(this).find("iframe");
        ifr.attr("src",ifr.attr("src"));
    })
    
    $(".screen-change").on("click",function(){
        var w = parseInt($(".screen-width").text());
        var h = parseInt($(".screen-height").text());
        $(".screen-width").text(h);
        $(".screen-height").text(w);
        $("#web-view").css({width:h+17,height:w});
        if($("#web-view").hasClass("landscape")){
            $("#web-view").removeClass("landscape");
        }else{
            $("#web-view").addClass("landscape");
        }
    })
    $(document).on("click","[data-toggle=view]",function(e){
        e.preventDefault();
        var href = $(this).attr("href");
        var tg = $(this).data("target");
        var ifr = $(tg).find("iframe");
        ifr.attr("src",href);
        $(tg).modal("show");
    })
    $("#modal-view").on("show.bs.modal",function(){
        var ld = $(this).find("#web-view");
        ld.addClass("ifr-loading");
        $(this).find(".modal-content").attr("view","full")
        $(this).find("iframe").load(function(){
            ld.removeClass("ifr-loading");
        })
    })
    $("#modal-view").on("hidden.bs.modal",function(){
        $(this).find("iframe").removeAttr("src")
        $("#web-view").removeAttr("style");
        $("#screen").val("0");
        $(".screen-info").addClass("hidden");
    })
    $(".modal").on("show.bs.modal",function(){
        //console.log($(this).attr("class"));
        modalWidth()
    })
    $(document).on("click","[data-browse-file]", function(e){
        e.preventDefault();

        var parents = $(this).closest("[data-fileinput]");
        var target = $(this).data("target");
        //console.log(parents);
        var url = $(this).attr("href");
        $(target+' .modal-content').load(url, function(){
            $(target).modal('show');
            $(this).find("[data-set-image]").click(function(){
                var src = $(this).data("src");
                if(src){
                    parents.find("input").val(src);
                    parents.find("img").attr('src',src);
                    $(target).modal('hide');
                }else{
                    alertMsg.error("你还没选择图片！");
                }
            })
        })
    })
    $(document).on("click","[data-remove-file]", function(e){
        var parents = $(this).closest("[data-fileinput]");
        var src = $(this).data("remove-file");
        parents.find("input").val('');
        parents.find("img").attr('src',src);
    });
    $(".modal-file").on('shown.bs.modal',function(){
        initUI($(this));
    })

    var host_name = $("#host_name").val();
    $('#modal-file-ueditor').on('show.bs.modal',function(){
        $(this).find('.modal-content').load(host_name+'/uploadmgr/uploadmgrList?browseFile=true&s_type=image&multiple=2')
    });
    $('#modal-file-ueditor').on('hidden.bs.modal',function(){
        $(this).find('.modal-content').html("");
    });

    $('#modal-attachfiles').on('show.bs.modal',function(){
        $(this).find('.modal-content').load(host_name+'/uploadmgr/uploadmgrList?browseFile=true&s_type=other&multiple=2')
    });
    $('#modal-attachfiles').on('hidden.bs.modal',function(){
        $(this).find('.modal-content').html("");
    });

    $(document).on('click','#modal-file-ueditor [data-set-image]',function(e){
        var img = $(this).data("src");
        var id = $("#modal-file-ueditor").data("target-ueditor");
        var ue = UE.getEditor(id);
        //console.log(id);
        if (img){

            if(Object.prototype.toString.call(img) === '[object Array]'){
                var obj = {html:""};
                $.each(img, function(k,v){
                    var im = '<p><img class="img-responsive" src="'+v+'"/></p>';
                    obj.html = obj.html.concat(im);
                })
            }else{
                var obj = {html:""};
                obj.html = '<img class="img-responsive" src="'+img+'"/>';
            }
            ue.execCommand('template', obj);
            $('#modal-file-ueditor').modal('hide');
            ifr = $(ue.container).find(".edui-editor-iframeholder iframe").contents();
            setTimeout(function(){
                $(".view",ifr).find(".carousel .item").each(function(){
                    var t = $(this).find("img");
                    var $this = $(this);
                    //t.html("<div style='height: 100px; background:url(/Public/admin/assets/img/loading-spinner-grey.gif) center center no-repeat'></div>");
                    setTimeout(function(){
                        $this.html(t)
                        if($this.hasClass("active")){
                            $this.find("img").trigger("click");
                        } 
                    },300)              
                                     
                    //$(this).append('\r');
                });
            },100)

            

        }else{
            alertMsg.error("你还没选择图片！");
        }
    })
    var pdf="fa-file-pdf-o",zip="fa-file-archive-o",doc="fa-file-word-o",xls="fa-file-excel-o",img="fa-file-image-o",audio="fa-file-audio-o",video="fa-file-video-o",ppt="fa-file-powerpoint-o",txt="fa-file-text-o",file="fa-file-o";
    var icons = {
        'pdf':pdf,'zip':zip,'rar':zip,'txt':txt,'doc':doc,'docx':doc,'ppt':ppt,'xls':xls,'xlsx':xls,'csv':xls,'jpg':img,'jpeg':img,'gif':img,'png':img,'bmp':img,'svg':img,'swf':file,'flv':video,'fla':file,'avi':video,'wmv':video,'wma':audio,'rm':video,'mov':video,'mpg':video,'rmvb':video,'3gp':video,'mp4':video,'mp3':audio
    };
    $(document).on('click','#modal-attachfiles [data-set-image]',function(e){
        var file = $(this).data("src");
        var name = $(this).data("name");
        var id = $("#modal-attachfiles").data("target-ueditor");
        var ue = UE.getEditor(id);
        //console.log(id);
        if (file){

            if(Object.prototype.toString.call(file) === '[object Array]'){
                var obj = {html:""};
                $.each(file, function(k,v){
                    var ext = getExt(v);
                    var im = '<p><a class="attachfiles" href="'+v+'"><i class="filetype fa '+icons[ext]+'"></i>&nbsp;'+name[k]+'.'+ext+'</a></p>';
                    obj.html = obj.html.concat(im);
                })
            }else{
                var obj = {html:""};
                var ext = getExt(file);
                obj.html = '<p><a class="attachfiles" href="'+file+'"><i class="filetype fa '+icons[ext]+'"></i>&nbsp;'+name+'.'+ext+'</a></p>';
            }
            ue.execCommand('template', obj);
            $('#modal-attachfiles').modal('hide');           
        }else{
            alertMsg.error("你还没选择文件！");
        }
    })
    UE.registerUI('image', function(editor, uiName) {
        //注册按钮执行时的command命令，使用命令默认就会带有回退操作
        editor.registerCommand(uiName, {
            execCommand: function(e,v) { 
                $('#modal-file-ueditor').modal('show');
                //var id = $(this.target).closest(".ueditor-init").attr("id");
                //console.log(v);
                $('#modal-file-ueditor').data("target-ueditor",v);
            }
        });
        //创建一个button
        var btn = new UE.ui.Button({
            //按钮的名字
            name: uiName,
            //提示
            title: "图片上传",
            //添加额外样式，指定icon图标，这里默认使用一个重复的icon
            cssRules: 'background-position: -380px 0;',
            //点击时执行的命令
            onclick: function(e,v) {   
                      
                $('#modal-file-ueditor').modal('show');
                var id = $(this.target).closest(".ueditor-init").attr("id");
                //console.log(id);
                $('#modal-file-ueditor').data("target-ueditor",id);
                //这里可以不用执行命令,做你自己的操作也可
            }
        });
        //当点到编辑内容上时，按钮要做的状态反射
        editor.addListener('selectionchange', function() {
            var state = editor.queryCommandState(uiName);
            if (state == -1) {
                btn.setDisabled(true);
                btn.setChecked(false);
            } else {
                btn.setDisabled(false);
                btn.setChecked(state);
            }
        });
        //因为你是添加button,所以需要返回这个button
        return btn;
    });
    UE.registerUI('attachfiles', function(editor, uiName) {
        //注册按钮执行时的command命令，使用命令默认就会带有回退操作
        editor.registerCommand(uiName, {
            execCommand: function(e,v) { 
                $('#modal-attachfiles').modal('show');
                //var id = $(this.target).closest(".ueditor-init").attr("id");
                //console.log(v);
                $('#modal-attachfiles').data("target-ueditor",v);
            }
        });
        //创建一个button
        var btn = new UE.ui.Button({
            //按钮的名字
            name: uiName,
            //提示
            title: "附件",
            //添加额外样式，指定icon图标，这里默认使用一个重复的icon
            cssRules: 'background-position: -620px -40px;',
            //点击时执行的命令
            onclick: function(e,v) {   
                      
                $('#modal-attachfiles').modal('show');
                var id = $(this.target).closest(".ueditor-init").attr("id");
                //console.log(id);
                $('#modal-attachfiles').data("target-ueditor",id);
                //这里可以不用执行命令,做你自己的操作也可
            }
        });
        //当点到编辑内容上时，按钮要做的状态反射
        editor.addListener('selectionchange', function() {
            var state = editor.queryCommandState(uiName);
            if (state == -1) {
                btn.setDisabled(true);
                btn.setChecked(false);
            } else {
                btn.setDisabled(false);
                btn.setChecked(state);
            }
        });
        //因为你是添加button,所以需要返回这个button
        return btn;
    });
    Array.prototype.max = function() {
      return Math.max.apply(null, this);
    };
    Array.prototype.min = function() {
      return Math.min.apply(null, this);
    };
    (function() {
      function decimalAdjust(type, value, exp) {
        if (typeof exp === 'undefined' || +exp === 0) {
          return Math[type](value);
        }
        value = +value;
        exp = +exp;
        if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
          return NaN;
        }
        value = value.toString().split('e');
        value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
        value = value.toString().split('e');
        return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
      }
      if (!Math.round10) {
        Math.round10 = function(value, exp) {
          return decimalAdjust('round', value, exp);
        };
      }
      if (!Math.floor10) {
        Math.floor10 = function(value, exp) {
          return decimalAdjust('floor', value, exp);
        };
      }
      if (!Math.ceil10) {
        Math.ceil10 = function(value, exp) {
          return decimalAdjust('ceil', value, exp);
        };
      }
    })();
});
