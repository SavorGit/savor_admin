var a = 1;
$.ajax({
	type:"get",
	url:"/admin/installoffer/getalldevice",
	async:true,
	dataType:"json",
	success:function(call){
		//console.log(call)
		for(var i=0;i<call.length;i++){
			var ophtml = '<option value="'+call[i].id+'">'+call[i].device_name+'</option>';
			$('#selopt').append(ophtml);
		}
	}
});
$('.addition').click(function(){
	//var sel = '<select></select>'
	var b = a++;
	var selopt = $('#selopt').html();
	console.log(selopt)
var ta_html = '<tr><td>'+b+'</td><td><select class="device_name" name="info['+b+'][device_id]"><option>请选择</option>'+selopt+'</select></td><td><select name="info['+b+'][params_id]" class="xinhao"><option>请选择</option></select></td><td class="params"></td><td><input name="info['+b+'][nums]" type="text" class="nums" value="0"></td><td></td><td><input class="cost_nums" type="text" value="0"></td><td><input class="cost" type="text" value="0"></td><td><input type="text" name="info['+b+'][market_price]" value="0" class="market_price"></td><td class="all_market_price">0</td><td></td><td><input type="text" class="our_price" name="info['+b+'][our_price]"></td><td><button class="del">删除</button></td><td><input type="text" value="100" class="explain">%</td></tr>'
$('.tina_xj').before(ta_html)

})
$('#tbod').on('change','.device_name',function(){
	var device_val = $(this).val();
	var _this = $(this)
	_this.parent().parent().find('.xinhao').html('');
	_this.parent().parent().find('.xinhao').html('<option>请选择</option>');
	$.ajax({
		type:"post",
		url:"/admin/installoffer/getStandard?id="+device_val,
		dataType:"json",
		async:true,
		success:function(call){
			//console.log(call)
			for(var i=0;i<call.length;i++){
				var ophtml = '<option value="'+call[i].id+'">'+call[i].standard+'</option>';
				_this.parent().parent().find('.xinhao').append(ophtml);
			}
		}
	});
})
$('#tbod').on('change','.xinhao',function(){
	var device_val = $(this).val();
	var _this = $(this)
	_this.parent().parent().find('.params').html('')
	$.ajax({
		type:"post",
		url:"/admin/installoffer/getParams?id="+device_val,
		dataType:"json",
		async:true,
		success:function(call){
			console.log(call)
			if(call!=''){
				//alert(call.cost_price)
				_this.parent().parent().find('.params').html(call.params)
				_this.parent().parent().find('.cost_nums').val(call.cost_price) //成本 价
				_this.parent().parent().find('.market_price').val(call.market_price) //市场价
			}
		}
	});
})
$('#tbod').on('click','.del',function(){ //删除
	$(this).parent().parent().remove();
	var market_price_total = 0;
	$('.all_market_price').each(function(){
		var market_price_nums = parseFloat($(this).html());
		market_price_total+=market_price_nums
	})
	if(market_price_total=='NaN'){
		market_price_total=0
	}
	$('#market_price_total').html(market_price_total);
	/*------------------------------*/
	var our_price_total_n = 0;
	$('.our_price').each(function(){
		var our_price_total_nums = parseFloat($(this).val());
		our_price_total_n+=our_price_total_nums
	})
	if(our_price_total_n=='NaN'){
		our_price_total_n=0
	}
	$('#our_price_total').html(our_price_total_n);
	/*------------*/
	var cost_nums = $(this).val()*$(this).parent().parent().find('.cost_nums').val();
	$(this).parent().parent().find('.cost').val(cost_nums)
	/*----------------------*/
	var cost_total = 0;
	$('.cost').each(function(){
		var costs = parseInt($(this).val());
		cost_total+=costs;
	})
	if(cost_total=='NaN'){
		cost_total=0
	}
	$('#cost_total').html(cost_total)
	/*-----------------------------*/
	
	$('.group_our_price').html(parseInt($('#our_price_total').html())+parseInt($('#our_price_total_B').html()))
})

