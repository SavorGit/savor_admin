$('.ban_list').html('');//初始化
var honame = $('#honame').val();
$('.tianjiabw').click(function(){
	$('.position-list').html('');
	$('.position-list-group').html('')
	//alert($('.dval').val())
	var tsnull = 1;
	$('.dval').each(function(){
		if($(this).val()==''||$('#cishu').val()==''){
			//alert(1)
			tsnull = 2;
		}else if($('#marketid').val()==''){
			tsnull = 3;
		}
	})
	if(tsnull==2){
		alert('日期和播放次数不可以为空！')
	}else if(tsnull==3){
		alert('请添加视频')
	}else if(tsnull==1){
	boiteName()
	$('#modal').modal('show')
	}
})

$('.select-position').on('click','.position-ones-name',function(){
	var _this = $(this).parent();
	var isnull = $(this).parent().next().html()
	var ho_id = $(this).parent().find('input').prop('id');
	var kai_time = $('.dval').eq(0).val();
	var jie_time = $('.dval').eq(1).val();
	var pl_time = $('#cishu').val();
	if(isnull==''){
		$.ajax({
		type:"post",
		url:honame+"/advdelivery/getValidBoxidByHotel",
		dataType:"json",
		data:"hotel_id="+ho_id+"&start_time="+kai_time+"&end_time="+jie_time+"&play_times="+pl_time,
		async:true,
		success:function(calls){
			console.log(calls)
			if(calls.code==1){
				var call = calls.data;
				if(call==''){
					alert('当前酒楼无版位！')
				}
				for(var i=0;i<call.length;i++){
					if(call[i].btype==1){
						var bw_html = '<div style="width:242px;height:30px;display: inline-block;"><input type="checkbox" style="margin-left: 25px;" class="position-twos" id="'+call[i].bid+'"/><label style="margin-left: 10px;" for="'+call[i].bid+'">'+call[i].bname+'</label></div>';
						_this.next().append(bw_html);
					}else if(call[i].btype==0){
						var no_html = '<div style="width:242px;height:30px;display: inline-block;"><label style="margin-left: 28px;color:red;" for="'+call[i].bid+'">'+call[i].bname+'</label></div>';
						_this.next().append(no_html);
					}
				}
			}else{
					alert(calls.msg)
				}
		}
	});
	
	}else{
		$(this).parent().next().toggle()
	}
})

/*全选*/
$('.select-position').on('click','.position-ones',function(){
	var isnull = $(this).parent().next().html()
	if(isnull==''){
		$(this).prop('checked',false);
		alert('当前酒楼的版位加载出来，才可以全选！')
	}else{
		var check_position = $(this).prop('checked');
		if(check_position==true){
			$(this).parents().next().find('input').prop('checked',true);
		}else if(check_position==false){
			$(this).parents().next().find('input').prop('checked',false);
		}
	}
})
/*返回已经被选中的酒楼名称和版位数量*/
	
$('#xuanzhe_btn').click(function(){ 
		//var a = $('.position-ones,.position-twos').attr('checked');
		var arr = [];
		var arr2 = []
		$('.position-two').each(function(){
			var is_ch = $(this).find('input').is(':checked');
			if(is_ch==true){
				var obj = {};
				var obj2 = {};
				var arr3 = [];
				var arrid = [];
				var a = $(this).prev().children('label').html();//所选中的酒楼名称
				var a_id = $(this).prev().children('label').prop('id')//所选中的酒楼id
				var b = $(this).find('input:checked').length;
				//var c = $(this).find('label').html();
				var this_each = $(this).find('input');
				this_each.each(function(){
					var xuanzhong = $(this).prop('checked');//所选中的版位名称
					if(xuanzhong==true){
						var c = $(this).next().html();
						var c_id = $(this).next().prop('for');
						arr3.push(c);
						arrid.push(c_id);
					}
				})
				//console.log(arr3)
				var arrs3 = arr3.join(',')
				obj2.namejiu = a;
				obj2.jiuid = a_id;
				obj2.banid = arrid;
				obj2.jiunum = arr3;
				obj.namejiu = a;
				obj.jiunum = b
				//alert(a+'+'+b)
				arr.push(obj)
				arr2.push(obj2)
			}
		})
		console.log(arr)
		console.log(arr2)
		var is = JSON.stringify(arr2)
		$('#bczhi').val(is);
		$('.acitve-position-lists').html('');
		var stra = 0;
		for(var i=0;i<arr.length;i++){
			var list_html = '<div class="position-list-group"><span class="acitve-position-jname">'+arr[i].namejiu+'</span><span class="acitve-position-num" >'+arr[i].jiunum+'个</span><span class="acitve-position-del" jiuname = "'+arr[i].namejiu+'">X</span></div>'
			$('.acitve-position-lists').append(list_html);
			stra+=arr[i].jiunum;
		}
		$('.jiuloushuliang').html(arr.length);
		$('.banweishu').html(stra);
	})

/*删除*/
$('.acitve-position-lists').on('click','.acitve-position-del',function(){
		var a = $(this).attr('jiuname');
		$(".position-ones-name:contains('"+a+"')").parent().find('input').prop('checked',false);
		$(".position-ones-name:contains('"+a+"')").parent().next().find('input').prop('checked',false);
		var is = $('#bczhi').val();
		var ias = eval(is)
		var del_index = $(this).parent().index();
		ias.splice(del_index,1);
		console.log(ias);
		$(this).parent().remove();
		var saco = JSON.stringify(ias)
		$('#bczhi').val(saco);
		$('.jiuloushuliang').html($('.position-list-group').length);
		var xsa = $(this).parent().find('.acitve-position-num').html();
		var asdddd = parseInt(xsa)
		$('.banweishu').html($('.banweishu').html()-asdddd);
	})

