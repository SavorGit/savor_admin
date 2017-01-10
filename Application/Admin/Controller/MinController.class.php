<?php
namespace Admin\Controller;
use Think\Controller;

class MinController extends Controller{

    public function index(){
        Vendor('Minify/index');
    }    
}
