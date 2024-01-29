<?php
namespace Admin\Model;
class FinanceContractHotelModel extends BaseModel{

    protected $tableName='finance_contract_hotel';

    public function getContract($fields,$where,$order=''){
        $result = $this->alias('a')
            ->join('savor_finance_contract contract on a.contract_id=contract.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->select();
        return $result;

    }
}
