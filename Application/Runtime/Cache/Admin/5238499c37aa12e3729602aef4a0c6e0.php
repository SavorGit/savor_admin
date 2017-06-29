<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="renderer" content="webkit">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title>寻味管理系统</title>
  <link href="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/plugins&f=font-awesome/css/font-awesome.min.css,simple-line-icons/css/simple-line-icons.css,bootstrap/css/bootstrap-custom.css,bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css,bootstrap-fileinput/bootstrap-fileinput.css,jquery-tags-input/jquery.tagsinput.css,bootstrap-switch/css/bootstrap-switch.min.css,bootstrap-select/bootstrap-select.min.css,footable/footable.css,dropzone/css/dropzone.css,icons-files/file.css,baidumap/searchinfowindow.min.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/css&f=core.css,components.css,plugins.css,style.css" rel="stylesheet" type="text/css" />

  <link rel="shortcut icon" href="/Public/admin/assets/img/favicon.png"/>
  <link rel="apple-touch-icon" sizes="57*57" href="/Public/admin/assets/img/phoneicon.png">
  <link rel="apple-touch-icon" sizes="72*72" href="/Public/admin/assets/img/phoneicon.png">
  <link rel="apple-touch-icon" sizes="114*114" href="/Public/admin/assets/img/phoneicon.png">
  <link rel="apple-touch-icon" sizes="144*144" href="/Public/admin/assets/img/phoneicon.png">

</head>

