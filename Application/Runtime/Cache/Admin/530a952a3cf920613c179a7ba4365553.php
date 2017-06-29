<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>

	<head>
		<meta charset="UTF-8">
		<title>Insert title here</title>
	</head>
	<style type="text/css">
		.pic {
			margin-bottom: 20px;
		}
        .pic img{
          width:100%;
          height:100%;
        }
		
		.pis_top {
			width: 100%;
			height: 30px;
			background-color: #878787;
			color: white;
			line-height: 30px;
			margin-bottom: 20px;
		}
		
		.imga {
			display: inline-block;
			width: 168px;
			height: 100px;
			margin-left: 52px;
			border: 1px solid;
			float: left;
			text-align: center;
			line-height: 100px;
			color: white;
			background-color: #78909c;
		}
		
		.texta_val {
			width: 65%;
			height: 100px;
			margin-left: 52px;
			border: 1px solid black;
		}
		
		.tianjia {
			width: 168px;
			height: 80px;
			border: 1px dashed black;
			text-align: center;
			line-height: 80px;
			margin-left: 100px;
			margin-bottom: 30px;
			cursor: pointer;
		}
		
		.dela {
			font-size: 20px;
			margin-left: 18px;
		}
		 .nr{
    width: 100%;
  }
  .xuan{
    width: 94px;
    height: 30px;
    border: 1px solid black;
    margin-left: 10px;
    text-align: center;
    line-height: 30px;
    float: left;
    margin-top: 8px;
  }
  .xuan .marg{
    margin-left: 5px;
  }
  .quanbu_nr{
    width: 100%;
    margin-bottom: 20px;
    float: left;
  }
  .baise{
    background-color: white;
  }
  .biaolist i{display:none}
  .actives{
		background-color: darkgray;
	}
	.fooet{
		width: 100%;
		margin-bottom: 20px;
		margin-top: 35px;
		margin-left: 43%;
	}
	</style>
