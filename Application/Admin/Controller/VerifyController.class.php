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
                'useCurve'  =>  false,
                'useNoise'  =>  false,
                'length' => 4, // 验证码位数
                'fontttf' => '5.ttf',
                'imageH'    =>  0,               // 验证码图片高度
                'imageW'    =>  0,               // 验证码图片宽度
                'bg'        =>  array(243, 251, 254),  // 背景颜色
                 'imageH' => 30
               ];
         $Verify = new Verify($config);
             $Verify->entry();
     }
}