<?php
namespace Admin\Model;
use Common\Lib\Page;

class BoxcostModel extends BaseModel{

    protected $tableName='finance_boxcost';

    public function setBoxcost($hotel_id,$boxinfo){
        if($boxinfo['cost_status']==1){
            $m_contract_hotel = new \Admin\Model\ContracthotelModel();
            $data = array('hotel_id'=>$hotel_id);
            $res_hotel = $m_contract_hotel->getInfo($data);
            if(!empty($res_hotel)){
                $contract_id = $res_hotel['contract_id'];
                $m_contract  = new \Admin\Model\ContractModel();
                $res_contract = $m_contract->getRow('self_type,pay_templateids',array('id'=>$contract_id));
                $m_costtemplate  = new \Admin\Model\CosttemplateModel();
                $result_template = $m_costtemplate->getDataList('id',array('type'=>1,'is_standard'=>1),'id asc');
                $pay_template_standard_id = 0;
                if(!empty($result_template)){
                    $pay_template_standard_id=$result_template[0]['id'];
                }
                $pay_template_fid = -1;
                if(!empty($res_contract['pay_templateids'])){
                    $pay_templateids = explode(',',trim($res_contract['pay_templateids'],','));
                    $pay_template_fid = $pay_templateids[0];
                }
                if($res_contract['self_type']==1 && $pay_template_standard_id && $pay_template_fid==$pay_template_standard_id){
                    $add_data = array('hotel_id'=>$hotel_id,'room_id'=>$boxinfo['room_id'],'box_id'=>$boxinfo['box_id'],
                        'box_mac'=>$boxinfo['box_mac'],'template_id'=>$pay_template_fid);
                    $res_cost = $this->getInfo(array('hotel_id'=>$hotel_id,'box_id'=>$boxinfo['box_id']));
                    if(!empty($res_cost)){
                        $this->updateData(array('id'=>$res_cost['id']),$add_data);
                    }else{
                        $this->add($add_data);
                    }
                }
            }
        }else{
            $res_cost = $this->getInfo(array('hotel_id'=>$hotel_id,'box_id'=>$boxinfo['box_id']));
            if(!empty($res_cost)){
                $this->delData(array('id'=>$res_cost['id']));
            }
        }
        return true;
    }

    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->join('savor_finance_cost_template template on a.template_id=template.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_finance_cost_template template on a.template_id=template.id','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_finance_cost_template template on a.template_id=template.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->select();
        }
        return $data;
    }
}
