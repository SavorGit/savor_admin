var honame = $('#honame').val();
$('.positions').change(function(){
		if($(this).val()==3){		
			$('.kuai').show();
		}else{
			$('.kuai').hide();
		}
		if($(this).val()==1 || $(this).val()==3){
			$('#all_city_list').hide();
			$('#city_list').show();
		}else {
			$('#all_city_list').show();
			$('#city_list').hide();
		}
		var installs =  $('#2').val();
		if($(this).val()==3 && installs !=''){
			$('#teaminstall').show();
		}else {
			$('#teaminstall').hide();
		}
		
		$(this).next().find('span').prop('class','')
		
	})
	$('.kuai span').click(function(){
		var skill_id = $(this).prop('id');
		if($(this).is('.active')){
			$(this).removeClass('active')
			$(this).find('input').val('')
			if(skill_id ==2){
				$('#teaminstall').hide();
			}
		}else{
			$(this).addClass('active');
			$(this).find('input').val($(this).prop('id'))
			
			if(skill_id ==2){
				$('#teaminstall').show();
			}
		}
		
	})
	
	
	
	$('.entirety').on('change','#inch',function(){
		var this_val =  $(this).val();
		//alert(this_val)
		if(this_val.indexOf('9999')==0){
			$('.inner li').each(function(){
				$(this).removeClass('selected');
				if($(this).attr('data-original-index')==0){
					$('.btn-success').attr('title','全国')
					//$('.pull-left').html('全国');
					$('#inch').prev().find('.pull-left').html('全国')
					$('#inch').val('9999')
					$(this).addClass('selected')
				}
			})
		}
	})
	$('#tj').click(function(){
		
	/*选择酒楼*/
	var obj = {};
	var url_s = honame+'/optionuser/manager_list';
	var area_v = $('select[name="area_v"]').val();
	var level_v = $('select[name="level_v"]').val();
	var state_v = $('select[name="state_v"]').val();
	var key_v = $('select[name="key_v"]').val();
	var main_v = $('input[name="main_v"]').val();
	var hbt_v = $('select[name="hbt_v"]').val();
	var names = $('input[name="names"]').val();
	var cityId = $('#inch').val();
	var citys = cityId.join(',');
	$("#area_id").val(area_v);
	obj.area_v = 0;
	obj.level_v = level_v;
	obj.state_v = state_v;
	obj.key_v = key_v;
	obj.main_v = main_v;
	obj.name = names;
	obj.ajaxversion = 1;
	obj.hbt_v = hbt_v;
	obj.cityid = citys;
	$.ajax({
		type:"post",
		url:honame+'/optionuser/manager_list',
		data:obj,
		async: true,
		success:function(call){
			var cdata =JSON.parse(call).hotel;
			var citydq = JSON.parse(call).arinfo;
			console.log(JSON.parse(call))
			var boite = document.getElementById('boite');
			boite.innerHTML = "";
			$('#cityhtml').html('')
			for(var j=0;j<citydq.length;j++){
				let cityhtml = `
				<option value="${citydq[j].id}">${citydq[j].region_name}</option>
				`
				$('#cityhtml').append(cityhtml);
			}
			for(var i=0;i<cdata.length;i++){
				var texts = cdata[i].hotel_name;
				var tid = cdata[i].hotel_id;
				boite.innerHTML+='<div class="jlwid"><input type="checkbox"id="'+tid+'" class="checks" /><span>'+texts+'</span></div>'
			}
		}
	});
	})
	$('#checkquan').click(function(){
		
	if($('#checkquan').prop("checked")){
		$(".checks").prop('checked',true)
	}else{
		$(".checks").removeAttr('checked');
	}
	})