<script src="../../../../Public/admin/assets/js/page.js"></script>
	<body>
    <form method="post" action="<?php echo ($host_name); ?>/article/doAddPictures" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
      <input type="hidden" name="taginfo" id="hid"  value="<?php echo ($taginfod); ?>"/>
      <input type="hidden" id="pagenum" />
      <input type="hidden" id="pagetotal" value="<?php echo ($pagecount); ?>">
      <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">
      <input type="hidden" name="ctype" value="2">
		<h2 style="text-align: center;margin-top: 15px;font-size: 20px;">添加图集</h2>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label" style="width: 58px;margin-left: 45px; margin-top: 10px;">
          分类:
        </label>
        <div class="col-xs-12 col-sm-10">
          <select name="cate" class="form-control bs-select" title="请选择..." required>

            <?php if(is_array($vcainfo)): $i = 0; $__LIST__ = $vcainfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>" <?php if($row['id'] == $vinfo['category_id']): ?>selected<?php endif; ?> > <?php echo ($row['name']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>

          </select>
        </div>
      </div>
		<div class="form-group" style="margin-top: 30px;margin-left: 30px;">
			<!--标题-->
			<span>标题:</span>
			<input style="margin-left: 20px;" type="text" name="title" />
		</div>
		<div class="form-group">
			<!--封面图-->
			<label class="col-xs-12 col-sm-2 control-label" style="width: 58px;margin-left: 45px;">封面图:</label>
			<div class="col-xs-12 col-sm-10">
				<div class="fileinput fileinput-new" data-fileinput="">
					<div class="fileinput-preview thumbnail" data-trigger="fileinput">
						<a data-target="#modal-file" href="javascript:void(0)">
							<img id="media_idimg" src="/Public/admin/assets/img/noimage.png" border="0">
							<span id="media_idimgname"></span>
						</a>
					</div>
					<div>
						<a class="btn btn-success btn-file" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadResource?filed=media_id&rtype=2" data-browse-file="">
							选择图片
						</a>
						<input type="hidden" name="media_id" id="media_id" value="">
						<a href="javascript:;" class="btn btn-danger" data-remove-file="/Public/admin/assets/img/noimage.png">
							删除 </a>
					</div>
				</div>
			</div>
		</div>
		<div class="pic_list" style="width: 96%;margin: 0 auto;height: 360px;margin-top: 45px;overflow: auto;border: 1px solid black;margin-bottom: 20px;">
			<div class="pis_top">
				<span style="margin-left: 15px;">目标序号</span>
				<span style="margin-left: 40px;">图片</span>
				<span style="margin-left: 162px;">内容描述</span>
			</div>
			<div class="pic">
				<span style="float: left;margin-left: 33px;">1</span>
				<a class="imga" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadMapResource?filed=pics_map_1&rtype=2" data-browse-file>
                  <img id="pics_map_1img" src="" border="0" />
				</a>
              <input type="hidden" id="pics_map_1" name="pics_map_1" />
				<textarea name="texta_pic_1" class="texta_val"></textarea>
			</div>
          <div class="pic">
            <span style="float: left;margin-left: 33px;">2</span>
            <a class="imga" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadMapResource?filed=pics_map_2&rtype=2" data-browse-file>
              <img id="pics_map_2img" src="" border="0" />
            </a>
            <input type="hidden" id="pics_map_2" name="pics_map_2" />
            <textarea name="texta_pic_2" class="texta_val"></textarea>
          </div>
          <div class="pic">
            <span style="float: left;margin-left: 33px;">3</span>
            <a class="imga" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadMapResource?filed=pics_map_3&rtype=2" data-browse-file>
              <img id="pics_map_3img" src="" border="0" />
            </a>
            <input type="hidden" id="pics_map_3" name="pics_map_3" />
            <textarea name="texta_pic_3" class="texta_val"></textarea>
          </div>
          <div class="pic">
            <span style="float: left;margin-left: 33px;">4</span>
            <a class="imga" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadMapResource?filed=pics_map_4&rtype=2" data-browse-file>
              <img id="pics_map_4img" src="" border="0" />
            </a>
            <input type="hidden" id="pics_map_4" name="pics_map_4" />
            <textarea name="texta_pic_4" class="texta_val"></textarea>
          </div>
          <div class="pic">
            <span style="float: left;margin-left: 33px;">5</span>
            <a class="imga" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadMapResource?filed=pics_map_5&rtype=2" data-browse-file>
              <img id="pics_map_5img" src="" border="0" />
            </a>
            <input type="hidden" id="pics_map_5" name="pics_map_5" />
            <textarea name="texta_pic_5" class="texta_val"></textarea>
          </div>

			<div class="tianjia">
				添加
			</div>
		</div>
		
		
		<div class="form-group row" id="biaoqian">
            <label class="col-xs-12 col-sm-2 control-label" style="width: 71px;margin-left:36px ;">
              选择标签:
            </label>
            <button type="button" id="tianjia" class="btn btn-primary" style="margin-left: 20px; background-color: #aed316;height: 25px;line-height: 12px;" data-toggle="modal" data-target="#myModa">添加标签</button>
            <div style="width: 100%;" class="biaolist">


              <?php if(is_array($tagaddart)): $i = 0; $__LIST__ = $tagaddart;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><div class="xuan a" id="<?php echo ($row['tagid']); ?>">
                  <?php echo ($row['tagname']); ?>											<i class="fa fa-close marg"></i></div><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
            <div class="modal fade" id="myModa" style="" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button style="width:20px;height:20px;" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">添加标签</h4>
                  </div>
                  <div class="modal-body">
                    <div class="mod_top">
                      <div class="form-group">
                        <button type="button" class="btn btn-primary" style="margin-left: 20px;height: 25px;line-height: 12px;" data-toggle="modal" data-target="#myModaa">添加标签</button>
                        <div class="nr">

                        </div>
                      </div>
                      <div class="form-group">
                        <input type="text" class="soushuo" style="margin-left: 61px;height: 28px;width: 410px;"/>
							        			<span class="input-group-btn" style="display:inline-block;">
							              <button class="btn btn-primary" style="height: 26px;line-height: 2px; background-color:#2988e6 ;" type="button" id="sdd"><i class="fa fa-search"></i></button>
							            </span>
                        <div class="quanbu_nr">


                          <?php if(is_array($pageinfo)): $i = 0; $__LIST__ = $pageinfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><input class="xuan baise" type="button" value="<?php echo ($row['tagname']); ?>" id="<?php echo ($row['id']); ?>" tname="<?php echo ($row['tagname']); ?>" /><?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                        <div id="example" style="margin-left: 113px;"></div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="yes2" style="margin-left: 35%;height: 26px;line-height: 14px;">确定</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="nos" style="margin-left: 15px;height: 26px;line-height: 14px;">取消</button>
                  </div>

                </div>
              </div>
            </div>

            <!--小-->
            <div style="" class="modal fade" id="myModaa" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button style="width:20px;height:20px;" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">添加标签</h4>
                  </div>
                  <div class="modal-body">
                    <div class="mod_top">
                      <div class="form-group">
                        <input type="text" class="form-control" id="exampleInputName2" placeholder="标签名">
                      </div>
                    </div>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="yes" style="margin-left: 35%;height: 26px;line-height: 14px;">确定</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" style="margin-left: 15px;height: 26px;line-height: 14px;">取消</button>
                  </div>

                </div>
              </div>
            </div>
          </div>
          <!--来源-->
          <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label" style="width: 71px;margin-left:36px;line-height: 30px;">
                来源：
              </label>
              <div class="col-xs-12 col-sm-10">
                <!-- <input name="source" type="text" value="<?php echo ($vainfo["source"]); ?>"  minlength="2" maxlength="30" class="form-control" /> -->
              	<select name="source_id" class="form-control bs-select" title="请选择..." required>

					<?php if(is_array($sourcelist)): $i = 0; $__LIST__ = $sourcelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>" <?php if($row['id'] == $vinfo['source_id']): ?>selected<?php endif; ?> > <?php echo ($row['name']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>

				</select>
              </div>
            </div>
            <!--预约发布-->
            <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label" style="width: 71px;margin-left:36px;line-height: 30px;">
                预约发布：
              </label>
              <div class="col-xs-12 col-sm-10">
                <div class="input-group date form_datetime" data-date="<?php echo ($vainfo["bespeak_time"]); ?>">
                  <input name="logtime" type="text" size="16" class="form-control date" placeholder="开始日期" value="<?php echo ($vainfo["bespeak_time"]); ?>" readonly>
                  <span class="input-group-btn">
                    <button class="btn default date-reset" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
                </div>
              </div>
            </div>
          <!--  <br />
     <div class="modal-footer">
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-primary" type="submit">保存</button>
    </div>-->
    <div class="fooet">
    	<button class="btn btn-default close-m" type="button" style="margin-right: 40px;">取消</button>
      <button id="savepicmap" class="btn btn-primary" type="submit">保存</button>
    </div>
      <input type="hidden" name="pictuji" id="pictuji"  value=""/>
      </form>
	</body>