/*监听键盘输入*/
$('#tbod').on('keyup','.nums',function(){
	var market_price = $(this).parent().parent().find('.market_price').val();
	var all_market_price = market_price*$(this).val();
	$(this).parent().parent().find('.all_market_price').html(all_market_price);
	var our_price = $(this).parent().parent().find('.all_market_price').html()*$(this).parent().parent().find('.explain').val()/100
	$(this).parent().parent().find('.our_price').val(our_price)
	var market_price_total = 0;
	$('.all_market_price').each(function(){
		var market_price_nums = parseFloat($(this).html());
		market_price_total+=market_price_nums
	})
	if(market_price_total=='NaN'){
		market_price_total=0
	}
	$('#market_price_total').html(market_price_total);
	/*------------------------------*/
	var our_price_total_n = 0;
	$('.our_price').each(function(){
		var our_price_total_nums = parseFloat($(this).val());
		our_price_total_n+=our_price_total_nums
	})
	if(our_price_total_n=='NaN'){
		our_price_total_n=0
	}
	$('#our_price_total').html(our_price_total_n);
	/*------------*/
	var cost_nums = $(this).val()*$(this).parent().parent().find('.cost_nums').val();
	$(this).parent().parent().find('.cost').val(cost_nums)
	/*----------------------*/
	var cost_total = 0;
	$('.cost').each(function(){
		var costs = parseInt($(this).val());
		cost_total+=costs;
	})
	if(cost_total=='NaN'){
		cost_total=0
	}
	$('#cost_total').html(cost_total)
	var allcost_total= parseInt($('#cost_total').html())+parseInt($('#cost_total_B').html());
	$('#group_cost_price').html(allcost_total)
	var allcost_totals =parseInt($('#cb_num1').val())+parseInt($('#cb_num2').val());
	$('#allxm').html(allcost_total+allcost_totals);
	var allour_price= parseInt($('#our_price_total').html())+parseInt($('#our_price_total_B').html());
	$('.group_our_price').html(allour_price)
	/*总报价*/
	var z_our_price = parseInt($('.group_our_price').html())*0.05;
	var z_our_price2 = parseInt($('.group_our_price').html())*0.0334;
	$('#z_our_price').html(parseInt(z_our_price2))
	$('#shuidian').html(parseInt(z_our_price))
})
$('#tbod').on('keyup','.market_price',function(){
	var nums = $(this).parent().parent().find('.nums').val();
	var all_market_price = nums*$(this).val();
	$(this).parent().parent().find('.all_market_price').html(all_market_price)
	var our_price = $(this).parent().parent().find('.all_market_price').html()*$(this).parent().parent().find('.explain').val()/100
	$(this).parent().parent().find('.our_price').val(our_price)
	var market_price_total = 0;
	$('.all_market_price').each(function(){
		var market_price_nums = parseFloat($(this).html());
		market_price_total+=market_price_nums
	})
	if(market_price_total=='NaN'){
		market_price_total=0
	}
	$('#market_price_total').html(market_price_total);
	var our_price_total_n = 0;
	$('.our_price').each(function(){
		var our_price_total_nums = parseFloat($(this).val());
		our_price_total_n+=our_price_total_nums
	})
	if(our_price_total_n=='NaN'){
		our_price_total_n=0
	}
	$('#our_price_total').html(our_price_total_n);
	var allour_price= parseInt($('#our_price_total').html())+parseInt($('#our_price_total_B').html());
	$('.group_our_price').html(allour_price)
	/*总报价*/
	var z_our_price = parseInt($('.group_our_price').html())*0.05;
var z_our_price2 = parseInt($('.group_our_price').html())*0.0334;
	$('#z_our_price').html(parseInt(z_our_price2))
	$('#shuidian').html(parseInt(z_our_price))
})
$('#tbod').on('keyup','.explain',function(){
	var our_price  =  $(this).parent().parent().find('.all_market_price').html()*$(this).val()/100
	$(this).parent().parent().find('.our_price').val(our_price);
	var our_price_total_n = 0;
	$('.our_price').each(function(){
		var our_price_total_nums = parseFloat($(this).val());
		our_price_total_n+=our_price_total_nums
	})
	if(our_price_total_n=='NaN'){
		our_price_total_n=0
	}
	$('#our_price_total').html(our_price_total_n);
	var allour_price= parseInt($('#our_price_total').html())+parseInt($('#our_price_total_B').html());
	$('.group_our_price').html(allour_price)
	/*总报价*/
	var z_our_price = parseInt($('.group_our_price').html())*0.05;
	var z_our_price2 = parseInt($('.group_our_price').html())*0.0334;
	$('#z_our_price').html(parseInt(z_our_price2))
	$('#shuidian').html(parseInt(z_our_price))
})
//$('#tbod').on('keyup','.')
$('#tbod').on('keyup','.cost',function(){
	var cost_total = 0;
	$('.cost').each(function(){
		var costs = parseInt($(this).val());
		cost_total+=costs;
	})
	if(cost_total=='NaN'){
		cost_total=0
	}
	$('#cost_total').html(cost_total)
	console.log(cost_total)
	var allcost_total= parseInt($('#cost_total').html())+parseInt($('#cost_total_B').html());
	$('#group_cost_price').html(allcost_total)
	$('#allxm').html(allcost_total)
	var allcost_totals =parseInt($('#cb_num1').val())+parseInt($('#cb_num2').val());
	$('#allxm').html(allcost_total+allcost_totals);
	
})
$('#tbod').on('keyup','.cost_nums',function(){
	var cost_nums = $(this).val()*$(this).parent().parent().find('.nums').val();
	$(this).parent().parent().find('.cost').val(cost_nums)
	var cost_total = 0;
	$('.cost').each(function(){
		var costs = parseInt($(this).val());
		cost_total+=costs;
	})
	if(cost_total=='NaN'){
		cost_total=0
	}
	$('#cost_total').html(cost_total)
	var allcost_total= parseInt($('#cost_total').html())+parseInt($('#cost_total_B').html());
	$('#group_cost_price').html(allcost_total)
	$('#allxm').html(allcost_total)
	var allcost_totals =parseInt($('#cb_num1').val())+parseInt($('#cb_num2').val());
	$('#allxm').html(allcost_total+allcost_totals);
})
/*bbbbbbbbbbbbbbbbbbbbbbb*/
$('#tbod').on('keyup','.nums_B',function(){
	var market_price = $(this).parent().parent().find('.market_price_B').val();
	var all_market_price = market_price*$(this).val();
	$(this).parent().parent().find('.all_market_price_B').html(all_market_price);
	var our_price = $(this).parent().parent().find('.all_market_price_B').html()*$(this).parent().parent().find('.explain_B').val()/100
	$(this).parent().parent().find('.our_price_B').val(our_price)
	var cost_nums = $(this).val()*$(this).parent().parent().find('.cost_nums_B').val();
	$(this).parent().parent().find('.cost_B').val(cost_nums);
	var market_price_total = 0;
	$('.all_market_price_B').each(function(){
		var market_price_nums = parseFloat($(this).html());
		market_price_total+=market_price_nums
	})
	if(market_price_total=='NaN'){
		market_price_total=0
	}
	$('#market_price_total_B').html(market_price_total);
	/*------------------------------*/
	var our_price_total_n = 0;
	$('.our_price_B').each(function(){
		var our_price_total_nums = parseFloat($(this).val());
		our_price_total_n+=our_price_total_nums
	})
	if(our_price_total_n=='NaN'){
		our_price_total_n=0
	}
	$('#our_price_total_B').html(our_price_total_n);
	/*------------*/
	var cost_nums = $(this).val()*$(this).parent().parent().find('.cost_nums').val();
	$(this).parent().parent().find('.cost').val(cost_nums)
	/*----------------------*/
	var cost_total = 0;
	$('.cost_B').each(function(){
		var costs = parseInt($(this).val());
		cost_total+=costs;
	})
	if(cost_total=='NaN'){
		cost_total=0
	}
	$('#cost_total_B').html(cost_total)
	var allcost_total= parseInt($('#cost_total').html())+parseInt($('#cost_total_B').html());
	$('#group_cost_price').html(allcost_total)
	
	var allcost_totals =parseInt($('#cb_num1').val())+parseInt($('#cb_num2').val());
	$('#allxm').html(allcost_total+allcost_totals);
	var allour_price= parseInt($('#our_price_total').html())+parseInt($('#our_price_total_B').html());
	$('.group_our_price').html(allour_price)
	/*总报价*/
	var z_our_price = parseInt($('.group_our_price').html())*0.05;
	var z_our_price2 = parseInt($('.group_our_price').html())*0.0334;
	$('#z_our_price').html(parseInt(z_our_price2))
	$('#shuidian').html(parseInt(z_our_price))
	/*奖金*/
	
	if($('.numsb').val()>=6){
		$('#many').html('400')
	}else{
		$('#many').html('200')
	}
	var numsb = (parseInt($('.numsb').val())+parseInt($('.numsb1').val())+parseInt($('.numsb2').val()))*10
	$('#tc').html(numsb)
})
$('#tbod').on('keyup','.market_price_B',function(){
	var nums = $(this).parent().parent().find('.nums_B').val();
	var all_market_price = nums*$(this).val();
	$(this).parent().parent().find('.all_market_price_B').html(all_market_price)
	var our_price = $(this).parent().parent().find('.all_market_price_B').html()*$(this).parent().parent().find('.explain_B').val()/100
	$(this).parent().parent().find('.our_price_B').val(our_price)
	var market_price_total = 0;
	$('.all_market_price_B').each(function(){
		var market_price_nums = parseFloat($(this).html());
		market_price_total+=market_price_nums
	})
	if(market_price_total=='NaN'){
		market_price_total=0
	}
	$('#market_price_total_B').html(market_price_total);
	/*------------------------------*/
	var our_price_total_n = 0;
	$('.our_price_B').each(function(){
		var our_price_total_nums = parseFloat($(this).val());
		our_price_total_n+=our_price_total_nums
	})
	if(our_price_total_n=='NaN'){
		our_price_total_n=0
	}
	$('#our_price_total_B').html(our_price_total_n);
	/*------------*/
	var our_price_total_n = 0;
	$('.our_price_B').each(function(){
		var our_price_total_nums = parseFloat($(this).val());
		our_price_total_n+=our_price_total_nums
	})
	if(our_price_total_n=='NaN'){
		our_price_total_n=0
	}
	$('#our_price_total_B').html(our_price_total_n);
	var allour_price= parseInt($('#our_price_total').html())+parseInt($('#our_price_total_B').html());
	$('.group_our_price').html(allour_price)
	/*总报价*/
	var z_our_price = parseInt($('.group_our_price').html())*0.05;
	var z_our_price2 = parseInt($('.group_our_price').html())*0.0334;
	$('#z_our_price').html(parseInt(z_our_price2))
	$('#shuidian').html(parseInt(z_our_price))
})
$('#tbod').on('keyup','.explain_B',function(){
	var our_price  =  $(this).parent().parent().find('.all_market_price_B').html()*$(this).val()/100;
	
	$(this).parent().parent().find('.our_price_B').val(our_price);
	var market_price_total = 0;
	$('.all_market_price_B').each(function(){
		var market_price_nums = parseFloat($(this).html());
		market_price_total+=market_price_nums
	})
	if(market_price_total=='NaN'){
		market_price_total=0
	}
	$('#market_price_total_B').html(market_price_total);
	/*------------------------------*/
	var our_price_total_n = 0;
	$('.our_price_B').each(function(){
		var our_price_total_nums = parseFloat($(this).val());
		our_price_total_n+=our_price_total_nums
	})
	if(our_price_total_n=='NaN'){
		our_price_total_n=0
	}
	$('#our_price_total_B').html(our_price_total_n);
	/*------------*/
	var allour_price= parseInt($('#our_price_total').html())+parseInt($('#our_price_total_B').html());
	$('.group_our_price').html(allour_price)
	/*总报价*/
	var z_our_price = parseInt($('.group_our_price').html())*0.05;
	var z_our_price2 = parseInt($('.group_our_price').html())*0.0334;
	$('#z_our_price').html(parseInt(z_our_price2))
	$('#shuidian').html(parseInt(z_our_price))
	
})
$('#tbod').on('keyup','.cost_B',function(){
	var cost_total = 0;
	$('.cost_B').each(function(){
		var costs = parseInt($(this).val());
		cost_total+=costs;
	})
	if(cost_total=='NaN'){
		cost_total=0
	}
	console.log(cost_total)
	$('#cost_total_B').html(cost_total)
	var allcost_total= parseInt($('#cost_total').html())+parseInt($('#cost_total_B').html());
	$('#group_cost_price').html(allcost_total)
	$('#allxm').html(allcost_total)
	var allcost_totals =parseInt($('#cb_num1').val())+parseInt($('#cb_num2').val());
	$('#allxm').html(allcost_total+allcost_totals);
})
$('#tbod').on('keyup','.cost_nums_B',function(){
	var cost_nums = $(this).val()*$(this).parent().parent().find('.nums_B').val();
	$(this).parent().parent().find('.cost_B').val(cost_nums);
	var cost_total = 0;
	$('.cost_B').each(function(){
		var costs = parseInt($(this).val());
		//console.log(costs)
		cost_total+=costs;
	})
	if(cost_total==NaN){
		cost_total=0
	}
	//console.log(cost_total)
	$('#cost_total_B').html(cost_total)
	var allcost_total= parseInt($('#cost_total').html())+parseInt($('#cost_total_B').html());//市场总价
	$('#group_cost_price').html(allcost_total)
	$('#allxm').html(allcost_total)
	var allcost_totals =parseInt($('#cb_num1').val())+parseInt($('#cb_num2').val());
	$('#allxm').html(allcost_total+allcost_totals);
})
$('.serve1').keyup(function(){
	var serve1 = parseInt($(this).val())+parseInt($('.serve2').val());
	$('.allm').html(serve1)
	/*总报价*/
	var z_our_price = parseInt($('.group_our_price').html())*0.05;
	var z_our_price2 = parseInt($('.group_our_price').html())*0.0334;
	$('#z_our_price').html(parseInt(z_our_price2))
	$('#shuidian').html(parseInt(z_our_price))
})
$('.serve2').keyup(function(){
	var serve1 = parseInt($(this).val())+parseInt($('.serve1').val());
	$('.allm').html(serve1)
	/*总报价*/
	var z_our_price = parseInt($('.group_our_price').html())*0.05;
	var z_our_price2 = parseInt($('.group_our_price').html())*0.0334;
	$('#z_our_price').html(parseInt(z_our_price2))
	$('#shuidian').html(parseInt(z_our_price))
})
$('#cb_num1').keyup(function(){
	var allcost_total= parseInt($('#cost_total').html())+parseInt($('#cost_total_B').html());
	var allcost_totals =parseInt($('#cb_num1').val())+parseInt($('#cb_num2').val());
	$('#allxm').html(allcost_total+allcost_totals);
})
$('#cb_num2').keyup(function(){
	var allcost_total= parseInt($('#cost_total').html())+parseInt($('#cost_total_B').html());
	var allcost_totals =parseInt($('#cb_num1').val())+parseInt($('#cb_num2').val());
	$('#allxm').html(allcost_total+allcost_totals);
})
setInterval(function(){
	$('#many2').html($('#many').html())
	$('#tc2').html($('#tc').html())
	var zbj =parseInt($('.group_our_price').html());
	var zcb = parseInt($('#group_cost_price').html());
	var slr = parseInt($('.group_our_price').html())-parseInt($('#group_cost_price').html())-parseInt($('#z_our_price').html())-parseInt($('#shuidian').html())-parseInt($('#many').html())-parseInt($('#tc').html());	
	$('#lirun').html(parseInt(slr));
	var all_ab_bazaar =  parseInt($('#market_price_total').html())+parseInt($('#market_price_total_B').html())
	$('.all_ab_bazaar').html(all_ab_bazaar)
	var cde_sc = parseInt($('.all_ab_bazaar').html())+parseInt($('.d_shic').val())+parseInt($('.e_shic').val());
	$('.abde_shic').html(cde_sc);
	var allabde = zbj+parseInt($('.serve1').val())+parseInt($('.serve2').val());
	$('.allm').html(allabde);
	var l_orr = parseInt($('#z_our_price').html())+parseInt($('#shuidian').html())+parseInt($('#many2').html())+parseInt($('#tc2').html());
	$('#z_orr').html(l_orr)
},200)
setInterval(function(){
	var zbj =parseInt($('.group_our_price').html());
	var zcb = parseInt($('#group_cost_price').html());
	var slr2 = (zbj-zcb-$('#z_our_price').html()-$('#shuidian').html()-$('#many').html()-$('#tc').html())/zbj;
	var slr2s = parseInt(slr2*100)
	$('#lirunl').html(slr2s+'%');
},200)
