<?php
namespace Admin\Controller;
use Think\Controller;
class EmptyController extends Controller {
    
    public function _empty(){
        $uri = $_SERVER['REQUEST_URI'];
        $url = rtrim($uri,'/');
        header('Location: '.$url);
        exit;
    }
}