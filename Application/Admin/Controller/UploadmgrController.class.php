<?php
namespace Admin\Controller;
/**
 * @desc uploadmgr图片类
 *
 */
class UploadmgrController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function uploadmgrList() {
        $uploadmgr  = new \Admin\Model\UploadmgrModel();
        $size  = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = " where 1";
        $sname=I('s_name');
        $stime=I('s_time');
        $stype=I('s_type');
        $time = $uploadmgr->groupInfo('shw_savepath');
        $this->assign('time', $time);
        if($sname) {
            $where .= " and shw_title like '%$sname%'";
        }
        if($stime) {
            $where .= " and shw_savepath = $stime";
        }
        if($stype) {
            if($stype == 'other') {
                $where .= " and shw_minetype not like 'video%' and shw_minetype not like 'audio%' and shw_minetype not like 'image%'";
            }else if($stype == 'ext'){
                $sext = explode(',',I('s_ext'));
                foreach ($sext as $k => $v) {
                    $where .= " and shw_fileext = '$v'";
                }
            }else{
                $where .= " and shw_minetype like '$stype%'";
            }
        }    

        $result = $uploadmgr->getList($where, $start, $size);
        foreach ($result['list'] as $k => $v) {
            $result['list'][$k]['shw_size'] = filesize($this->imgup_path().$v['shw_savepath'].'/'. $v['shw_savename']);
        }

