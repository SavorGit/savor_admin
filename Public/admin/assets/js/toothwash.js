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
var btn_num = 0;
$('input[type=text]').blur(function() {
	btn_num = 0;
	if($(this).val() == '') {
		btn_num++
		$('#txt-name').next('span').html('请填写收货人姓名')
		$('#tel').next('span').html('请填写手机号')
		$(this).siblings('.null-tel').show();
	} else {
		$(this).siblings('.null-tel').hide();
		var tela = $('#tel').val();
		var txt_name = $('#txt-name').val();

		if(txt_name.length > 8) { //验证字符长度
			$('#txt-name').next('span').show();
			$('#txt-name').next('span').html('需输入1—8个字符')
			btn_num++
		}
		if(tela != '') {
			if(!(/^1[34578]\d{9}$/.test(tela))) { //验证手机号
				btn_num++
				$('#tel').next('span').show();
				$('#tel').next('span').html('请填写正确的手机号码')
			} else {
				$('#tel').next('span').hide();
			}

		}
	}
})
$('input[type=text]').focus(function() {
	$(this).siblings('.null-tel').hide();
})
//var numa = 1;
var flag  = 1;
$('.yzm').click(function() {
	
	/*if(numa>3){
		$('.zhez').show();
		$('.num-yzm').show();
	}*/
	$('#piccode').val('');
	$("#verify_img").click();
	$('#verify_code').val('');
	var verival = $(this).val();
	//alert(verival);
	var mobile=$('#tel').val();
	var activity_id =$('#activity_id').val();
	var a = 59;
	var tel = $('#tel').val();
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
			url:'/admin/Activitydetail/getmobileCode',
			data:{'mobile':mobile,'activity_id':activity_id},
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
	//console.log(btn_num)
	/*$('.vala').each(function() {
		if($(this).val() == '') {
			//alert('信息未填写完整')
			tankuang(100, '信息未填写完整')
			flag = 1;

		}else{
			if(btn_num==0){
			
			}else{
				tankuang(100, '有信息填写错误！');
				return false;
			}
		}

	});*/
	if(apply_name =='' ){
		tankuang(100,'请填写收货人');
		return false;
	}
	if(apply_name.length>8){
		tankuang(100,'收货人长度为1-8个字');
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
		url :'/admin/Activitydetail/doapply',
		data:{'apply_name':apply_name,'mobile':mobile,'verify_code':verify_code,'address':address,'activity_id':activity_id},
		dataType: "json",
		success:function(data){
			if(data.status !=1){
				tankuang(data.extent ,data.msg);
			}else if(data.status==1){
				//tankuang(100,'下单成功');
				$('.zhez').show();
				$('body').css('overflow','hidden');
				$('#apply_ok').show();
			}
		}
	
	})
	
})
$('.top-img').click(function() {
	$('.zhez').show();
	$('.shuoming').show();
	$('body').css('overflow','hidden');
})
$('.btn_z').click(function() {
	$('.zhez').hide();
	$('body').css('overflow','auto');
	$('.ok').hide();
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
		url :'/admin/Activitydetail/configcode',
		data:{'pic_code':piccode,'mobile':mobile,'activity_id':activity_id},
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