/*------反选----------*/
$('#checkfan').click(function(){
	 $(".checks").each(function(){     
   
     if($(this).prop("checked"))     
   {     
    $(this).removeAttr("checked");     
         
   }     
   else    
   {     
    $(this).prop("checked",true);
         
   }     
        
    })  
})
/*----------选取---------------*/
	$('#searchs').click(function(){
		var obj = {};
		var url_s = honame+'/optionuser/manager_list';
		var area_v = $('select[name="area_v"]').val();
		var level_v = $('select[name="level_v"]').val();
		var state_v = $('select[name="state_v"]').val();
		var key_v = $('select[name="key_v"]').val();
		var main_v = $('input[name="main_v"]').val();
		var hbt_v = $('select[name="hbt_v"]').val();
		var names = $('input[name="names"]').val();
		var cityId = $('#inch').val();
		var citys = cityId.join(',');
		$("#area_id").val(area_v);
		obj.area_v = area_v;
		obj.level_v = level_v;
		obj.state_v = state_v;
		obj.key_v = key_v;
		obj.main_v = main_v;
		obj.name = names;
		obj.ajaxversion = 1;
		obj.hbt_v = hbt_v;
		obj.cityid = citys;
		var objs = JSON.stringify(obj);
		$.ajax({
			type:"post",
			url:honame+'/optionuser/manager_list',
			data:obj,
			async: true,
			success:function(call){
				console.log(call);
				var cdata =JSON.parse(call).hotel;
				var boite = document.getElementById('boite');
				boite.innerHTML = "";
				for(var i=0;i<cdata.length;i++){
						var texts = cdata[i].hotel_name;
						var tid = cdata[i].hotel_id;
						boite.innerHTML+='<div class="jlwid"><input type="checkbox"id="'+tid+'" class="checks" /><span>'+texts+'</span></div>'
				}
				if($('#jia').html()!=''){
					$('.jiulou').each(function(){
						var _thisId = $(this).prop('id');
						$('.jlwid').each(function(){
							var inpid = $(this).find('input').prop('id');
							if(_thisId==inpid){
								$(this).find('input').prop('checked',true);
							}
						})
					})
				}
			}
		});
	})

	$('#yes').click(function(){
		var id = [];
		var name = [];
		$('[class="checks"]:checked').each(function(){
			id.push($(this).attr('id'));
			name.push($(this).next().html());
		})
		var hotelstr = id.join(",");
		$("#hotelids").val(hotelstr);
		var jia = document.getElementById('jia');
		//jia.innerHTML='';
		if($('#jia').html()!=''){
			$('[class="checks"]:checked').each(function(){
				
				var jlthis = $(this).prop('id');
				var jlthishtml = $(this).parent().find('span').html();
				var jlhtml = '<a class="jiulou" id="'+jlthis+'">'+jlthishtml+'<span class="del">X</span>&nbsp;&nbsp;</a>'
				$('.jiulou').each(function(){
					var _this = $(this);
					var _thisId = $(this).prop('id');
					if(_thisId==jlthis){
						$(this).remove();
					}
				})
				$('#jia').append(jlhtml)
			})
		}else{
			
			for(var i=0;i<name.length;i++){
				jia.innerHTML+='<a class="jiulou" id="'+id[i]+'">'+name[i]+'<span class="del">X</span>&nbsp;&nbsp;</a>'
			}
		}
	})
	//删除
	$('body').on('click','.del',function(){
		var _thisid = $(this).parent().prop('id')
		$('.jlwid').each(function(){
			var ifthis = $(this).find('input').prop('id');
			if(ifthis==_thisid){
				$(this).find('input').prop('checked',false);
			}
		})
		$(this).parent().remove();
		var id = [];
		$('.jiulou').each(function(){
			id.push($(this).attr('id'));
		})
		var hotelstr =id.join(",");
		$("#hotelids").val(hotelstr);
	})
	
//保存
$('#subyes').click(function(){
	var jid;
	var idarr = [];
	$('.jiulou').each(function(){
		jid = $(this).prop('id');
		idarr.push(jid)
	})
	var idarrs = idarr.join(',');
	console.log(idarrs)
	$('#hotelids').val(idarrs);
})
