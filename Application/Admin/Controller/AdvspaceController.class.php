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
use Admin\Controller\BaseController;


class AdvspaceController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
        $this->lnum = 10;
    }

    /**
     * @desc 广告投放列表
     * @method getlist
     * @access public
     * @http post
     * @param numPerPage intger 显示每页记录数
     * @param pageNum intger 当前页数
     * @param _order $record 排序
     * @return void|array
     */
    public function getlist(){
        //获取所有机顶盒
        $now_date = date("Y-m-d");
        $boxModel = new \Admin\Model\BoxModel();
        $field = 'state,name';
        $where = '1=1 and flag=0';
        $box_arr = $boxModel->getInfo($field, $where);
        $all_box_len = count($box_arr);
        $normal_box_arr = array_filter($box_arr, function($result){
            if ($result['state'] != 1) {
                return false;
            }else {
                return true;
            }
        });
        $normal_box_len = count($normal_box_arr);
        //获取当前已经用机顶盒和总时长
        $pubadsboxModel = new \Admin\Model\PubAdsBoxModel();
        $map['end_date']  = array('egt', $now_date);
        $map['start_date']  = array('elt', $now_date);
        $field = 'sbox.box_id,sbox.pub_ads_id,ads.duration';
        $group = 'sbox.box_id,sbox.pub_ads_id';
        $ads_box_arr = $pubadsboxModel->getAllBoxPubAds($field,
            $map, $group);
        $tmp_use_box_arr = array();
        $tmp_use_time_arr = array();
        foreach ($ads_box_arr as $ak => $av) {
            $tmp_use_box_arr[$av['box_id']] = 1;
            $tmp_use_time_arr[$av['pub_ads_id']] = $av['duration'];
        }
        $tmp_use_box_len = count($tmp_use_box_arr);
        $tmp_use_box_space = $normal_box_len - $tmp_use_box_len;
        $tmp_use_time_total = array_sum($tmp_use_time_arr);
        $av_time = floor($tmp_use_time_total/$tmp_use_box_len/60);
        $this->assign('all_box_len', $all_box_len);
        $this->assign('normal_box_len', $normal_box_len);
        $this->assign('tmp_use_box_len', $tmp_use_box_len);
        $this->assign('tmp_use_box_space', $tmp_use_box_space);
        $this->assign('aver_time', $av_time);
        $this->display('advspace');
    }
}
