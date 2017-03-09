<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify;
/**
 * @desc 客户端页面
 *
 */
class VerifyController extends Controller {
    
    /* 生成验证码 */
    public function verify()
    {
            $config = [
               'fontSize' => 16, // 验证码字体大小
                'length' => 4, // 验证码位数
                 'imageH' => 30
               ];
         $Verify = new Verify($config);
             $Verify->entry();
     }
}