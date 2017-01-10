$(function($){
    $(document).on("click","[data-browse-file]", function(e){
        e.preventDefault();

        var parents = $(this).closest("[data-fileinput]");
        var target = $(this).data("target");
        console.log(parents);
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
                    alertMsg.alert("你还没选择图片！");
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
    $(".modal").on('show.bs.modal',function(){
        if($(this).find(".modal-table").length > 0){
            $(this).find(".modal-table").css("min-height",$(window).height());
        }
    })
    $(".modal-file").on('shown.bs.modal',function(){
        initUI($(this));
    })

});