</html>
<script type="text/javascript">

  $("#savepicmap").click(function() {
   // alert(111);
    var arr_nr = [];
    $('.pic_list .pic').each(function(){

      var obj_nr = {};
      obj_nr.aid = $(this).find("input").val();
      obj_nr.atext = $(this).find("textarea").val();
      if(obj_nr.aid != 0){
        arr_nr.push(obj_nr);
      }
    });
    var arr_nrs = JSON.stringify(arr_nr);
    console.log(arr_nrs);
    $("#pictuji").val(arr_nrs);
  });

	$('.tianjia').click(function() {

		var a = $('.pic:last').find('span').html();
		if(a == '50') {
			alert('图集不能超过50')
		} else {
			var num = parseInt(a) + 1;

          var button="<input type='hidden' id='pics_map_"+num+"' name='pics_map_"+num+ "' />";

			var imghtml = '<div class="pic"><span style="float: left;margin-left: 33px;">' + num + '</span><a class="imga" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadMapResource?filed=pics_map_'+num+'&rtype=2" data-browse-file> <img id="pics_map_'+num+'"img"  src="" border="0" /></a>'+button+'<textarea name="texta_pic_'+num+'"  class="texta_val"></textarea><i class="fa fa-trash dela"></i></div> ';
			$(this).before(imghtml);
		}
	})

	$('body').on('click', '.dela', function() {
		$(this).parent().remove();
	})
	$('textarea').keydown(function() {
		var textval = $(this).val().length;
		if(textval >= 60) {
			$(this).val($(this).val().substring(0, 60));
		}
	})
	
	
