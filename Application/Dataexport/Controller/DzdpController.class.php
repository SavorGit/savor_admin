<?php
//大中点评导入数据
namespace Dataexport\Controller;

class DzdpController extends BaseController{
    public function exportCircle(){
        set_time_limit(9000);
        ini_set("memory_limit", "8018M");
        
        $path = '/application_data/web/php/savor_admin/Public/uploads/2023-04-26/fs-dzdp.xlsx';
        //$area_id = 1;  //北京
        //$area_id = 9;  //上海
        //$area_id = 236;//广州
        //$area_id = 246;//深圳
        $area_id = 248;//佛山
        
        //$count_id = 35;
        //$count_id = 107;
        //$count_id= 236;
        //$count_id= 246;
        $count_id= 248;
        
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        vendor("PHPExcel.PHPExcel.IOFactory");
        $objPHPExcel = \PHPExcel_IOFactory::load($path);
        
        
        $sheet = $objPHPExcel->getSheet(0);
        //获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        
        
        //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();
        for ($i = 0; $i < $highestColumnNum; $i++) {
            $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
            $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
            $filed[] = $cellVal;
        }
        
        
        //开始取出数据并存入数组
        $datas = array();
        //$hotel_str = '';
        //$spx = '';
        $serial_number_new = [];
        for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
            $row = array();
            for ($j = 0; $j < $highestColumnNum; $j++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                $cellVal = (string)$sheet->getCell($cellName)->getValue();
                if($cellVal === 'null'){
                    $cellVal = '';
                }
                if($cellVal === '"' ||  $cellVal === "'"){
                    $cellVal = '#';
                }
                if($cellVal === 'null'){
                    $cellVal = '';
                }
                $row[$filed[$j]] = $cellVal;
            }
            $datas [] = $row;
        }
        assoc_unique_new($datas,'name');
        $m_area_info = new \Admin\Model\AreaModel();
        $bj_area_list = $m_area_info->where(array('parent_id'=>$count_id))->select();
        
