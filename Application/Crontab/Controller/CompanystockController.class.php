<?php
namespace Crontab\Controller;
use Think\Controller;

class CompanystockController extends Controller{

    public function stockbj(){
        $now_time = date('Y-m-d H:i:s');
        echo "stockbj start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceCompanyStockModel();
        $m_stock->handle_company_stock(1);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        $now_time = date('Y-m-d H:i:s');
        echo "stockbj end:$now_time \r\n";
    }

    public function stocksh(){
        $now_time = date('Y-m-d H:i:s');
        echo "stocksh start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceCompanyStockModel();
        $m_stock->handle_company_stock(9);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        $now_time = date('Y-m-d H:i:s');
        echo "stocksh end:$now_time \r\n";
    }

    public function stockgz(){
        $now_time = date('Y-m-d H:i:s');
        echo "stockgz start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceCompanyStockModel();
        $m_stock->handle_company_stock(236);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        $now_time = date('Y-m-d H:i:s');
        echo "stockgz end:$now_time \r\n";
    }

    public function stockfs(){
        $now_time = date('Y-m-d H:i:s');
        echo "stockfs start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceCompanyStockModel();
        $m_stock->handle_company_stock(248);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        $now_time = date('Y-m-d H:i:s');
        echo "stockfs end:$now_time \r\n";
    }

}