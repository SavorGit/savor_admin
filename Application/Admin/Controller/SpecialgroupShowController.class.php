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
use Common\Lib\Weixin_api;

class SpecialgroupShowController extends Controller {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->host_name_admin =  get_host_name().'/admin';
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
                    $speca_arr_info[$spk]['mcurl'] = $this->host_name_admin.'/'.$spv['mcurl'];
                    $speca_arr_info[$spk]['create_time'] = date("Y-m-d", strtotime($spv['create_time']));
                }
            }

        } else {
            $speca_arr_info = array();
        }

        $wpi = new Weixin_api();
        $share_url ='http://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $shareimg = 'http://'.$_SERVER['HTTP_HOST'].'/Public/admin/assets/img/logo_100_100.jpg';
        $share_title = $speca_arr_info[0]['sptitle'];
        if(empty($speca_arr_info[0]['spdesc'])){
            $share_desc = '小热点，陪伴你创造财富，享受生活。';
        }else{
            $cot = html_entity_decode($speca_arr_info[0]['spdesc']);
            $cot = strip_tags($cot);
            $share_desc = mb_substr($cot,0,50);
        }
        

        $share_config = $wpi->showShareConfig($share_url, $share_title,$share_desc,$share_url,$share_url);
        extract($share_config);
        $appid = $share_config['appid'];
        $noncestr = $share_config['noncestr'];
        $signature = $share_config['signature'];
        $this->assign('noncestr', $noncestr);
        $this->assign('signature', $signature);
        $this->assign('appid', $appid);
        $this->assign('share_title', $share_title);
        $this->assign('share_desc', $share_desc);
        $this->assign('shareimg', $shareimg);
        $this->assign('share_link', $share_url);



        $this->assign('srinfo', $speca_arr_info);
        $this->assign('vinfo', $spinfo);
        $this->display('new_special');

    }


}
