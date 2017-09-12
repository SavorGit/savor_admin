var calculate_size = function() {
	var BASE_FONT_SIZE = 100;

	var docEl = document.documentElement,
		clientWidth = docEl.clientWidth;
	if(!clientWidth) return;
	docEl.style.fontSize = BASE_FONT_SIZE * (clientWidth / 750) + 'px';
};

// Abort if browser does not support addEventListener
if(document.addEventListener) {
	var resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize';
	window.addEventListener(resizeEvt, calculate_size, false);
	document.addEventListener('DOMContentLoaded', calculate_size, false);
	calculate_size();
}
var con_hei = $('.content').css('height');
$('#conwith').val(con_hei);
var btn_num = 0;
$('input[type=text]').blur(function() {
	btn_num = 0;
	if($(this).val() == '') {
		btn_num++
		//$('#txt-name').next('span').html('请填写收货人姓名')
		// tankuang(100,'请填写收货人姓名');
		// tankuang(100,'请填写手机号');
		//$('#tel').next('span').html('请填写手机号')
		//$(this).siblings('.null-tel').show();
	} else {
		$(this).siblings('.null-tel').hide();
		var tela = $('#tel').val();
		var txt_name = $('#txt-name').val();

		if(txt_name.length > 16) { //验证字符长度
			
			tankuang(150,'付款人长度为1—16个字符');
			btn_num++
		}
		if(tela != '') {
			if(!(/^1[34578]\d{9}$/.test(tela))) { //验证手机号
				btn_num++
				tankuang(150,'请填写正确的手机号码');
			} else {
				$('#tel').next('span').hide();
			}

		}
	}
})
$('.pay-group span').click(function(){//点击选中
	$(this).addClass('active').siblings().removeClass('active');
	var goods_id = $(this).attr("dt");
	var goods_name = $(this).attr("ga");
	var goods_price = $(this).attr("gp");
	$('#goods_name').val(goods_name);
	$('#goods_price').val(goods_price);
	$('#goods_id').val(goods_id);
	
})
//数量
$('.jian').click(function(){
	
	if($('.shuzi').html()!='1'){
		var jians = parseInt($('.shuzi').html());
		$('.shuzi').html(jians-1);
	}
})
$('.jia').click(function(){
	var order_max_num = $('#order_max_num').val();
	var jias = $('.shuzi').html();
	var a = parseInt(jias)
	if(a<order_max_num){
		$('.shuzi').html(a+1);
	}
	
})
$('input[type=text]').focus(function() {
	$(this).siblings('.null-tel').hide();
})
//var numa = 1;
var flag  = 1;
$('.yzm').click(function() {
	$('#piccode').val('');
	$("#verify_img").click();
	$('#verify_code').val('');
	var verival = $(this).val();
	//alert(verival);
	var mobile=$('#tel').val();
	var activity_id =$('#activity_id').val();
	var a = 59;
	var tel = $('#tel').val();
	var goods_id= $('#goods_id').val();
	var goods_nums = $('.shuzi').html();
	if(activity_id ==''){
		tankuang(100,'该活动不存在');
		return false;
	}
	if(tel ==''){
		tankuang(100,'请填写手机号');
		return false;
		flag = 0;
		
	}else {
		if(!(/^1[34578]\d{9}$/.test(tel))) {
			tankuang(110,'请填写正确的手机号');
			return false;
			flag = 0;
		}else {
			$(this).prop('disabled', true)
		}
	}
	
	if($(this).val() == '获取验证码' || $(this).val() == '重新获取') { //发送短信
		
		$.ajax({
			type:'post',
			url:'/admin/Activitydetail/getActMobileCode',
			data:{'mobile':mobile,'activity_id':activity_id,'goods_id':goods_id,'goods_nums':goods_nums},
			dataType:"json",
			success:function(data){
				if(data.status !=1){
					if(data.status==201){
						$('.zhez').show();
						$('body').css('overflow','hidden');
						$('.num-yzm').show();
						$('.yzm').prop('disabled', false)
					}else {
						tankuang(data.extent,data.msg);
						
						$('.yzm').prop('disabled', false)
					}
					
				}else {
					tankuang(100,'验证码发送成功');
					var times = setInterval(function() { //开启计时器
						$('.yzm').val('' + (a--) + 's')
						console.log($('.yzm').val())
						if($('.yzm').val() == '0s') {
							$('.yzm').val('重新获取')
							clearInterval(times)
							$('.yzm').prop('disabled', false)
						}
					}, 1000)
				}
				
			}
			
		})
		
	}
	/*if(flag ==1){
		var times = setInterval(function() { //开启计时器
			$('.yzm').val('' + (a--) + 's')
			console.log($('.yzm').val())
			if($('.yzm').val() == '0s') {
				$('.yzm').val('重新获取')
				clearInterval(times)
				$('.yzm').prop('disabled', false)
			}
		}, 1000)
	}*/
	
})
var flag =1;
$('.btn').click(function(event) {
	var apply_name = $('#txt-name').val();
	var mobile     = $('#tel').val();
	var verify_code = $('#verify_code').val();
	var address    = $('#address').val();
	var activity_id= $('#activity_id').val();
	var goods_id   = $('#goods_id').val();
	var goods_nums = $('.shuzi').html();
	
	var sourceid = $('#sourceid').val();
	
	if(goods_nums<1){
		tankuang(100,'请选择正确的商品数量');
		return false;
	}
	if(apply_name =='' ){
		tankuang(100,'请填写付款人');
		return false;
	}
	if(apply_name.length>16){
		tankuang(150,'付款人长度为1-16个字');
		return false;
	}
	if(mobile==''){
		tankuang(100,'请填写手机号');
		return false;
	}
	if(verify_code ==''){
		tankuang(100,'请填写手机验证码');
		return false;
	}
	if(address !='' && address.length>100){
		
		tankuang(110,'地址长度为1-100字');
		return false;
	}
	
	$.ajax({
		type:'post',
		url :'/admin/Activitydetail/doActApply',
		data:{'apply_name':apply_name,'mobile':mobile,'verify_code':verify_code,'address':address,'activity_id':activity_id,'goods_id':goods_id,'goods_nums':goods_nums,'sourceid':sourceid},
		dataType: "json",
		success:function(data){
			if(data.status !=1){
				tankuang(data.extent ,data.msg);
			}else if(data.status==1){
				//tankuang(100,'下单成功');
				$('.dingdan_name').html($('#txt-name').val());
				$('.dingdan_phone').html($('#tel').val());
				$('.dingdan_phname').html($('#goods_name').val());
				$('.dingdan_phyuan').html($('#goods_price').val());
				$('.dingdan_bu').html($('.shuzi').html());
				$('.zhez').show();
				$('body').css('overflow','hidden');
				$('#apply_ok').show();
			}
		}
	
	})
	
})

