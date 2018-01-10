//标签属性
var honame = $('#honame').val();
$('#modebody').on('change','.rkId',function(){
	var pid = $(this).prop('value');
	var obj = {};
	obj.pId = pid;
	//var objid = 
	$.ajax({
		type:"post",
		url:honame+"/rtbadvert/getTagCat",
		data:"tid="+pid,
		dataType: "json",
		async:true,
		success:function(call){
			//console.log(call)
			if(call.code==0){
				var callinfo = call.tinfo;
				$('.njid').html('')
				for(var i=0;i<callinfo.length;i++){
					var ophtml = '<option value="'+callinfo[i].id+'">'+callinfo[i].tagname+'</option>'
					$('.njid').append(ophtml)
				}
			}else{
				alert('获取失败')
			}
		}
	});
})

//查询
$('#xuanzhebq_btn').click(function(){
	var njid = $('.njid').val();
	var serchbq_name = $('#serchbq-name').val();
	$.ajax({
		type:"post",
		url:honame+"/rtbadvert/getTagInfoByCat",
		data:"tid="+njid+"&tcode="+serchbq_name,
		async:true,
		dataType: "json",
		success:function(call){
			console.log(call);
			$('.bq-list').html('');
			var callinfo = call.tinfo;
			if(call.code==0){
				for(var i=0;i<callinfo.length;i++){
					var bqhtml = `
					<div class="bj-one">
					<input type="checkbox" class="bj-ones" id="${callinfo[i].id}">
					<label class="bj-ones-name" for="${callinfo[i].id}">${callinfo[i].tagname}</label>
					</div>
					`
					$('.bq-list').append(bqhtml)
				}
			}else{
				alert('搜索出错！')
			}
		}
	});
	
})
//返回已选标签
$('#yesbq_btn').click(function(){
	if($('.acitve-position-lists-bq').html() == '') {
			$('.bj-one').each(function() {
				if($(this).find('input').prop('checked') == true) {
					let jiulou_name = `
						<div id="${$(this).find('input').prop('id')}" class='position-list-group-bq'>
						<span class='acitve-position-jnamebq'>${$(this).find('label').html()}</span>
						<span class="acitve-position-delbq">x</span>
						</div>
						`
					$('.acitve-position-lists-bq').append(jiulou_name);
			
				}
			})
			
			$('.biaoqianshuliang').html($('.position-list-group-bq').length);
	}else{
			$('.bj-one').each(function() {
				if($(this).find('input').prop('checked') == true) {
					var this_name = $(this).find('label').html();
					var _thiss = $(this);
					$('.position-list-group-bq').each(function() {
						if($(this).find('.acitve-position-jnamebq').html() == this_name) {
							$(this).remove();
						}
					})
					var jiulou_name = `
								<div id="${_thiss.find('input').prop('id')}" class='position-list-group-bq'>
								<span class='acitve-position-jnamebq'>${_thiss.find('label').html()}</span>
								<span class="acitve-position-delbq">x</span>
								</div>
								`
					$('.acitve-position-lists-bq').append(jiulou_name);
				} else {
					var this_name = $(this).find('label').html();
					var _thiss = $(this);
					$('.position-list-group-bq').each(function() {
						if($(this).find('.acitve-position-jnamebq').html() == this_name) {
							$(this).remove();
						}
					})

				}
			})
		
			$('.biaoqianshuliang').html($('.position-list-group-bq').length)

		}
		
})
/*删除*/
$('.acitve-position-lists-bq').on('click','.acitve-position-delbq',function(){
	let this_del_html = $(this).parent().find('.acitve-position-jnamebq').html();
		$('.bj-one').each(function() {
			if($(this).find('label').html() == this_del_html) {
				$(this).find('input').prop('checked', false)
			}
		})
		$(this).parent().remove();

		$('.biaoqianshuliang').html($('.position-list-group-bq').length)
})
/*保存*/
$('#bq_bc').click(function(){
	if($('.acitve-position-lists-bq')!=''){
		var arr_bq = [];
		$('.biaolist').html('')
		$('.position-list-group-bq').each(function(){
			var obj_bq = {};
			var eachbq = `
			<div class="bqstyle" id="${$(this).prop('id')}">${$(this).find('.acitve-position-jnamebq').html()}</div>
			`
			var aid = $(this).prop('id');
			var bq_name = $(this).find('.acitve-position-jnamebq').html();
			obj_bq.tagid = aid;
			obj_bq.tagname = bq_name;
			arr_bq.push(obj_bq);
			$('.biaolist').append(eachbq);
			$('#tianjia').modal('hide');
		})
		console.log(arr_bq)
		var arr_bqs = JSON.stringify(arr_bq);
		$('#taginfo').val(arr_bqs)
	}else{
		alert('请选择标签！')
	}
})
