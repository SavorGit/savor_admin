<?php
namespace Admin\Controller;

/**
 *@desc 专题组控制器,对专题组添加或者修改
 * @Package Name: SpecialgroupController
 *
 * @author      白玉涛
 * @version     3.0.1
 * @copyright www.baidu.com
 */
use Think\Controller;

class SpecialgroupShowController extends Controller {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->host_name =  C('HOST_NAME').'/admin';
        $this->oss_host = 'http://'.C('OSS_HOST_NEW').'/';
    }



    /*
     * @desc 显示h5页面
     * @method editSpecialGroup
     * @access public
     * @http NULL
     * @return void
     */
    public function showsp(){
        $sourcename = I('get.location','');
        $this->assign('sourc', $sourcename);
        $spgroupModel = new \Admin\Model\SpecialGroupModel();
        $id = I('get.id');
        $field = "sg.NAME,sg.title sptitle, sg.img_url spimg,sg.desc        spdesc,sr.sgtype,sr.stext,sr.sarticleid,sr.spictureid,
        sr.stitle,sm.oss_addr simg,smc.title mtitle,smc.img_url mimg,
        smc.id marticle,smc.content_url mcurl,smc.create_time ";
        $where =  " 1=1 and sg.id = $id";
        $speca_arr_info = $spgroupModel->fetchDataBySql($field, $where);
        $oss_host = $this->oss_host;
        $spinfo = array(
            'sgid'=>$id,
            'name'=>$speca_arr_info[0]['name'],
            'title'=>$speca_arr_info[0]['sptitle'],
            'oss_addr'=>$oss_host.$speca_arr_info[0]['spimg'],
            'desc'=>$speca_arr_info[0]['spdesc'],
        );
        if ($speca_arr_info) {
            foreach ($speca_arr_info as $spk=>$spv) {
                if($spv['sgtype'] == 3) {
                    $speca_arr_info[$spk]['simg'] = $oss_host.$spv['simg'];
                }else if($spv['sgtype'] == 2){
                    $speca_arr_info[$spk]['mimg'] = $oss_host.$spv['mimg'];
                    $speca_arr_info[$spk]['mcurl'] = $this->host_name.'/'.$spv['mcurl'];
                    $speca_arr_info[$spk]['create_time'] = date("Y-m-d", strtotime($spv['create_time']));
                }
            }

        } else {
            $speca_arr_info = array();
        }

        $this->assign('srinfo', $speca_arr_info);
        $this->assign('vinfo', $spinfo);
        $this->display('new_special');

    }


}
