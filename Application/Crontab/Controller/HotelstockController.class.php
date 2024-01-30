<?php
namespace Crontab\Controller;
use Think\Controller;

class HotelstockController extends Controller{
    public function stathotelstock(){
        $now_time = date('Y-m-d H:i:s');
        echo "stathotelstock start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceStockModel();
        $m_stock->handle_hotel_stock();
        $now_time = date('Y-m-d H:i:s');
        echo "stathotelstock end:$now_time \r\n";
    }

    public function statgoodsstock(){
        $now_time = date('Y-m-d H:i:s');
        echo "handle_goods_stock start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceStockModel();
        $m_stock->handle_goods_stock();
        $now_time = date('Y-m-d H:i:s');
        echo "handle_goods_stock end:$now_time \r\n";
    }

}