<body scroll="no">
<?php if(is_array($ico_arr)): $k = 0; $__LIST__ = $ico_arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ico): $mod = ($k % 2 );++$k;?><input type="hidden" id="menu_<?php echo ($ico["id"]); ?>" value="<?php echo ($ico["img_url"]); ?>" /><?php endforeach; endif; else: echo "" ;endif; ?>
<input type="hidden" id="xlicon" value="/Public/admin/assets/img/sysmenuico/more_zhankai.png" />
<input type="hidden" id="xlmricon" value="/Public/admin/assets/img/sysmenuico/more.png" />
  <input id="host_name" value="<?php echo ($host_name); ?>" type="hidden"/>
  <input id="site_host_name" value="<?php echo ($site_host_name); ?>" type="hidden"/>
  <div class="preloading-container loading">
    <div class="login-container">
      <div class="logo">
        <a>
        <img src="/Public/admin/assets/img/logo-big-white.png" alt=""/>
        </a>
      </div>
      <div class="loading-text">
        系统载入中...
      </div>
    </div>
  </div>
  <div id="layout">
    <div id="header">
      <div class="headerNav">
        <a class="logo1" href="#"></a>
        <a class="logo2 active" href="#">小热点</a>
        <ul class="nav">
          <li><span><i class="icon-user"></i> <?php echo ($sysuerinfo["username"]); ?></span></li>
          <li><a mask="true" target="dialog" href="<?php echo ($host_name); ?>/user/chagePwd"><i class="icon-lock"></i> <span class="hidden-xs">修改密码</span></a></li>
          <li><a href="<?php echo ($host_name); ?>/login/logout"><i class="icon-logout"></i> <span class="hidden-xs">安全退出</span></a></li>
        </ul>
      </div>
      <!-- navMenu -->
    </div>
    <div id="sidebar" class="active">
      <div class="heading">
        
      <div class="menu-container">
        <ul class="main-menu" style="margin-top:12px;">
          <?php if($menuList != ''): echo ($menuList); ?>
      <?php else: ?>
        <li>
          <span>暂无菜单</span>
        </li><?php endif; ?>
        </ul>
      </div>
    </div>
    </div>
    <div id="container" class="active">
      <div id="navTab" class="tabsPage">
        <div class="tabsPageHeader">
          <div class="tabsPageHeaderContent">
            <ul class="navTab-tab">
              <li tabid="main" class="main"><a href="javascript:;"><span><i class="icon-home"></i> 系统信息</span></a></li>
            </ul>
          </div>
          <div class="tabsLeft"><i class="icon-arrow-left"></i></div>
          <!-- 禁用只需要添加一个样式 class="tabsLeft tabsLeftDisabled" -->
          <div class="tabsRight"><i class="icon-arrow-right"></i></div>
          <!-- 禁用只需要添加一个样式 class="tabsRight tabsRightDisabled" -->
          <div class="tabsMore" tabIndex="1"><i class="icon-options-vertical"></i></div>
          <div class="tabsSideNav" tabIndex="1"><i class="fa"></i></div>
        </div>
        <ul class="tabsMoreList">
          <li><a href="javascript:;">系统信息</a></li>
        </ul>
        <div class="navTab-panel tabsPageContent layoutBox">
          <div class="page unitBox">
            <div class="pageContent autoflow">
              <div class="accountInfo clearfix">
                <p class="form-control-static text-danger">提示：您好,您本次登录的时间为 <strong class="login-time"><?php echo date("Y年m月d日 H:i:s");?></strong>&nbsp;&nbsp;当前IP地址为 <strong class="login-time"><?php echo ($_SERVER['REMOTE_ADDR']); ?></strong></p>
                <div class="pull-right index-top-op">
                  <a warn="警告" title="你确定要清理缓存吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/clean/cache" calback="navTabAjaxDone" class="btn btn-danger"><i class="fa fa-trash"></i> 清理缓存</a>
                </div>
              </div>
              <div class="indexContent">
                <div class="row sm-row">
                    <div class="portlet success-box box">
                      <div class="portlet-title">
                        <div class="caption">
                          <i class="fa fa-cogs"></i>系统信息
                        </div>
                      </div>
                      <div class="portlet-body">
                        <ul class="info-list">
                          <li>运行环境: <span>LNMP/LAMP</span></li>
                          <li>PHP版本: <span><?php echo ($VerPHP); ?></span></li>
                          <li>MYSQL版本: <span><?php echo ($VerMysql); ?></span></li>
                          <li>服务器IP: <span><?php echo ($_SERVER['SERVER_ADDR']); ?></span></li>
                          <li>服务器时间: <span><?php echo date("Y年m月d日 H:i:s");?></span></li>
                          <li>当前访问地址: <span><?php echo ($_SERVER['HTTP_HOST']); ?></span></li>
                        </ul>
                      </div>
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  <div id="modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Modal title</h4>
        </div>
        <div class="modal-body">
          <p>One fine body&hellip;</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <div id="modal-file" class="modal fade modal-file">
    <div class="modal-table">
      <div class="modal-cell">
        <div class="dialog modal-dialog">
          <div class="modal-content">
            
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="modal-view" class="modal fade">
    <div class="modal-table">
      <div class="modal-cell">
        <div class="dialog modal-dialog fullscreen">
          <div class="modal-content" view="full">
            <div class="dialogHeader modal-header">
              <a class="close-m" data-dismiss="modal"></a>
              <div class="screen-view pull-right hidden-xs">
                <select id="screen" class="form-control input-sm hidden-xs">
                  <?php $_result=C('MOBILE_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" data-width="<?php echo ($vo["w"]); ?>" data-height="<?php echo ($vo["h"]); ?>"><?php echo ($vo["t"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                  <option value="0" selected>全屏</option>
                </select>
                <div class="screen-info hidden">
                  <span class="screen-width"></span>
                  <span>x</span>
                  <span class="screen-height"></span>
                  <span class="screen-change"><i class="fa fa-refresh"></i></span>
                </div>
              </div>
              <div class="label-control pull-right hidden-xs">模拟器：</div>
              <h1 class="fa-globe">页面浏览</h1>
            </div>
            <div id="web-view">
              <iframe></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="modal-file-ueditor" class="modal fade modal-file">
    <div class="modal-table">
      <div class="modal-cell">
        <div class="dialog modal-dialog">
          <div class="modal-content">
            
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="modal-attachfiles" class="modal fade modal-file">
    <div class="modal-table">
      <div class="modal-cell">
        <div class="dialog modal-dialog">
          <div class="modal-content">
            
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /.modal -->
  <div id="footer">&copy;  Copyright <a target="_blank" href="#">北京寻味传媒集团</a></div>
  <script src="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/plugins&f=jquery.min.js,jquery.cookie.min.js,jquery.validate.js,jquery.bgiframe.js"></script>
  <script src="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/plugins&f=bootstrap/js/bootstrap.js,lazyload.js,bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js,bootstrap-select/bootstrap-select.min.js,jquery-tags-input/jquery.tagsinput.min.js,bootstrap-switch/js/bootstrap-switch.min.js,footable/footable.js,dropzone/dropzone.js,hammer.min.js,jquery.hammer.js"></script>
  <script type="text/javascript" src="<?php echo ($site_host_name); ?>/min?b=./Public/admin/ueditor&f=ueditor.config.js,ueditor.all.js"></script>
  <script src="<?php echo ($site_host_name); ?>/min?b=./Public/admin/assets/plugins/dwzjs&f=dwz.js,dwz.regional.zh.js" type="text/javascript"></script>
  <script src='/Public/admin/assets/js/oss/plupload.full.min.js'></script>
  <script src="/Public/admin/assets/js/main.js"></script>
  <script type="text/javascript">
  function addEvent(elem, event, fn) {
      if (elem.addEventListener) {
          elem.addEventListener(event, fn, false);
      } else {
          elem.attachEvent("on" + event, function() {
              // set the this pointer same as addEventListener when fn is called
              return(fn.call(elem, window.event));   
          });
      }
  }
  $(function() {
    DWZ.init({
      loginUrl:"/admin/Login/login", //跳到登录页面
      statusCode:{ok:1,error:0},
      keys:{statusCode:"status", message:"info"},
      pageInfo:{pageNum:"pageNum", numPerPage:"numPerPage", orderField:"_order", orderDirection:"_sort"}, //【可选】
      debug:false, // 调试模式 【true|false】 //【可选】
      callback: function() {
        initEnv();
      }
    });
    //顶部滚动文字提示
    function textLeft1() {
      var left = parseInt($('.top-tips1 p').css('left'));
      if(left<-$('.top-tips1 p').width()) {
        left="100%";
      }else {
        left -= 1;
      }
      $('.top-tips1 p').css('left',left);
    }
    setInterval(textLeft1,30);
    function textLeft2() {
      var left = parseInt($('.top-tips2 p').css('left'));
      if(left<-$('.top-tips2 p').width()) {
        left="100%";
      }else {
        left -= 1;
      }
      $('.top-tips2 p').css('left',left);
    }
    setInterval(textLeft2,30);
  });
  function echartLoaded(){
    $("#echart-script").addClass("loaded",1);
  }
  function loadEcharts() {
    var script = document.createElement("script");
    script.id = "echart-script";
    script.type = "text/javascript";
    script.src = "/Public/admin/assets/plugins/echart/echarts-all.js";
    document.body.appendChild(script);
    addEvent(script,'load', echartLoaded);
  }


  function moveOption(left, right,left2,right2)
  {
    for(var i = left.options.length - 1 ; i >= 0 ; i--)
    {
      if(left.options[i].selected)
      {
        var opt = new Option(left.options[i].text,left.options[i].value);
        opt.selected = true;
        right.options.add(opt);
        //left.remove(i);

        var opt2 = new Option(left2.options[i].text,left2.options[i].value);
        opt2.selected = true;
        right2.options.add(opt2);
        //left2.remove(i);
      }
    }
  }

  function moveOption_ri(left, right,left2,right2)
  {
    for(var i = right.options.length - 1 ; i >= 0 ; i--)
    {
      if(right.options[i].selected)
      {
        var opt = new Option(right.options[i].text,right.options[i].value);
        opt.selected = true;
        left.options.add(opt);
        right.remove(i);

        var opt2 = new Option(right2.options[i].text,right2.options[i].value);
        opt2.selected = true;
        left2.options.add(opt2);
        right2.remove(i);
      }
    }
  }

  //置顶
  function  moveTop(obj,obj2)
  {
    var  opts = [];
    var  opts2 = [];
    for(var i =obj.options.length -1 ; i >= 0; i--)
    {
      if(obj.options[i].selected)
      {
        opts.push(obj.options[i]);
        obj.remove(i);

        opts2.push(obj2.options[i]);
        obj2.remove(i);
      }
    }
    var index = 0 ;
    for(var t = opts.length-1 ; t>=0 ; t--)
    {
      var opt = new Option(opts[t].text,opts[t].value);
      opt.selected = true;
      obj.options.add(opt, index);

      var opt2 = new Option(opts2[t].text,opts2[t].value);
      opt2.selected = true;
      obj2.options.add(opt2, index++);
    }
  }
  //置底


  function  moveBottom(obj,obj2)
  {
    var  opts = [];
    var  opts2 = [];
    for(var i =obj.options.length -1 ; i >= 0; i--)
    {
      if(obj.options[i].selected)
      {
        opts.push(obj.options[i]);
        obj.remove(i);

        opts2.push(obj2.options[i]);
        obj2.remove(i);
      }
    }
    for(var t = opts.length-1 ; t>=0 ; t--)
    {
      var opt = new Option(opts[t].text,opts[t].value);
      opt.selected = true;
      obj.options.add(opt);


      var opt2 = new Option(opts2[t].text,opts2[t].value);
      opt2.selected = true;
      obj2.options.add(opt2);
    }
  }

  function moveUp(obj,obj2)
  {
    //最上面的一个不需要移动，所以直接从i=1开始
    for(var i=1; i < obj.length; i++) {
      if(obj.options[i].selected)
      {
        if(!obj.options.item(i-1).selected)
        {
          var selText = obj.options[i].text;
          var selValue = obj.options[i].value;
          obj.options[i].text = obj.options[i-1].text;
          obj.options[i].value = obj.options[i-1].value;
          obj.options[i].selected = false;
          obj.options[i-1].text = selText;
          obj.options[i-1].value = selValue;
          obj.options[i-1].selected=true;



          var selText = obj2.options[i].text;
          var selValue = obj2.options[i].value;
          obj2.options[i].text = obj2.options[i-1].text;
          obj2.options[i].value = obj2.options[i-1].value;
          obj2.options[i].selected = false;
          obj2.options[i-1].text = selText;
          obj2.options[i-1].value = selValue;
          obj2.options[i-1].selected=true;
        }
      }
    }
  }

  function moveDown(right,right2)
  {
    for(var i = right.length -2 ; i >= 0; i--){
    //向下移动，最后一个不需要处理，所以直接从倒数第二个开始
      if(right.options[i].selected)
      {
        if(!right.options[i+1].selected)
        {
          var selText = right.options[i].text;
          var selValue = right.options[i].value;
          right.options[i].text = right.options[i+1].text;
          right.options[i].value = right.options[i+1].value;
          right.options[i].selected = false;
          right.options[i+1].text = selText;
          right.options[i+1].value = selValue;
          right.options[i+1].selected=true;


          var selText = right2.options[i].text;
          var selValue = right2.options[i].value;
          right2.options[i].text = right2.options[i+1].text;
          right2.options[i].value = right2.options[i+1].value;
          right2.options[i].selected = false;
          right2.options[i+1].text = selText;
          right2.options[i+1].value = selValue;
          right2.options[i+1].selected=true;
        }
      }
    }
  }

  //删除
  function deleteSelectItem(right,right2)
  {
    for(var i=0; i<right.options.length; i++)
    {
      if(i>=0 && i<=right.options.length-1 && right.options[i].selected)
      {
        right.options[i] = null;
        right2.options[i] = null;
        i --;
      }
    }
  }

  //复制
  function copyRow(){
    var selOpt = document.getElementById('right');
    var selOpt2 = document.getElementById('right2');
    for(i=0;i<selOpt.length;i++) {
      if (selOpt[i].selected == true) {
        var value = selOpt[i].value;//获取当前选择项的值.
        var value2 = selOpt2[i].value;//获取当前选择项的值.
      }
    }
    console.log(value,value2);
    for(i=0;i<selOpt.length;i++) {
      if (selOpt[i].selected == true) {
        var text = selOpt[i].text;//获取当前选择项的文本.
        var text2 = selOpt2[i].text;//获取当前选择项的文本.
      }

    }
    console.log(text,text2);
    selOpt.add(new Option(text,value));
    selOpt2.add(new Option(text2,value2));
  }
  window.onload = function(){
      loadEcharts();
  }
  /*菜单显示和隐藏*/
  $('.collapse').hide()
  $('body').on('click','.main-menu>li>a',function(){
  	$(this).next().toggle(200);
  
  	$('.main-menu>li>a').css({'color':'#ffffff','border-left':'none'})
  	$(this).css({'color':'#fe8332','border-left':'2px solid #fe8332'});
  });
  var xlmoren_icon = '';
  
  
  $('.main-menu>li').click(function(){
	  
	  	var $icolistid = $(this).attr('id');  //的id
	  	var xl_icon = $('#xlicon').val();
	  	var xlmr_icon = $('#xlmricon').val();
	  	var aid = '#menu_'+$icolistid;
	  	var $inputsrc = $(aid).val();
	  	 
	  	$(this).children().eq(0).children().eq(0).attr('src',$inputsrc);//ico的图片地址
	  	$(this).children().eq(0).children().eq(1).attr('src',xl_icon);
	  	
	  	$('.main-menu>li').each(function(){
	  	 	var seid = $(this).attr('id');
	  		if($icolistid !=seid){
	  			var moren = $(this).children().eq(1).children().eq(0).attr('value');
	  			$("#"+seid).children().eq(0).children().eq(0).attr('src',moren);
	  			$(this).css({'color':'#ffffff','border-left':'none'});
	  			$(this).children().eq(1).css('display','none');
	  			$(this).children().eq(0).children().eq(1).attr('src',xlmr_icon);
	  		}
	  	});
	  	
	  })
  </script>
</body>

</html>