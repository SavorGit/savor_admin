var on_off,_this;
var numa = 1;
var host_name = $("#honame").val();
$('.zt-group').on('click','.btn-wz',function(){
	_this = $(this);

	var ss = _this.parent().next().prop('class');
	on_off = 0;
	if(ss=='zt_gropu_nr'){
		on_off = 1;

	}else if(ss==undefined){
		on_off = 2;
		$('#mode_textare').val('')
		$('#bt_textare').val('');

	}
	return on_off,_this
})
/*-------------------------添加文字--------------------*/
$('#wz_save').click(function(){
	var mode_textare = $('#mode_textare').val();
	if(mode_textare !=''){
		if(mode_textare.length<200){
			$('#tjwz').modal('hide');
			if(on_off==1){
				_this.parent().next().html(mode_textare+'<img class="zt_bianji" src="../../../../Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="../../../../Public/admin/assets/img/zt_shanc.png"/>')
				_this.parent().next().attr('leix','scontent');
				_this.parent().next().attr('data-call',mode_textare);
			}else if(on_off==2){
				adddiv(_this,mode_textare);
				_this.parent().next().attr('leix','scontent');
				_this.parent().next().attr('data-call',mode_textare);
			}
		}else{
			alert('字数已经超出200字')
		}
	}else{
		alert('请填写内容！')
	}
})
/*-------------------------添加小标题----------------------------------*/

$('#bt_save').click(function(){
	var bt_textare = $('#bt_textare').val();
	var bt_textares = '<p style="font-weight: 700;color:#922C3E;">'+bt_textare+'</p>';
	if(bt_textare !=''){
		if(bt_textare.length<100){
			$('#tjxbt').modal('hide');
			if(on_off==1){
				_this.parent().next().html('<p style="font-weight: 700;color:#922C3E;">'+bt_textare+'</p><img class="zt_bianji" src="../../../../Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="../../../../Public/admin/assets/img/zt_shanc.png"/>');
				_this.parent().next().attr('leix','stitle');
				_this.parent().next().attr('data-call',bt_textare);
			}else if(on_off==2){
				adddiv(_this,bt_textares);
				_this.parent().next().attr('leix','stitle');
				_this.parent().next().attr('data-call',bt_textare);
			}
		}else{
			alert('字数已经超过100字')
		}
	}else{
		alert('请填写内容！')
	}
})

/*-------------------------------添加图片--------------------------*/

$('body').off('click').on('click','.dz-check span,#savemapImg',function(){
	var ssaa = _this.attr('btnnums');
	var zt_gropu_nridnex = $('.zt_gropu_nr').length;
	var imgHtml = '<a class="imga"  href="#" data-browse-file=""><img class="zt_tjpic" id="pics_map_'+ssaa+'img" src="" border="0"><input class="picas" type="hidden" value="" id="pics_map_'+ssaa+'" name="pics_map_'+ssaa+'"></a>'
	if(on_off==2){
		adddiv(_this,imgHtml);
		_this.parent().next().attr('leix','spid');
	}else{
		_this.parent().next().html(imgHtml+'<img class="zt_bianji" src="../../../../Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="../../../../Public/admin/assets/img/zt_shanc.png"/>')
	}

})
/*初始化文章*/
$('body').on('click','.btn-wz',function(){
	$('#search_val').val('');
	$('#search_wz ul').html('')
})
/*文章搜索*/
$('#search_btn').click(function(){
	var sear_val = $('#search_val').val();
	$('#search_wz ul').html('');
	$.ajax({
		type:"post",
		url:host_name+"/specialgroup/getArticleByname",
		async:true,
		data:"tagname="+sear_val,
		dataType:"json",
		success:function(call){
			console.log(call)
			for(var i=0;i<call.length;i++){
				var wz_html = '<li>'+call[i].name+' <input type="hidden" id="'+call[i].id+'" value="'+call[i].img_url+'"></li>'
				$('#search_wz ul').append(wz_html);
			}
		}

	});

})
/*选中*/
$('#search_wz').on('click','li',function(){
	$(this).addClass('active_li').siblings().removeClass('active_li');
})
/*选中文章保存*/
$('#wz_bc').click(function(){
	if($('#search_wz li').is('.active_li')){
		$('#tjwzh').modal('hide');
		var wz_div = '<div>'+$('#search_wz .active_li').text()+'</div>';
		var wz_div = '<div class="wz_yangshi"><img src="'+$('#search_wz ul li input').val()+'"/><p>'+$('#search_wz .active_li').text()+'</p></div><img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/>';
		if(on_off==1){
			_this.parent().next().html(wz_div);
			_this.parent().next().attr('leix','sarticle');
			_this.parent().next().attr('data-call',$('#search_wz li input').prop('id'));
		}else if(on_off==2){
			adddiv(_this,wz_div);
			_this.parent().next().attr('leix','sarticle');
			_this.parent().next().attr('data-call',$('#search_wz li input').prop('id'));
		}
	}else{
		alert('请选择文章！！！')
	}
})
/*保存*/
$('#qb_save').click(function(){
	var arr = [];
	var zt_gropu_nridnex = $('.zt_gropu_nr').length;
	if(zt_gropu_nridnex<1){
		alert('专题组的数量不得少于5个');
	}else{
		$('.zt_gropu_nr').each(function(){
			var asss = $(this).find('a').children('input').val();
			if($(this).attr('leix')=='spid'){
				$(this).attr('data-call',asss);
			}
			var obj = {};
			var oData = $(this).attr('data-call');
			var oLeix = $(this).attr('leix');
			var oData = $(this).attr('data-call');

			obj[oLeix] = oData;
			arr.push(obj);
		})
		var arr_nrs = JSON.stringify(arr);
		console.log(arr_nrs);
		$('#savespgroup').val(arr_nrs);
	}
});