        $multiple = I('multiple');
        $this->assign('multiple', $multiple);
        $this->assign('uploadmgrlist', $result['list']);
        $this->assign('page',  $result['page']);
        $browseFile = I('browseFile');
        if($browseFile){
            $this->display('browse');
        }else{
            $this->display('index');
        }
        
    }
    
    //新增用户
    public function uploadmgrAdd(){
        //处理提交数据
        $uploadmgr = new \Admin\Model\UploadmgrModel();
        if(IS_POST) {
            $imgName = html_entity_decode($_FILES['fileup']['name']['0']);
            $m = I('post.m');

            //在接收到数据的时候没有正常的解析, 所以直接用图片的名称来判断      
            if($imgName) {
                //上传图片
                $upload = new \Think\Upload();
                $upload->exts = array('pdf','zip','rar','txt','doc','docx','ppt','xls','xlsx','csv','jpg','jpeg','gif','png','bmp','svg','swf','flv','fla','avi','wmv','wma','rm','mov','mpg','rmvb','3gp','mp4','mp3');
                $upload->maxSize = 2097152;
                $upload->rootPath  =     $this->imgup_path();
                $upload->savePath  =     '';
                $upload->saveName = time().mt_rand();
                $info   =   $upload->upload();
                $path = $this->imgup_path(); 
                if(!$info){
                    $errMsg = $upload->getError();
                    $this->output($errMsg, 'uploadmgr/uploadmgrAdd', 0,0);
                }else{                
                    foreach ($info as $k => $v) {
                        $size = $v['size'];
                        $myimg = $path.$v['savepath'].$v['savename'];
                        $sizeinfo = getimagesize($myimg);
                        $v['ext'] = strtolower($v['ext']);
                        if ($v['ext'] == 'png' || $v['ext'] == 'gif' || $v['ext'] == 'jpg' || $v['ext'] == 'jpeg'){
                            $imgResult = $this->getThumbSize($myimg, 160, 160, '160x160_');
                            $img = new \Think\Image();
                            if($v['ext'] == 'jpg' || $v['ext'] == 'jpeg'){
                                $img -> open($myimg) -> save($myimg,'jpg',75,true);
                            }       
                        }                 
                        $name = str_replace('.'.$v['ext'], '', $v['name']);
                        $data[$k]['shw_title'] = $name;
                        $data[$k]['shw_savepath'] = $v['savepath'];
                        $data[$k]['shw_savename'] = $v['savename'];
                        $data[$k]['shw_minetype'] = $v['type'];
                        $data[$k]['shw_fileext'] = $v['ext'];
                        if($sizeinfo[0]){
                            $data[$k]['shw_width'] = $sizeinfo[0];
                            $data[$k]['shw_height']= $sizeinfo[1];
                        }
                        $userInfo = session('sysUserInfo');
                        $data[$k]['log_user'] = $userInfo['username'];
                        $data[$k]['log_time']  = date("Y-m-d H:i:s");
                        $result = $uploadmgr->addData($data[$k], 0);
                        if($m == 1){
                            echo $v['savepath'].$v['savename'];
                        }else{
                            if($result){
                                $json = array('status' => 1, 'name' => $name, 'savename' => $v['savename'],'ext'=>$v['ext']);
                                $this->ajaxReturn($json,'TEXT');
                            }else{
                                $json = array('status' => 2, 'name' => $name, 'savename' => $v['savename'],'ext'=>$v['ext']);
                                $this->ajaxReturn($json,'TEXT');
                            }
                            
                        }
                    }
                }
            }
        }
    }
    
    public function uploadmgrInfo($id){
        //处理提交数据
        $uploadmgr = new \Admin\Model\UploadmgrModel();
        $result = $uploadmgr -> getInfo($id);
        $result['shw_size'] = filesize($this->imgup_path().$result['shw_savepath'].'/'. $result['shw_savename']);
        $this -> assign('file', $result);
        $this->display('Uploadmgr/info');
    }
    
    public function delFile($id){
        $delete    = new \Admin\Model\UploadmgrModel();
        if($id) {        
            $file = $delete -> getInfo($id);
            $uploadpath = $this -> imgup_path();
            $files = scandir($uploadpath.$file['shw_savepath']);
            $delfile = $uploadpath.$file['shw_savepath'].'/'.$file['shw_savename'];
            foreach ($files as $k => $v) {
                $filename = explode('_', $v);
                if($filename[1] == $file['shw_savename']){
                    $delthumb = $uploadpath.$file['shw_savepath'].'/'.$v;
                    unlink($delthumb);
                }
            }        
            $result = $delete -> delData($id);
            unlink($delfile);
        }
        return $result;
    }
    //删除 记录
    public function uploadmgrDel() {
        $id = I('get.id', 0, 'int');
        $gid = I('delid');
        if($id) {
            $result = $this -> delFile($id);
            if($result) {
                $this->output('删除成功', 'Uploadmgr/uploadmgrList',2);
            } else {
                $this->output('删除失败', 'Uploadmgr/uploadmgrList',2);
            }
        } elseif ($gid[0]) {
            foreach ($gid as $k => $v) {
                $result2[$k] = $this -> delFile($v);
            }
            if($result2) {             
                $this->output('删除成功', 'Uploadmgr/uploadmgrList',2);
            } else {
                $this->output('删除失败', 'Uploadmgr/uploadmgrList',2);
            }
        }else{
            $this->error('删除失败,缺少参数!');
        }
    }
    
    //获取当前栏目下的静态页面
    private function getMyhtmlPage($tableName){
        if(!$tableName) return array();
        $tableNames = ucfirst(str_replace("savor_", '', $tableName));
        $htmlPage = scandir(APP_PATH."Site/View/$tableNames/");
        if(is_array($htmlPage) && $htmlPage){
            $pages = array();
            $num = 0;
            foreach ($htmlPage as $k => $v){
                if($v != '.' && $v != '..') {
                    $pages[$num]['id'] = $num++;
                    $pages[$num]['title'] = $v;
                    $pages[$num]['vtitle']= str_replace(".html", "", $v);
                }
            }
        }
        return $pages;
    }
    
    //ajax获取对应栏目下的页面名称
    public function getAjaxHtml(){
        $tableName = $_REQUEST['tname'];
        if(!$tableName) echo json_encode(array());
        $arrHtml = $this->getMyhtmlPage($tableName);
        $start = '<label>当前页面：</label><select class="" name="shwpage">';
        $end   = '</select>';
        $body = '';
        $result = '';
        if(arrHtml) {
            foreach ($arrHtml as $k => $v){
                $body .= '<option value="'.$v['vtitle'].'">'.$v['title'].'</option>';
            }
            echo $start.$body.$end;
        }else{
            echo $start.'<option value="">无页面</option>'.$end;
        }
    }
}