$('.btn_z').click(function() {
	$('.zhez').hide();
	$('body').css('overflow','auto');
	$('.ok').hide();
	$('.for input[type=text]').val('');
	$('.tear').val('');
	$('.shuzi').html('1');
	$('.yzm').val('获取验证码');
	$('.xia').click();
})
$('.qx').click(function() {
	$('.zhez').hide()
	$('.num-yzm').hide()
	$('body').css('overflow','auto');
	$('#piccode').val('');
	$("#verify_img").click();
})
$('.shuoming img').click(function() {
	$('.zhez').hide();
	$('.shuoming').hide();
	$('body').css('overflow','auto');
})
$("#verify_img").click(function() {
   var verifyURL = "/admin/verify/verify";
   var time = new Date().getTime();
    $("#verify_img").attr({
       "src" : verifyURL + "/" + time
    });
});
$("#confimcode").click(function() {
	var a = 59;
	var piccode = $('#piccode').val();
	var mobile  = $('#tel').val();
	var activity_id = $('#activity_id').val();
	var goods_id = $('#goods_id').val();
	var goods_nums = $('.shuzi').html();
	if(activity_id==''){
		tankuang(100,'该活动不存在');
		return false;
	}
	if(mobile==''){
		tankuang(100,'请填写手机号');
		$('.zhez').hide()
		$('body').css('overflow','auto');
		$('.num-yzm').hide()
		return false;
	}
	if(piccode==''){
		tankuang(100,'请填写验证码');
		return false;
	}
	if(piccode.length !=4 ){
		tankuang(100,'验证码长度不正确');
		return false;
	}
	
	$.ajax({
		type:'post',
		url :'/admin/Activitydetail/configActCode',
		data:{'pic_code':piccode,'mobile':mobile,'activity_id':activity_id,'goods_id':goods_id,'goods_nums':goods_nums},
		dataType: "json",
		success:function(data){
			if(data.status==1){
				$('.zhez').hide()
				$('body').css('overflow','auto');
				$('.num-yzm').hide()
				
				tankuang(data.extent,'验证码发送成功');
				var times = setInterval(function() { //开启计时器
					$('.yzm').val('' + (a--) + 's')
					console.log($('.yzm').val())
					if($('.yzm').val() == '0s') {
						$('.yzm').val('重新获取')
						clearInterval(times)
						$('.yzm').prop('disabled', false)
					}
				}, 1000)
			}else {
				tankuang(100,data.msg);
			}
		}
		
	})
})
/*提示框*/
function tankuang(pWidth, content) {
	$("#msg").remove();
	var html = '<div id="msg" style="position:fixed;top:50%;width:100%;height:0.8rem;line-height:0.8rem;margin-top:-15px;"><p style="background:#000;opacity:0.8;width:' + pWidth + 'px;color:#fff;text-align:center;padding:10px 10px;margin:0 auto;font-size:12px;border-radius:4px;">' + content + '</p></div>'
	$("body").append(html);
	var t = setTimeout(next, 3000);

	function next() {
		$("#msg").remove();

	}
}
$('.xia').click(function(){
	$('.content').css({
		position:'fixed',
		top:'2.4rem'
	})
	$('.xia').hide();
	$('.bj').show();
		$('.content').animate({
			height:'50px',
			top:'97%'
			},500);
		//$('.for').animate({height:'50px'});
		$('.for').hide();
		$('.for').css('opacity','0.1')
		setTimeout(function(){
			$('.content').hide()
			$('.xiadan').show();
			$('.bai_zhezhao').show();
		},500)
	})
$('.xiadan').click(function(){
	$('.content').show();
	$('.xiadan').hide();
	$('.bai_zhezhao').hide();
	var conwidth = $('#conwith').val()
	$('.content').animate({
		height:conwidth,
		top:'2.4rem'
		},500);
		setTimeout(function(){
			$('.for').show();
			$('.for').animate({opacity:'1'})
			$('.bj').hide();
			$('.content').css({
				position:'',
			})
			$('.xia').show(500);
		},500)
})
