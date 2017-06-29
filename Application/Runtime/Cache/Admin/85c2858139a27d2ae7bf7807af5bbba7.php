<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<script>  
    if(!window.jQuery){
      var path = window.location.pathname;
      path = path.replace("/admin/","");
      console.log(path);
      window.location.href = "<?php echo ($host_name); ?>#" + path;
    }
</script>

<link href="/Public/admin/assets/css/fileinput.css" rel="stylesheet" type="text/css" />
<script src="/Public/admin/assets/js/fileinput.min.js" type="text/javascript" />
<style type="text/css">
  .entirety{
    margin: 0 auto;
    width: 500px;
  }
  .bom_input{
    margin-top: 15px;
  }
</style>
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/checkaccount/doaddCheckAccount" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDoneIce)">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">

    <div class="pageFormContent modal-body" style="z-index:10;">

      <div class="modal-dialog modal-lg" role="document">

          <div class="entirety">
            <div class="top_input" style="margin-top: 30px;margin-bottom: 20px;">
              <div class="form-group">
                <label style="margin-right: 25px;line-height: 20px;width: 88px;">费用类别:</label>
                <select name="fee" style="width: 160px;border-radius: 6px !important;">
                  <?php if(is_array($fee_list)): $i = 0; $__LIST__ = $fee_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"> <?php echo ($row); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>


                </select>
              </div>
              <div class="form-group">
                <label style="margin-right: 25px;line-height: 20px;width: 88px;">费用时间段:</label>


                  <input  type="date" name="starttime" style="margin-right: 20px;border-radius: 6px !important;float:left;"/>
                  <input  type="date" name="endtime"   style="border-radius: 6px !important;"/>



              </div>
              <div class="form-group">
                <label style="margin-right: 25px;line-height: 20px;">发票邮寄地址:</label>

                <select id="rec_addr" name="rec_addr" style="width: 160px;border-radius: 6px !important;margin-right: 10px;">
                  <option value="0">选择发票邮寄地址</option>
                  <?php if(is_array($account_config)): $i = 0; $__LIST__ = $account_config;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>"> <?php echo ($row['receipt_addr']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <select name="rec_tel" id="rec_tel" style="width: 160px;border-radius: 6px !important;">
                  <option value="0">收件人，电话</option>
                  <?php if(is_array($account_config)): $i = 0; $__LIST__ = $account_config;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>"> <?php echo ($row['receipt_tel']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
              </div>
              <div class="form-group">
                <label style="margin-right: 25px;line-height: 20px;width: 88px;">发票信息:</label>
                <select name="rec_head" id="rec_head" style="width: 160px;border-radius: 6px !important;margin-right: 10px;">
                  <option value="0">发票抬头</option>
                  <?php if(is_array($account_config)): $i = 0; $__LIST__ = $account_config;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>"> <?php echo ($row['receipt_head']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <select name="rec_taxnum" id="rec_taxnum" style="width: 160px;border-radius: 6px !important;">
                  <option value="0">纳税人识别号</option>
                  <?php if(is_array($account_config)): $i = 0; $__LIST__ = $account_config;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>"> <?php echo ($row['receipt_taxnum']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
              </div>
              <h3 style="margin-bottom: 15px;font-size: 15px;">酒楼明细</h3>
              <button type="button" class="btn btn-primary" data-toggle="modal" id="checkout" aria-pressed="false" autocomplete="off" style="border-radius: 5px !important;" data-target=".bs-example-modal-sm"  class="btn btn-primary btn-xs">
                导入酒楼金额明细
              </button>

              <div class="bom_input">
                <table id="hoteldetail" class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
                  <thead>
                  <tr>
                    <th>酒楼id</th>
                    <th>酒楼名称</th>
                    <th>金额</th>
                  </tr>
                  </thead>
                  <tbody class="accountcl">
                  </tbody>
                </table>
                <textarea name="remark" placeholder="备注" style="width: 100%;height: 150px;border-radius: 5px !important;"></textarea>
              </div>
              <div class="form-group" style="margin-top: 20px;">
                <button type="submit" data-toggle="modal" data-target="#myModasm" class="btn btn-primary" style="margin-left: 25%;height: 26px;line-height: 14px;border-radius: 5px !important;">保存并发送通知</button>
                <button type="button" class="btn btn-primary close-m"  style="margin-left: 15px;height: 26px;line-height: 14px;border-radius: 5px !important;" >取消</button>

              </div>
            </div>

          </div>
        </div>


      <input  type="hidden"  name="accountjson" id="accountjson" value="">
    </div>
  </form>
</div>
</div>
<!-----发送信息
<div class="modal fade" style="z-index:999;" id="myModasm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button style="width:20px;height:20px;" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">发送结果</h4>
      </div>
      <div class="modal-body">
        <h2 style="text-align: center;font-size: 20px;margin-bottom: 35px;">发送结果</h2>
        <p style="margin-bottom: 10px;">发送成功30家酒楼，失败2家</p>
        <p>发送失败明细(2家)</p><br /><br /><br />
        <div class="err_jiulou">
          <span>花家怡园(id:30)</span><span style="margin-left: 15px;">失败原因:该酒楼不存在</span>
        </div>
      </div>
      <button type="button" class="btn btn-primary" data-dismiss="modal" style="margin-left: 47%;height: 26px;line-height: 14px;border-radius: 5px !important;margin-bottom: 15px;" id="ok">确定</button>
    </div>
  </div>
