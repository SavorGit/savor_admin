<?php
namespace Tvcode\Controller;
use Think\Controller;

class IndexController extends Controller {

    public function index(){
        $scene = I('get.scene');
        echo $scene;
    }

}