        foreach($datas as $key=>$val){
            foreach($bj_area_list as $kk=>$vv){
                if($val['region_name']==$vv['region_name']){
                    $datas[$key]['area_id']   = $area_id;
                    $datas[$key]['county_id'] = $vv['id'];
                    $datas[$key]['status']    = 1;
                    
                    break;
                }
            }
            unset($datas[$key]['region_name']);
        }
        //print_r($datas);exit;
        $m_business_circle = new \Admin\Model\BusinessCircleModel();
        $m_business_circle->addAll($datas);
        echo date('Y-m-d H:i:s').' ok';
    }
    //更新几个数据表的酒楼名称
    public function updateHotelname(){
        exit('已执行该脚本');
        $sql ="select id,name from savor_hotel where
               id in (
                    468,28,590,972,848,550,1507,1564,1007,548,1510,1365,668,572,462,156,
                    49,476,1282,563,81,123,94,65,1253,79,1347,1223,1497,686,1766,1796,1723,
                    1307,1805,1604,1352,1652,742,1200,1562,191,177,189,32,827,1731,186,185,187,
                    524,188,1508,1338,163,1224,1654,842,678,666,95,34,1215,571,57,1248,1473,1247,
                    1568,10,1721,1710,1734,167,36,1804,151,147,1437,1362,1732,1686,1375,1658,543,
                    1021,210,113,1548,1764,1561,512,473,1795,26,1241,806,1348,1576,1243,1673,500,625,
                    56,41,896,24,1807,135,1470,180,140,1360,1306,1646,1359,92,838,837,840,835,1231,546,
                    1675,582,680,196,1267,17,1672,214,787,683,216,1491,84,215,1729,1341,786,1194,1722,
                    1447,1216,1709,1015,802,679,12,90,1563,1244,1733,1573,85,238,886,1278,205,1222,1012,
                    555,55,29,199,204,237,23,207,1006,91,825,243,1293,841,1001,431,464,11,88,1291,67,68,
                    1295,1642,1433,1310,1102,129,169,1472,168,175,176,878,39,888,1263,763,98,61,1239,217,
                    1314,105,138,1738,1300,1270,471,1346,1329,174,218,552,436,18,47,1296,833,222,31,212,38,
                    48,980,1345,1676,884,995,52,181,1242,1238,1273,1315,223,232,940,1185,753,1339,1344,1237,
                    1320,1353,1356,116,112,311,268,602,1268,770,1053,1292,921,1335,952,351,447,1274,294,950,
                    994,1283,603,296,783,1308,779,295,781,260,264,1294,766,251,671,776,533,897,252,258,332,
                    255,262,630,782,263,261,256,675,490,677,335,253,606,333,330,628,805,369,674,265,807,607,
                    313,923,328,327,275,621,623,620,624,622,1285,367,1302,1261,1214,1405,1299,489,1201,1613,
                    907,1288,1277,1231,1819,1416,354,312,1412,303,618,1392,871,631,895,376,894,1415,356,304,
                    305,372,445,384,349,293,320,913,1071,284,315,283,599,629,289,853,877,1191,1399,944,342,
                    608,302,1786,366,600,1376,1396,1279,1398,1394,1714,378,688,916,849,616,693,273,1397,
                    1269,613,610,817,1395,1406,383,1438,619,615,612,1393,1051,420,941,1258,459,1176,816,964,
                    847,943,421,423,424,874,912,1094,810,1087,1221,1022,1019,637,1039,1448,920,1124,1062,706,
                    1054,1029,1075,1226,1333,1257,1262,1045,1091,632,711,1233,1023,1026,958,959,975,1208,1125,
                    1109,1050,917,1106,718,716,714,910,713,719,717,1121,416,1177,1077,1225,1063,967,1209,1227,
                    1041,1048,1210,1107,1097,1037,1076,963,934,922,1351,1331,1229,1435,1323,1218,1078,1115,
                    529,1265,1047,1074,1119,636,1259,1099,430,392,639,863,428,968,872,955,404,412,388,1182,
                    1101,1070,898,1276,1046,1187,1056,927,405,393,1286,664,1059,1123,391,936,663,931,1105,
                    1058,1330,1197,1095,701,427,1065,721,1100,1096,1188,939,1088,415,1005,1068,1009,1110,
                    1085,1450,1111,1199,1113,1649,1599,1632,1586,1382,1523,1456,1570,1518,1592,1575,898,
                    1381,1608,1539,1492,1538,1477,1552,1478,1488,1531,1489,1535,1505,1466,1490,1631,1475,
                    1551,1534,1462,1529,1605,1479,1557,1560,1556,1537,1468,1520,1528,1458,1626,1577,1374,
                    1378,1384,1402,1566,1506,1423,1485,1515,1544,1541,1536,1371,1386,1524,1425,1500,1593,
                    1567,1572,1487,1619,1380,1460,1559,1630,1628,1629,1480,1694,1621,1614,1542,1627,1600,
                    1408,1606,1486,1525,1571,1367,1481,1493,1502,1588,1635,1591,1428,1579,1454,1565,1554,
                    1712,1637,1418,1501,1521,1474,1483,1596,1633,1582,1590,1385,1498,1578,1549,1622,1503,
                    1602,1512,1638,1581,1482,1532,1777,1514,1685,1372,1661,1504,1775,1597
               )";
        
        $hotel_list = M()->query($sql);
        $m_StaticHotelbasicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
        $m_ForscreenRecord      = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_StaticHotelstaffdata = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
        $m_StaticBoxdata        = new \Admin\Model\Smallapp\StaticBoxdataModel();
        $m_Statistics           = new \Admin\Model\Smallapp\StatisticsModel();
        
        //$tmp = $m_Statistics->where(array('id'=>1))->select();
        
        foreach($hotel_list as $key=>$v){
            //StaticHotelbasicdata hotel_id hotel_name
            //ForscreenRecord      hotel_id hotel_name
            //StaticHotelstaffdata hotel_id hotel_name
            //StaticBoxdata        hotel_id hotel_name
            //Statistics           hotel_id hotel_name
            
            $map = array();
            $map['hotel_id'] = $v['id'];
            $up_info = array();
            $up_info['hotel_name'] = $v['name'];
            
            $m_StaticHotelbasicdata->updateData($map, $up_info);
            $m_ForscreenRecord->updateData($map, $up_info);
            $m_StaticHotelstaffdata->updateData($map, $up_info);
            $m_StaticBoxdata->updateData($map, $up_info);
            $m_Statistics->updateData($map, $up_info);
        }
        echo date('Y-m-d H:i:s').' ok';
    }
    
}