</div>
---->

<div id="mymodal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">

  <div class="modal-dialog modal-sm" role="document" style="width:600px;">
    <div class="modal-content">
      <form id="checkexcel" name="checkexcel"
            action="<?php echo ($host_name); ?>/menu/addmen" method="post" >
        <div class="form-group">
          <div class="form-group">
            <input id="file-4" type="file" class="file-loading" data-upload-url="<?php echo ($host_name); ?>/menu/getfile" data-allowed-file-extensions='["csv", "xlsx"]'>
          </div>
          <hr>
          <button name="excelsub" class="btn btn-primary" type="button" id="excelsub" data-dismiss="modal" disabled="disabled">Submit</button>

          <button  class="btn btn-default" style="margin-left:435px;" type="button" data-dismiss="modal">cancel</button>
            <input  type="hidden"  name="excelpath" id="excelpath" value="">
        </div>
      </form>
    </div>
  </div>
</div>


<div id="mymodal2" data-target="#mymodal2" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">导入结果</h4>
      </div>
      <div class="modal-body">
        <ul id="notinclude">

        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
  $('#ok').click(function(){

    $('#myModa').modal('hide');
  });

  $("#rec_addr").change(function() {
    var  gid = $("#rec_addr").val();
    if(gid > 0){
      $('#rec_tel').empty();
      $('#rec_head').empty();
      $('#rec_taxnum').empty();
      $.ajax({
        type: 'POST',
        //设置json格式,接收返回数组。
        dataType: 'json',
        url: '<?php echo ($host_name); ?>/checkaccount/getaccountinfo',
        //ajax传递当前选项的value值,也就是当前的region_id。
        data:"tid="+gid,
        success: function(data) {
          //如果返回值不为空则执行。
          if (data.code == 1) {
            var minfo =  data.list;
            console.log(minfo);
            var option_str1 = '<option value="'+minfo.id+'">'+minfo.receipt_tel+'</option>';
            var option_str2 = '<option value="'+minfo.id+'">'+minfo.receipt_head+'</option>';
            var option_str3 = '<option value="'+minfo.id+'">'+minfo.receipt_taxnum+'</option>';
            $('#rec_tel').append(option_str1);
            $('#rec_head').append(option_str2);
            $('#rec_taxnum').append(option_str3);
          } else {

          }
        }

      })
    }else{
      return false;
    }

  });


  $("#checkout").click(function () {

    $('#file-4').fileinput('clear');
    $("#excelpath").val('');
  });


  $("#file-4").fileinput({
    uploadExtraData: {kvId: '10'},
    maxFileCount: 1,
    allowedFileExtensions:['csv', 'xlsx'],
  });

  $('#file-4').on('fileselectnone', function() {
    //alert('Huh! You selected no files.');
  });

  $('#file-4').on('filebrowse', function() {
    //alert('File browse clicked for #file-4');

  });

  $('#file-4').on('fileloaded', function() {
    //alert('Fileerrre for #file-4');

  });

  $('#file-4').on('filepreupload', function() {

  });
  $('#file-4').on('fileuploaded', function(event, data, previewId, index) {
    //alert(data);
    //alert(data.files);
    alert('上传EXCEL成功');
    //$('#file-4').fileinput('lock');
    //$('#file-4').fileinput('clear');
    $("#excelsub").attr("disabled",false);
    $("#excelpath").val(data.response);
    //var form = data.form, files = data.files, extra = data.extra,
    //	response = data.response, reader = data.reader;
    //console.log('File uploaded triggered');
  });


  $("#excelsub").click(


          //alert($("#m_type option:selected").val());
          function(){
            var excelpath = $("#excelpath").val();
            $(".bom_input #hoteldetail .accountcl").html('');

            $.ajax({
              type:"POST",
              ContentType: "application/json; charset=utf-8",
              dataType: "json",
              url:"<?php echo ($host_name); ?>/checkaccount/analyseExcel",
              data:"excelpath="+excelpath,
              success:function(data){
                if(data.error==0){
                  var data = data.message;
                  $("#accountjson").val(JSON.stringify(data));
                  for(var i=0,l=data.length;i<l;i++) {
                    for (var key in data[i]) {
                      //alert(key);
                      if (key == 'id') {
                        var ids = data[i][key];
                      }else if(key == 'name') {
                        var name = data[i][key];
                      }else if(key == 'money') {
                        var money = data[i][key];
                      }
                    }

                    $(".bom_input #hoteldetail .accountcl ").append("<tr><td  id="+ids+">"+ids+"</td><td id="+ids+">"+name+"</td><td id="+ids+">"+money+"</td></tr>");

                    //$('#mymodal').modal('hide');
                  }

                }else{
                  alert(data.message);
                  return false;
                }
              }
            });
          }

  );


  function dialogAjaxDoneIce(json){
	
    DWZ.ajaxDone(json);
    var str2 = json.info;
    if(str2.indexOf("由于使用第三方平台，可能有延时") > 0 )
    {
      $.pdialog.closeCurrent();
    }
    navTab.reload('Checkaccount/rplist');


  }

</script>