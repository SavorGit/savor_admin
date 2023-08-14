<?php
namespace Admin\Model;
class FinanceCompanyStockModel extends BaseModel{

    protected $tableName='finance_company_stock';

    public function handle_company_stock($area_id,$goods_id=0){
        $now_hour = date('G');
        if($now_hour<7){
            echo "not in hour \r\n";
            exit;
        }
        $m_company_stock_detail = new \Admin\Model\FinanceCompanyStockDetailModel();
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $m_finance_goods = new \Admin\Model\FinanceGoodsModel();
        if($goods_id){
            $gwhere = array('id'=>$goods_id);
        }else{
            $gwhere = array('brand_id'=>array('neq',11));
        }
        $res_goods = $m_finance_goods->getDataList('id,name',$gwhere,'id desc');
        foreach($res_goods as $vg){
            $goods_id = $vg['id'];

            $sql = "select idcode,all_type from (
            SELECT a.idcode,goods.id as goods_id,goods.name as goods_name,GROUP_CONCAT(a.type) as all_type FROM
            savor_finance_stock_record a left JOIN savor_finance_stock stock on a.stock_id=stock.id left JOIN savor_finance_goods
            goods on a.goods_id=goods.id WHERE stock.area_id={$area_id} and a.goods_id={$goods_id} AND a.dstatus=1 group by a.idcode
            ) as gs where NOT FIND_IN_SET('4', gs.all_type)>0";
            $res_stock_idcodes = $this->query($sql);

            $bottle_codes = array();
            $box_codes = array();
            foreach ($res_stock_idcodes as $cv){
                $code = $cv['idcode'];
                $qrcontent = decrypt_data($code,false);
                $qr_id = intval($qrcontent);
                $res_qrcontent = $m_qrcode_content->getInfo(array('id'=>$qr_id));
                if($res_qrcontent['type']==1 && $res_qrcontent['parent_id']==0){
                    $res_scodes = $m_qrcode_content->getDataList('*',array('parent_id'=>$qr_id),'id desc');
                    $is_unpack = 0;
                    foreach ($res_scodes as $sv){
                        $sv_code = encrypt_data($sv['id']);
                        $sql_record = "select type,wo_status from savor_finance_stock_record where idcode='{$sv_code}' and dstatus=1 order by id desc limit 0,1";
                        $res_record = $this->query($sql_record);
                        if(!empty($res_record[0]['type'])){
                            $is_unpack = 1;
                            if($res_record[0]['type']==1 || $res_record[0]['type']==3){
                                $sql_check = "SELECT a.idcode FROM savor_finance_stock_record a left JOIN savor_finance_stock stock on a.stock_id=stock.id 
                                    WHERE a.idcode='{$sv_code}' and a.dstatus=1 and stock.area_id={$area_id} order by a.id desc limit 0,1";
                                $res_check = $this->query($sql_check);
                                if(!empty($res_check)){
                                    $bottle_codes[]=$sv_code;
                                }
                            }
                        }else{
                            $is_unpack = 0;
                            break;
                        }
                    }
                    if($is_unpack==0){
                        $sql_record = "select type,wo_status from savor_finance_stock_record where idcode='{$code}' and dstatus=1 order by id desc limit 0,1";
                        $res_record = $this->query($sql_record);
                        if($res_record[0]['type']==1){
                            $box_codes[]=$code;
                        }
                    }

                }else{
                    $all_types = explode(',',$cv['all_type']);
                    if(count($all_types)==1 && ($all_types[0]==1 || $all_types[0]==3)){
                        $bottle_codes[]=$code;
                    }else{
                        $sql_coderecord = "select type,wo_status from savor_finance_stock_record where idcode='{$code}' and dstatus=1 order by id desc limit 0,1";
                        $res_coderecord = $this->query($sql_coderecord);
                        if($res_coderecord[0]['type']==1 || $res_coderecord[0]['type']==3){
                            $bottle_codes[]=$code;
                        }
                    }
                }
            }

            $bottle_codes = array_unique($bottle_codes);
            $box_num = count($box_codes);
            $bottle_num = count($bottle_codes);
            $num = $box_num*6+$bottle_num;

            $res_company_stock = $this->getInfo(array('area_id'=>$area_id,'goods_id'=>$goods_id));
            if(!empty($res_company_stock)){
                $company_stock_id = $res_company_stock['id'];
                $this->updateData(array('id'=>$company_stock_id),array('num'=>$num,'update_time'=>date('Y-m-d H:i:s')));
            }else{
                $company_stock_id = $this->add(array('area_id'=>$area_id,'goods_id'=>$goods_id,'num'=>$num));
            }
            $detail_data = array();
            foreach ($box_codes as $v){
                $detail_data[]=array('company_stock_id'=>$company_stock_id,'area_id'=>$area_id,'goods_id'=>$goods_id,
                    'num'=>6,'idcode'=>$v,'type'=>1);
            }
            foreach ($bottle_codes as $v){
                $detail_data[]=array('company_stock_id'=>$company_stock_id,'area_id'=>$area_id,'goods_id'=>$goods_id,
                    'num'=>1,'idcode'=>$v,'type'=>2);
            }
            $m_company_stock_detail->delData(array('area_id'=>$area_id,'goods_id'=>$goods_id));
            if(!empty($detail_data)){
                $m_company_stock_detail->addAll($detail_data);
            }
        }
    }
}
