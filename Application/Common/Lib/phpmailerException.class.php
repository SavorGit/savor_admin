<?php
namespace Common\Lib;
use Think\Exception;
class phpmailerException extends Exception {
    public function errorMessage() {
        $errorMsg = "<strong>aa</strong><br />\n";
        return $errorMsg;
    }
}