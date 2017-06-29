<?php
$y="180";
$x = array();
for($j=0;$j<200;$j++){
    $x[]= "$j";
}
for($i=0;$i<300;$i++){
        if(in_array($y,$x)){
            continue;
        }
}
?>