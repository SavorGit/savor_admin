var on_off,_this,_this2;
var numa = 1;
var bjimg = 1;
var host_name = $("#honame").val();
$('.zt-group').on('click','.btn-wz',function(){
	
	_this = $(this);
	$('#tjwz #mode_textare').val('');
	$('#tjxbt #bt_textare').val('');
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
$('.zt-group').on('click','.tianimg',function(){
	bjimg = 3;
	return bjimg;
})
/*-------------------------添加文字--------------------*/
$('#tjwz #wz_save').click(function(){
	var mode_textare = $('#mode_textare').val();
	if(mode_textare !=''){
		if(mode_textare.length<=200){
			$('#tjwz').modal('hide');
			
				adddiv(_this,mode_textare);
				_this.parent().next().attr('leix','scontent');
				_this.parent().next().attr('data-call',mode_textare);
			
		}else{
			alert('字数已经超出200字');
		}
	}else{
		alert('请填写内容！');
	}
})

/*-------------------------添加小标题----------------------------------*/
$('#bt_save').click(function(){
	var bt_textare = $('#bt_textare').val();
	var bt_textares = '<p style="font-weight: 700;color:#922C3E;">'+bt_textare+'</p>';
	if(bt_textare !=''){
		if(bt_textare.length<=100){
			$('#tjxbt').modal('hide');
				adddiv(_this,bt_textares);
				_this.parent().next().attr('leix','stitle');
				_this.parent().next().attr('data-call',bt_textare);
		}else{
			alert('字数已经超过100字');
		}
	}else{
		alert('请填写内容！')
	}
})
/*数字编辑*/
$('.zt-group').on('click','.zt_bianji',function(){
	$("#qb_save").prop('disabled','');
	$("#qb_save").removeClass('disabled');
	bjimg = 2;
	return bjimg;
	})


/*初始化文章*/
$('.zt-group').on('click','.btn-wz',function(){
	$('#tjwzh #search_val').val('');
	$('#tjwzh #search_wz ul').html('');
})
/*文章搜索*/
$('#tjwzh #search_btn').click(function(){
	var sear_val = $('#tjwzh #search_val').val();
	$('#tjwzh #search_wz ul').html('');
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
				$('#tjwzh #search_wz ul').append(wz_html);
			}
		}
	});
})
/*选中*/
$('#tjwzh #search_wz').on('click','li',function(){
	$(this).addClass('active_li').siblings().removeClass('active_li');
})
/*选中文章保存*/
$('#tjwzh #wz_bc').click(function(){
	if($('#tjwzh #search_wz li').is('.active_li')){
		$('#tjwzh').modal('hide');
		//var wz_div = '<div>'+$('#search_wz .active_li').text()+'</div>';
		var wz_div = '<div class="wz_yangshi"><img src="'+$('#tjwzh #search_wz ul .active_li input').val()+'"/><p>'+$('#tjwzh #search_wz .active_li').text()+'</p></div><img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/>';
		
			adddiv(_this,wz_div);
			_this.parent().next().attr('leix','sarticle');
			_this.parent().next().attr('data-call',$('#tjwzh #search_wz .active_li input').prop('id'));
		
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
$('.zt-group').on('click','.zt_bianji',function(){
	_this2 = $(this).parent();
	var aLeix = $(this).parent().attr('leix');
	$("#qb_save").prop('disabled','');
	$("#qb_save").removeClass('disabled');

	switch(aLeix){
		case 'scontent':
			var data_call = $(this).parent().attr('data-call');
			$('#bjwz #mode_textare').val(data_call);
			$('#bjwz').modal('show');
			break;
		case 'sarticle':
			$('#bjwzh').modal('show');
			break;
		case 'spid':
		_this2.find('#bj_imgbtn').click();
		//$(this).parent().prev().find('.btn-wz').eq(2).click();
			break;
		case 'stitle':
			var data_call = $(this).parent().attr('data-call');
			$('#bjxbt #bt_textare').val(data_call);
			$('#bjxbt').modal('show');
			break;
	}
	return _this2;
})
/*删除*/
$('.zt-group').on('click','.zt_del',function(){
	$("#qb_save").prop('disabled','');
	$("#qb_save").removeClass('disabled');
	if(confirm('是否删除当前内容')){
		$(this).parent().prev().remove()
		$(this).parent().remove();
	}
})
/*-------------------------编辑文字------------------------*/
$('#bjwz #bj_wz_save').click(function(){
	var mode_textare = $('#bjwz #mode_textare').val();
	if(mode_textare !=''){
		if(mode_textare.length<=200){
			$('#bjwz').modal('hide');
				_this2.html(mode_textare+'<img class="zt_bianji" src="../../../../Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="../../../../Public/admin/assets/img/zt_shanc.png"/>')
				_this2.attr('leix','scontent');
				_this2.attr('data-call',mode_textare);
			
		}else{
			alert('字数已经超出200字');
		}
	}else{
		alert('请填写内容！');
	}
})
/*-------------------------编辑小标题----------------------------------*/
$('#bjxbt #bj_bt_save').click(function(){
	var bt_textare = $('#bjxbt #bt_textare').val();
	var bt_textares = '<p style="font-weight: 700;color:#922C3E;">'+bt_textare+'</p>';
	if(bt_textare !=''){
		if(bt_textare.length<=100){
			$('#bjxbt').modal('hide');
				_this2.html('<p style="font-weight: 700;color:#922C3E;">'+bt_textare+'</p><img class="zt_bianji" src="../../../../Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="../../../../Public/admin/assets/img/zt_shanc.png"/>');
				_this2.attr('leix','stitle');
				_this2.attr('data-call',bt_textare);
		}else{
			alert('字数已经超过100字');
		}
	}else{
		alert('请填写内容！')
	}
})
/*编辑文章搜索*/
$('#bjwzh #search_btn').click(function(){
	var sear_val = $('#bjwzh #search_val').val();
	$('#bjwzh #search_wz ul').html('');
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
				$('#bjwzh #search_wz ul').append(wz_html);
			}
		}
	});
})
/*编辑选中*/
$(' #bjwzh #search_wz').on('click','li',function(){
	$(this).addClass('active_li').siblings().removeClass('active_li');
})
/*编辑选中文章保存*/
$('#bjwzh #bj_wz_bc').click(function(){
	if($('#bjwzh #search_wz li').is('.active_li')){
		$('#bjwzh').modal('hide');
		//var wz_div = '<div>'+$('#search_wz .active_li').text()+'</div>';
		var wz_div = '<div class="wz_yangshi"><img src="'+$('#bjwzh #search_wz ul .active_li input').val()+'"/><p>'+$('#bjwzh #search_wz .active_li').text()+'</p></div><img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/>';
		
			_this2.html(wz_div);
			_this2.attr('leix','sarticle');
			_this2.attr('data-call',$('#bjwzh #search_wz .active_li input').prop('id'));
	}else{
		alert('请选择文章！！！')
	}
})
$('#fengmian').click(function(){
	bjimg=1;
})
/*-------------------------------添加图片--------------------------*/
var numsa = 2;
$('body').off('click').on('click','.dz-check span,#savemapImg',function(){
	$("#qb_save").prop('disabled','');
	$("#qb_save").removeClass('disabled');
	/*var nums = numsa++;*/
	var ssaa = _this.attr('btnnums');
	var zt_btn_groupidnex = $('.zt-btn_group').length;//按钮
	var zt_gropu_nridnex = $('.zt_gropu_nr').length;//内容
	console.log(zt_gropu_nridnex);
	var bj_imgbtn_html = '<button type="button" btnnums="'+(zt_gropu_nridnex+2)+'" style="display:none" id="bj_imgbtn" class="btn btn-default btn-wz" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(zt_gropu_nridnex+2)+'&rtype=2" data-browse-file>添加图片</button>';
	if(bjimg==2){
		var ssaa2 = _this2.find('button').attr('btnnums');
		var imgHtml = ''+bj_imgbtn_html+'<a class="imga"  href="#" data-browse-file=""><img class="zt_tjpic" id="pics_map_'+ssaa2+'img" src="" border="0"><input class="picas" type="hidden" value="" id="pics_map_'+ssaa2+'" name="pics_map_'+ssaa2+'"></a>'
		var aHtml = '<div class="zt_gropu_nr" >'+imgHtml+'<img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/></div><div class="zt-btn_group"><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwz">添加文字</button><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwzh">添加文章</button><button type="button" btnnums = "'+(zt_gropu_nridnex+2)+'" class="btn btn-default btn-wz tianimg" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(zt_gropu_nridnex+2)+'&rtype=2" data-browse-file>添加图片</button><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjxbt">添加小标题</button></div>'
		var ajd = _this.parent();
		ajd.after(aHtml);
		_this.parent().next().attr('leix','spid');
		_this2.next().next().next().remove();
		_this2.remove();
		//_this2.next().next().remove();
		//_this2.next().remove();
	}else if(bjimg==3){
		var imgHtml = ''+bj_imgbtn_html+'<a class="imga"  href="#" data-browse-file=""><img class="zt_tjpic" id="pics_map_'+ssaa+'img" src="" border="0"><input class="picas" type="hidden" value="" id="pics_map_'+ssaa+'" name="pics_map_'+ssaa+'"></a>'
		var aHtml = '<div class="zt_gropu_nr" >'+imgHtml+'<img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/></div><div class="zt-btn_group"><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwz">添加文字</button><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwzh">添加文章</button><button type="button" btnnums = "'+(zt_gropu_nridnex+2)+'" class="btn btn-default btn-wz tianimg" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(zt_gropu_nridnex+2)+'&rtype=2" data-browse-file>添加图片</button><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjxbt">添加小标题</button></div>'
		var ajd = _this.parent();
		ajd.after(aHtml);
		_this.parent().next().attr('leix','spid');
		}
})
/*------------------专题简介字数-----------------------*/
$('#zt_jianjie').keyup(function() {
	var textval = $(this).val().length;
	if(textval >= 201) {
		alert ('您已超出200个字');
		$(this).val($(this).val().substring(0, 200));
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
	        $('.big').css({'max-width':'500px'});
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
	var aHtml = '<div class="zt_gropu_nr" >'+nr+'<img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/></div><div class="zt-btn_group"><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwz">添加文字</button><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwzh">添加文章</button><button type="button" btnnums = "'+(nums)+'" class="btn btn-default btn-wz tianimg" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(nums)+'&rtype=2" data-browse-file>添加图片</button><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjxbt">添加小标题</button></div>'
	var ajd = jd_this.parent();
	ajd.after(aHtml);
	//aHtml.insertAfter(ajd);
}


