<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
<meta charset="utf-8"/>
<title>寻味后台管理系统</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<link href="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/plugins&f=font-awesome/css/font-awesome.min.css,simple-line-icons/css/simple-line-icons.css,bootstrap/css/bootstrap-custom.css,bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css,bootstrap-fileinput/bootstrap-fileinput.css,jquery-tags-input/jquery.tagsinput.css,bootstrap-switch/css/bootstrap-switch.min.css,bootstrap-select/bootstrap-select.min.css,footable/footable.css,dropzone/css/dropzone.css,icons-files/file.css,baidumap/searchinfowindow.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/css&f=core.css,components.css,plugins.css,style.css" rel="stylesheet" type="text/css" media="screen" />
<link href="/Public/admin/assets/css/login.css" rel="stylesheet" type="text/css"/>

<link rel="shortcut icon" href="/Public/admin/assets/img/favicon.png"/>
<link rel="apple-touch-icon" sizes="57*57" href="/Public/admin/assets/img/phoneicon.png">
<link rel="apple-touch-icon" sizes="72*72" href="/Public/admin/assets/img/phoneicon.png">
<link rel="apple-touch-icon" sizes="114*114" href="/Public/admin/assets/img/phoneicon.png">
<link rel="apple-touch-icon" sizes="144*144" href="/Public/admin/assets/img/phoneicon.png">
<script>
	var t = document.getElementById('container');
	if(t){
		window.location.href = "/admin";
	}
</script>
</head>
<body class="login">
<div class="preloading-container">
	<div class="login-container">
	<div class="logo fade-in-up">
		<a href="/">
		<img src="/Public/admin/assets/img/logo-big-white.png" alt=""/>
		</a>
	</div>
	<div class="loading-text">
		系统载入中...
	</div>
	<div class="content fade-in-up">
		<form class="login-form" method="post" action="<?php echo ($host_name); ?>/login">
	    <?php if($errormsg != ''): ?><div class="alert alert-danger fade-in-up">
				<button class="close" data-close="alert"></button>
				<span><?php echo ($errormsg); ?></span>
			</div><?php endif; ?>
			<div class="form-group">
				<label class="control-label visible-ie8 visible-ie9">账号</label>
				<input id="user" class="form-control form-control-solid  placeholder-no-fix" type="text" autocomplete="off" placeholder="账号" name="username" value="<?php echo ($cookie_upwd["username"]); ?>"/>
			</div>
			<div class="form-group">
				<label class="control-label visible-ie8 visible-ie9">密码</label>
				<input id="pass" class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="密码" name="password" value="<?php echo ($cookie_upwd["userpwd"]); ?>"/>
			</div>
			<div class="form-actions">
				<button id="submit" class="btn btn-primary btn-block uppercase btn-lg">登 录</button>
			</div>
		</form>
	</div>
	</div>
</div>
<!--[if lt IE 9]>
<script src="/Public/admin/assets/js/plugins/respond.min.js"></script>
<script src="/Public/admin/assets/js/plugins/excanvas.min.js"></script> 
<![endif]-->
<script src="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/plugins&f=jquery.min.js,jquery.cookie.min.js,jquery.validate.js,jquery.bgiframe.js"></script>
<script src="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/plugins&f=bootstrap/js/bootstrap.js,lazyload.js,bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js,bootstrap-select/bootstrap-select.min.js,jquery-tags-input/jquery.tagsinput.min.js,bootstrap-switch/js/bootstrap-switch.min.js,dropzone/dropzone.js"></script>
<script>
	$(function(){
		$("#submit").on('click',function(e){
			///e.preventDefault();
			$(".preloading-container").addClass('loading');
			setTimeout(function(){
				$(".login-form").submit();
			},400)
		})
	})
</script>

</body>
</html>