/*标签*/
 $('#yes').click(function(){
    var biaoname = $('#exampleInputName2').val();
    if(biaoname==''){
      alert('请添加标签名称!');
      return false;
    }else if(biaoname.length==1){
      alert('输入少于2个字符，请重新输入！');
      return false;
    }else if(biaoname.length>=7){
      alert('最多只能输入6个字符，请重新输入！');
      return false;
    }else{
      $.ajax({
        type:"POST",
        url:"<?php echo ($host_name); ?>/tag/doAddAjaxTag",
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
            var biaohtml = '<div class="xuan">'+biaoname+'<i class="fa fa-close marg"></i></div>';
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
    $('#hid').val(arr_nrs)
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
        url:"<?php echo ($host_name); ?>/tag/getajaxpage",
        dataType: "json",
        data:"pageNum="+page,
        async:true,
        success:function(call){
          console.log(call)
          var calls = call.list;
          $('#pagenum').val(call.page)
          for(var i=0;i<calls.length;i++){

            var quanbu_nrs = '<input class="xuan baise" type="button" value="'+calls[i].tagname+'" id="'+calls[i].id+'" tname="'+calls[i].tagname+'" />';
            $('.quanbu_nr').append(quanbu_nrs);
          }
          activer();
        }
      });
    }
  }

  $('#example').bootstrapPaginator(options);

  /*搜索*/
  $('#sdd').click(function(){
    $('#pagetotal').val('1');
    var soushuo = $('.soushuo').val();
    $('.quanbu_nr').html('');
    console.log(soushuo)
    $.ajax({
      type:"post",
      url:"<?php echo ($host_name); ?>/tag/getajaxpage",
      dataType: "json",
      data:"fatagname="+soushuo+"&pageNum=1",
      async:true,
      success:function(call){
        console.log(call)
        var calls = call.list;
        for(var i=0;i<calls.length;i++){
          var quanbu_nrs = '<input class="xuan baise" type="button" value="'+calls[i].tagname+'" id="'+calls[i].id+'" tname="'+calls[i].tagname+'" />';
          $('.quanbu_nr').append(quanbu_nrs);
        }
						activer();
        var options = {
          currentPage: 1,//显示当前页数
          totalPages: call.page,  //总页数
          numberOfPages:5,  //显示几页
          onPageClicked: function (event, originalEvent, type, page) {
            //alert(page);
            $('.quanbu_nr').html('');
            $.ajax({
              type:"get",
              url:"<?php echo ($host_name); ?>/tag/getajaxpage",
              dataType: "json",
              data:"pageNum="+page,
              async:true,
              success:function(call){
                console.log(call)
                var calls = call.list;
                $('#pagenum').val(call.page)
                for(var i=0;i<calls.length;i++){
                  var quanbu_nrs = '<input class="xuan baise" type="button" value="'+calls[i].tagname+'" id="'+calls[i].id+'" tname="'+calls[i].tagname+'" />';
                  $('.quanbu_nr').append(quanbu_nrs);
                }
                activer();
              }
            });
          }
        }
        $('#example').bootstrapPaginator(options);
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
             			console.log(dis);
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

    })
  })
</script>