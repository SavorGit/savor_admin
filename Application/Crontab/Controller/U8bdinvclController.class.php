<?php
namespace Crontab\Controller;
use Think\Controller;

class U8bdinvclController extends Controller{
    //存货分类新增、修改
    public function saveGoodsBrand(){
        $u8 = new \Common\Lib\U8cloud();
        $m_series = new \Admin\Model\FinanceSeriesModel();
        
        $where = [];
        $where['a.status']     = 1;
        $where['brand.status'] = 1;
        
        $brand_list = $m_series->alias('a')
        ->join('savor_finance_brand brand on a.brand_id=brand.id','left')
        ->field('a.id series_id,a.name series_name,brand.id brand_id,brand.name brand_name,a.u8_pk_id','left')
        ->where($where)
        ->select();
        
        
        foreach($brand_list as $key=>$v){
            $data   = [];
            $params = [];
            
            if(empty($v['u8_pk_id'])){//新增
                
                if($v['series_id']<10){
                    $data['invclasscode'] = '0'.$v['series_id'];        //编码
                }else {
                    $data['invclasscode'] = $v['series_id'];
                }
                
                $data['invclassname'] = $v['brand_name'].'-'.$v['series_name'];   //存货分类名称
                $data['pk_corp']      = '0001';                                   //公司  0001:集团
                $params['invcl'][] = $data;
                
                $ret = $u8->addGoodsBrand($params);
                $result = json_decode($ret['result'],true);
                $status = $result['status'];
                if($status=='success'){//同步成功
                    $ret_data = json_decode($result['data'],true);
                    $map = [];
                    $map['id'] = $v['series_id'];
                    
                    $info = [];
                    $info['u8_pk_id'] = $ret_data[0]['pk_invcl'];
                    
                    $m_series->updateData($map, $info);
                }
                
            }else {//编辑
                
                if($v['series_id']<10){
                    $data['invclasscode'] = '0'.$v['series_id'];       //编码
                }else {
                    $data['invclasscode'] = $v['series_id'];
                }
                $data['pk_invcl'] = $v['u8_pk_id'];                    //存货分类主键
                $data['invclassname'] = $v['brand_name'].'-'.$v['series_name'];  //存货分类名称
                $params['invcl'][] = $data;
                $ret = $u8->editGoodsBrand($params);
            }
            
        }
        echo date('Y-m-d H:i:s').' OK';
    }
}