$("#editspecialgrrr").on("click",".btn .btn-default",function(){
	$("#qb_save").prop('disabled','');
	$("#qb_save").removeClass('disabled');
	//alert('werwewer');

});

/*编辑*/
$('body').on('click','.zt_bianji',function(){
	var aLeix = $(this).parent().attr('leix');
	$("#qb_save").prop('disabled','');
	$("#qb_save").removeClass('disabled');

	switch(aLeix){
		case 'scontent':
			$(this).parent().prev().find('.btn-wz').eq(0).click();
			var data_call = $(this).parent().attr('data-call');
			$('#mode_textare').val(data_call)
			break;
		case 'sarticle':
			$(this).parent().prev().find('.btn-wz').eq(1).click();
			break;
		case 'spid':
			$(this).parent().prev().find('.btn-wz').eq(2).click();
			break;
		case 'stitle':
			$(this).parent().prev().find('.btn-wz').eq(3).click();
			var data_call = $(this).parent().attr('data-call');
			$('#bt_textare').val(data_call)
			break;
	}
})
/*删除*/
$('body').on('click','.zt_del',function(){
	if(confirm('是否删除当前内容')){
		$(this).parent().prev().remove()
		$(this).parent().remove();
	}
})
/*------------------专题简介字数-----------------------*/
$('#zt_jianjie').keyup(function() {
	var textval = $(this).val().length;
	if(textval >= 200) {
		alert ('您已超出200个字');
		$(this).val($(this).val().substring(0, 199));
	}
})
 document.onkeypress=function(){
    if(event.keyCode==13){
   return false;
}
  }
 /*预览封面图*/
$('#media_idimg').click(function(){
	    var media_id = $('#media_id').val();
	    if(media_id !='' ){
	  	  var $a = $(this).attr('src');
	        $('.big').prop('src',$a).addClass('addbig');
	        $('.big').css({'max-width':'500px'})
	        $('.zhezhao').show();
	        $('.big').show(); 
	    }
	    
	});
$('.zhezhao').click(function(){

      $('.zhezhao').hide(500);
      $('.big').hide(500);
});
/*添加节点*/
var numsa = 2
function adddiv(jd_this,nr){
	var nums = numsa++
	//var zt_gropu_nridnex = $('.zt_gropu_nr').length;
	var aHtml = '<div class="zt_gropu_nr" >'+nr+'<img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/></div><div class="zt-btn_group"><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwz">添加文字</button><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwzh">添加文章</button><button type="button" btnnums = "'+(nums)+'" class="btn btn-default btn-wz" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(nums)+'&rtype=2" data-browse-file>添加图片</button><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjxbt">添加小标题</button></div>'
	var ajd = jd_this.parent();
	ajd.after(aHtml);
	//aHtml.insertAfter(ajd);
}