/*版位保存*/
$('#bw_bc').click(function(){
	$('.ban_list').html('')
	var acitve_position_lists = $('.acitve-position-lists').html();
	if(acitve_position_lists==''){
		alert('请选择版位')
	}else{
		var bczhi_val = $('#bczhi').val();
		var bczhi_vals = eval(bczhi_val);
		console.log(bczhi_vals)
		for(var i = 0;i<bczhi_vals.length;i++){
			var bczhi_bid = bczhi_vals[i].banid;
			var b_ht = '<div class="jiu_names"><span id="'+bczhi_vals[i].jiuid+'">'+bczhi_vals[i].namejiu+'</span></div><div class="ban_names"></div>';
			$('.ban_list').append(b_ht);
			for(var j=0;j<bczhi_bid.length;j++){
			var aaa = '<div class="ban_a" id="'+bczhi_vals[i].banid[j]+'">'+bczhi_vals[i].jiunum[j]+'</div>'
				$('.ban_names').eq(i).append(aaa)
			}
			$('#modal').modal('hide')
			$('.jiuloushu span').eq(0).html($('.jiuloushuliang').html())
			$('.jiuloushu span').eq(1).html($('.banweishu').html())
			/*var bczhi_bid = bczhi_vals[i].banid;
			for(var j=0;j<bczhi_bid.length;j++){
				var b_id = '<div class="ban_a" id="'+bczhi_bid[j]+'">'+bczhi_vals[i].jiunum[j]+'</div>';
				$('.ban_names').append(b_id);
			
			}*/
		}
	}
})
/*整体保存*/
$('#qb_save').click(function(){
	var arr = [];
	$('.jiu_names').each(function(){
		var arr2 = [];
		var obj = {}
		var lname = $(this).find('span').prop('id');
		var _this_bana = $(this).next().find('div');
		_this_bana.each(function(){
			var w_id = $(this).prop('id');
			arr2.push(w_id);
		})
		var arrs2 = arr2.join(',');
		obj.hotel_id = lname;
		obj.box_str = arr2;
		arr.push(obj)
	})
	var arrstr = JSON.stringify(arr);
	$('#hbars').val(arrstr)
	$('.vid_names').html($('#sp_name').html())
	$('.str_time').html($('.dval').eq(0).val())
	$('.end_time').html($('.dval').eq(1).val())
	$('.ljiu_num').html($('.jiuloushu span').eq(0).html())
	$('.h_num').html($('.jiuloushu span').eq(1).html())
	$('.bf_cishu').html($('#cishu').val())
	var sp_isid = $('#newsp').css('display');
	if($('.ban_list').html()!=''&& sp_isid!='none'){
		$('#bctk').modal('show');
	}else{
		alert('请选择版位或视频')
	}
	//console.log(arr)
})
$('#q_fb').click(function(){
	$('#bctk').modal('hide')
})
/*点击保存视频*/

$('body').on('click','#saveImg',function(){

	var media_url = $('#media_url').val();  //视频地址
	var resource_name = $('#resource_name').val();  //视频名称
	var seco = $('#seco').val();  //视频时长
	$('#sp_name').html(resource_name);
	$('#sp_url').html(media_url);
	if(seco<60){
		
		$('#sp_time').html(seco+'秒')
	}else if(seco>=60){
		var fen = parseInt(seco/60);
		var miaos = seco-60*fen;
		if(miaos==0){
			$('#sp_time').html(fen+'分钟00秒');
		}else{
			$('#sp_time').html(fen+'分钟'+miaos+'秒');
		}
	}
	$('#newsp').show()
	$('#sp').hide();
})
$('#sdd').click(function(){
	var city_id = $('.cityId').val();
	var serch_name = $('#serch-name').val();
	$('.position-list').html('');
		$.ajax({
		type:"post",
		url:honame+"/advdelivery/getOcupHotel",
		dataType:"json",
		data:"area_id="+city_id+"&hotel_name="+serch_name,
		async:true,
		success:function(calls){
			console.log(calls)
			if(calls.code==1){
				var call = calls.data;
				for(var i=0;i<call.length;i++){
				var jiu_name = '<div class="position-one"><input type="checkbox" class="position-ones" id="'+call[i].hid+'"/><label class="position-ones-name" id="'+call[i].hid+'">'+call[i].hname+'</label></div><div class="position-two"></div>';
				$('.position-list').append(jiu_name);
				}
				
			}else{
				alert(calls.msg)
			}
		}
	});
})
/*$('body').on('click','.datetimepicker<span',function(){
	alert(1)
})*/
$('#datetimepicker,#datetimepicker2').datetimepicker().on('changeDate', function(ev){
	$('.ban_list').html('');
	$('.jiuloushu span').eq(0).html('0');
	$('.jiuloushu span').eq(1).html('0');
	$('.position-list-group').html('');
});
$('#cishu').change(function(){
	$('.ban_list').html('');
	$('.jiuloushu span').eq(0).html('0');
	$('.jiuloushu span').eq(1).html('0');
	$('.position-list-group').html('');
})
function boiteName(){  //酒楼名称
	$.ajax({
		type:"post",
		url:honame+"/advdelivery/getOcupHotel",
		dataType:"json",
		async:true,
		success:function(calls){
			console.log(calls)
			if(calls.code==1){
				var call = calls.data;
				for(var i=0;i<call.length;i++){
				var jiu_name = '<div class="position-one"><input type="checkbox" class="position-ones" id="'+call[i].hid+'"/><label class="position-ones-name" id="'+call[i].hid+'">'+call[i].hname+'</label></div><div class="position-two"></div>';
				$('.position-list').append(jiu_name);
				}
				
			}else{
				alert(calls.msg)
			}
		}
	});
}

