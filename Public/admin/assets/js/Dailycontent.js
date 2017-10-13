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
		if(mode_textare.length<=1000){
			$('#tjwz').modal('hide');
			
				adddiv(_this,mode_textare);
				_this.parent().next().attr('leix','scontent');
				_this.parent().next().attr('data-call',mode_textare);
			
		}else{
			alert('字数已经超出1000字');
		}
	}else{
		alert('请填写内容！');
	}
})

/*数字编辑*/
$('.zt-group').on('click','.zt_bianji',function(){
	$("#qb_save").prop('disabled','');
	$("#qb_save").removeClass('disabled');
	bjimg = 2;
	return bjimg;
	})

/*保存*/
$('#qb_save').click(function(){
	var arr = [];
	var zt_gropu_nridnex = $('.zt_gropu_nr').length;
	if(zt_gropu_nridnex<1){
		//alert('内容的行数不得少于1行');
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
		$('#savedaily').val(arr_nrs);
		console.log($('#pictuji').val())
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
		if(mode_textare.length<=1000){
			$('#bjwz').modal('hide');
				_this2.html(mode_textare+'<img class="zt_bianji" src="../../../../Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="../../../../Public/admin/assets/img/zt_shanc.png"/>')
				_this2.attr('leix','scontent');
				_this2.attr('data-call',mode_textare);
			
		}else{
			alert('字数已经超出1000字');
		}
	}else{
		alert('请填写内容！');
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
	var zt_btn_groupidnex = $('.zt-btn_group').length;//按钮
	var zt_gropu_nridnex = $('.zt_gropu_nr').length;//内容

	console.log(zt_gropu_nridnex);
	var bj_imgbtn_html = '<button type="button" btnnums="'+(zt_gropu_nridnex+2)+'" style="display:none" id="bj_imgbtn" class="btn btn-default btn-wz" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(zt_gropu_nridnex+2)+'&rtype=2" data-browse-file>添加图片</button>';
	if(bjimg==2){
		var ssaa2 = _this2.find('button').attr('btnnums');
		var imgHtml = ''+bj_imgbtn_html+'<a class="imga"  href="#" data-browse-file=""><img class="zt_tjpic" id="pics_map_'+ssaa2+'img" src="" border="0"><input class="picas" type="hidden" value="" id="pics_map_'+ssaa2+'" name="pics_map_'+ssaa2+'"></a>'
		var aHtml = '<div class="zt_gropu_nr" >'+imgHtml+'<img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/></div><div class="zt-btn_group"><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwz">添加文字</button><button type="button" btnnums = "'+(zt_gropu_nridnex+2)+'" class="btn btn-default btn-wz tianimg" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(zt_gropu_nridnex+2)+'&rtype=2" data-browse-file>添加图片</button></div>'
		var ajd = _this.parent();
		ajd.after(aHtml);
		_this.parent().next().attr('leix','spid');
		_this2.next().next().next().remove();
		_this2.remove();
		//_this2.next().next().remove();
		//_this2.next().remove();
	}else if(bjimg==3){
		var ssaa = _this.attr('btnnums');
		var imgHtml = ''+bj_imgbtn_html+'<a class="imga"  href="#" data-browse-file=""><img class="zt_tjpic" id="pics_map_'+ssaa+'img" src="" border="0"><input class="picas" type="hidden" value="" id="pics_map_'+ssaa+'" name="pics_map_'+ssaa+'"></a>'
		var aHtml = '<div class="zt_gropu_nr" >'+imgHtml+'<img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/></div><div class="zt-btn_group"><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwz">添加文字</button><button type="button" btnnums = "'+(zt_gropu_nridnex+2)+'" class="btn btn-default btn-wz tianimg" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(zt_gropu_nridnex+2)+'&rtype=2" data-browse-file>添加图片</button></div>'
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
	var zt_gropu_nridnex = $('.zt_gropu_nr').length;
	var nums = numsa++
	//var zt_gropu_nridnex = $('.zt_gropu_nr').length;
	var aHtml = '<div class="zt_gropu_nr" >'+nr+'<img class="zt_bianji" src="/Public/admin/assets/img/zt_bianji.png"/><img class="zt_del" src="/Public/admin/assets/img/zt_shanc.png"/></div><div class="zt-btn_group"><button type="button" class="btn btn-default btn-wz" data-toggle="modal" href="#tjwz">添加文字</button><button type="button" btnnums = "'+(zt_gropu_nridnex+2)+'" class="btn btn-default btn-wz tianimg" data-target="#modal-file" href="'+host_name+'/resource/uploadMapResource?filed=pics_map_'+(zt_gropu_nridnex+2)+'&rtype=2" data-browse-file>添加图片</button></div>'
	var ajd = jd_this.parent();
	ajd.after(aHtml);
	//aHtml.insertAfter(ajd);
}

	/*标签*/
$('#yes').click(function(){
		var biaoname = $('#exampleInputName2').val();
		if(biaoname==''){
			alert('请添加标签名称!');
			return false;
		}else if(biaoname.length==1){
			alert('输入少于2个字符，请重新输入！');
			return false;
		}else if(biaoname.length>15){
			alert('最多只能输入15个字符，请重新输入！');
			return false;
		}else{
			$.ajax({
				type:"POST",
				url:host_name+"/tag/doAddAjaxTag",
				dataType: "json",
				data:"tagname="+biaoname,
				async:true,
				success:function(data){
					console.log(data);
					if(data.code==0){
						alert(data.err_msg);
						$('#exampleInputName2').val('');
					}else{
						console.log(data);
						var biaohtml = '<div class="xuan a" id="'+data.aid+'">'+biaoname+'<i class="fa fa-close marg"></i></div>';
						$('.nr').append(biaohtml)
						$('#exampleInputName2').val('');
					}
				}
			});


		}
	})
	$('.quanbu_nr').on('click','.xuan',function(){
		//$(this).hide();
		var biaoname = $(this).val();
		var biaoid = $(this).prop('id');
		$(this).addClass('actives');
		$(this).prop('disabled',true);
		/*$('.nr .xuan').each(function(i){

		 //console.log($('.nr .xuan').eq(i).prop('id'))
		 if(biaoid==$(this).prop('id')){
		 alert('已经添加过了')
		 $(this).last().remove();
		 }else{

		 var ahtml = '<div class="xuan a" id="'+biaoid+'">'+biaoname+'<i class="fa fa-close marg"></i></div>';
		 $('.nr').append(ahtml);
		 }
		 })*/
		//if($('.nr .xuan').length==0){
		var ahtml = '<div class="xuan a" id="'+biaoid+'">'+biaoname+'<i class="fa fa-close marg"></i></div>';
		$('.nr').append(ahtml);
		//}

	})
	$('.nr').on('click','.xuan .marg',function(){
		$(this).parent().remove();
		var biaotext = $(this).parent().text();
		//$('.quanbu_nr .xuan').val(biaotext).prop('disabled',false);
		$('.quanbu_nr .xuan').each(function(){

			if($(this).val()==biaotext){
				$(this).prop('disabled',false);
				$(this).removeClass('actives')
			}
		})
	})

	$('#yes2').click(function(){
		//$('.quanbu_nr').html();
		var arr_nr = [];
		$('.biaolist').html('');
		$('.biaolist').append($('.nr').html())
		$('.biaolist').find('i').hide()
		$('.nr .a').each(function(){
			var obj_nr = {};
			var aid = $(this).prop('id');
			var aname = $.trim($(this).text());
			obj_nr.tagid = aid;
			obj_nr.tagname = aname;
			arr_nr.push(obj_nr);
		})
		console.log(JSON.stringify(arr_nr));
		var arr_nrs = JSON.stringify(arr_nr);
		$('#dailyre').val(arr_nrs)
		$("#addartbutton").prop('disabled','');
		$("#addartbutton").removeClass('disabled');
	})

	var options = {
		currentPage: 1,//显示当前页数
		totalPages: $("#pagetotal").val(),  //总页数
		numberOfPages:5,  //显示几页
		onPageClicked: function (event, originalEvent, type, page) {
			//alert(page);
			$('.quanbu_nr').html('');

			$.ajax({
				type:"get",
				url:host_name+"/tag/getajaxpage",
				dataType: "json",
				data:"pageNum=" + page,
				async:true,
				success:function(call){
					console.log(call)
					var calls = call.list;
					if(call.page!=0){
						$('#pagenum').val(call.page)
						for(var i=0;i<calls.length;i++){

							var quanbu_nrs = '<input class="xuan baise" type="button" value="'+calls[i].tagname+'" id="'+calls[i].id+'" tname="'+calls[i].tagname+'" />';
							$('.quanbu_nr').append(quanbu_nrs);
						}
						activer();
					}

				}
			});
		}
	}

	$('#example').bootstrapPaginator(options);


	/*搜索*/
	$('#sdd').click(function() {
		$('#pagetotal').val('1');
		var soushuo = $('.soushuo').val();
		$('.quanbu_nr').html('');
		console.log(soushuo)
		$.ajax({
			type: "post",
			url: host_name+"/tag/getajaxpage",
			dataType: "json",
			data: "fatagname=" + soushuo + "&pageNum=1",
			async: true,
			success: function(call) {
				console.log(call)
				var calls = call.list;
				if(calls != '') {

					for(var i = 0; i < calls.length; i++) {
						var quanbu_nrs = '<input class="xuan baise" type="button" value="' + calls[i].tagname + '" id="' + calls[i].id + '" tname="' + calls[i].tagname + '" />';
						$('.quanbu_nr').append(quanbu_nrs);
					}

					activer()
					var options = {
						currentPage: 1, //显示当前页数
						totalPages: call.page, //总页数
						numberOfPages: 5, //显示几页
						onPageClicked: function(event, originalEvent, type, page) {
							//alert(page);
							$('.quanbu_nr').html('');

							var opage = {};
							opage.pageNum = page;
							opage.fatagname = soushuo;

							$.ajax({
								type: "get",
								url: host_name+"/tag/getajaxpage",
								dataType: "json",
								data: opage,
								async: true,
								success: function(call) {
									//console.log(call)
									var calls = call.list;
									$('#pagenum').val(call.page)
									for(var i = 0; i < calls.length; i++) {
										var quanbu_nrs = '<input class="xuan baise" type="button" value="' + calls[i].tagname + '" id="' + calls[i].id + '" tname="' + calls[i].tagname + '" />';
										$('.quanbu_nr').append(quanbu_nrs);
									}
									activer();
								}
							});
						}
					}
					$('#example').bootstrapPaginator(options);
					$('#example').show()
				}else{
					$('#example').hide()
				}
			}
		});
	})




	$('#tianjia').click(function(){

		$('.nr').html($('.biaolist').html())
		$('.nr i').show();
		activers();
	})

	function activer(){
		$('.nr .a').each(function(){
			var dis = $(this).prop('id');
			$('.quanbu_nr .xuan').each(function(){
				if($(this).prop('id')==dis){
					$(this).addClass('actives');
					$(this).prop('disabled',true);
					console.log(dis)
				}

			})

		})
	}
	function activers(){
		$('.biaolist .a').each(function(){
			var dis = $(this).prop('id');
			$('.quanbu_nr .xuan').each(function(){
				if($(this).prop('id')==dis){
					$(this).addClass('actives');
					$(this).prop('disabled',true);
					console.log(dis)
				}

			})

		})
	}

	$('#nos').click(function(){
		$('.quanbu_nr .xuan').each(function(){
			$(this).removeClass('actives');
			$(this).prop('disabled',false);
		})
	})
