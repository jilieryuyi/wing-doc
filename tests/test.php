<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/23
 * Time: 23:08
 */

$class = file_get_contents('/Users/yuyi/Web/xiaoan/api/app/Http/Controllers/Reward/RewardController.php');
preg_match_all("/((^[a-zA-Z0-9_]{1,}?)?[\sa-zA-Z0-9_\r\n]{1,}?)?function[\s\S].+?\([\s\S]{1,}?\)/",
    $class,
    $funcs
);

var_dump($funcs);
exit;
$p = '/Users/yuyi/Web/xiaoan/api/app/Http/Middleware/Authenticate.php=';
$c = "Middleware/*";

$c = str_replace("/","\/",$c);
$c = str_replace("*","(.*)?",$c);

echo $c;
$check = preg_match("/$c/",$p);
echo "\r\n\r\n";
var_dump($check);
exit;

$str = 'int $a';
var_dump(explode('$',$str));
exit;
$str = 'param int|string
      $coupon_id 宝券id或者uuid';
$str = str_replace(["\r","\n"]," ",$str);
$str = preg_replace("/[\s]+/"," ",$str);
echo $str,"\r\n";
$match = preg_split("/[\s]/",$str,4);
//preg_match_all("/[\s][\s\S]{1,}[\s].+?/",$str,$match);

var_dump($match);
exit;
$value = '@property int $id` int(11) NOT NULL AUTO_INCREMENT,';
preg_match("/@[a-zA-Z0-9]{1,}[\s\S]{1,}?/",$value,$match);
var_dump($match);
exit;
$doc = "bool isSelf(int \$user_id) 判断是否为指定用户所有 如果是返回true";
preg_match("/[\s\S]{1,}\)/",$doc,$match);
$func_str = $match[0];

preg_match("/[\s].+?[a-zA-Z0-9_]{1,}\(/",$func_str,$match);

$function_name = trim($match[0]);
$function_name = trim($function_name,"(");
//echo $function_name,"\r\n";

preg_match("/\([\s\S]{1,}\)/",$func_str,$match);
$params_str = trim($match[0],"(");
$params_str = trim($params_str,")");
$params_str = trim($params_str);
$params = explode(",",$params_str);


preg_match("/\)[\s\S]{1,}/",$doc,$match);
$doc = trim($match[0],")");
$doc = trim($doc);
$doc = "/**\r\n *".$doc."\r\n */";
echo $doc;

exit;

include "WFile.php";
$file = '/Users/yuyi/Web/activity/app/Logic/Lib2/Coupon.php';
$file = new \Wing\Doc\WFile($file);
$res = $file->parse();
var_dump($res);

//$value = "@author yuyi";
//preg_match("/@[a-zA-Z0-9\-\_]{1,}([\s]{1,})?[\s\S]{1,}/",$value,$match);
//var_dump($match);
//exit;
$content = ' 
/**
 * class name a
 * @author yuyi000
 */
 
/**
 * class name a
 * @author yuyi
 */
 
 
final   
class  
a imp 
cc,dd
{
/**
 * 求和
 * @param int $a
 * @param int $b
 */
    public function b 
    ($a,$b,
    $c){}
    public function b2($a,$b,
    $c){}
}


class b{
    public function c(){}
}';

//$old_str = $str;

//$str = str_replace("\r"," ",$content);
//$str = str_replace("\n"," ",$str);
//$str = preg_replace("/[\s]+/"," ", $str );
$str = $content;

preg_match_all("/class[\s,a-zA-Z0-9_]{1,}\{/",$str,$match);
$functions = [];
foreach ( $match[0] as $c ){

    //echo $c,"\r\n";
    $d = explode($c,$content,2);
    $b = $d[0];

    $l  = strrpos($b,"*/");
    $cd = strrpos($content,$c);

    //echo $l,"==>",$cd,"\r\n";

    //这里截取出来的 $tstr 刚好是*/
    $tstr = trim(substr($str,$l,$cd-$l));
    $tstr = str_replace("\r","",$tstr);
    $tstr = str_replace("\n","",$tstr);
    $tstr = str_replace(" ","",$tstr);
//echo $tstr,"<===\r\n";
    if( strlen($tstr) < 10 ) {
        preg_match_all("/\/\*[\s\S]{1,}?\*\//", $b, $doc);
        $class_doc = array_pop($doc[0]);
        var_dump($class_doc);
    }else {
        echo "没有注释\r\n";
    }


    $c1 = str_replace("\r"," ",$c);
    $c1 = str_replace("\n"," ",$c1);
    $c1 = preg_replace("/[\s]+/"," ", $c1 );

    preg_match("/class[\s][a-zA-Z_0-9]{1,}/",$c1,$o);

    $temp =explode(" ",$o[0]);
    $class_name = $temp[1];
    echo $class_name,"\r\n";

    $count = 1;
    $pos = strpos($str,$c)+strlen($c)+1;
    while(true){
        $c.=$str[$pos];

        if( $str[$pos] == "{" )
        {
            $count++;
        }

        else if( $str[$pos] == "}"){
            $count--;
        }

        if( $count <= 0 )
            break;

        $pos++;
    }

    //echo $c,"\r\n\r\n";
//    $c = str_replace("\r"," ",$c);
//    $c = str_replace("\n"," ",$c);
//    $c = preg_replace("/[\s]+/"," ", $c );

    preg_match_all("/function[\s\S]{1,}?\)/",$c,$funcs);
    //echo "class==>",$class_name,"\r\n";
    //var_dump($funcs);
    foreach ( $funcs[0] as $func){

       // echo $func,"\r\n\r\n";
        $fd = explode( $func, $str);
        $fd = $fd[0];
        $l  = strrpos($fd,"*/");
        $cd = strrpos($str,$func);

        //echo $l,"==>",$cd,"\r\n";

        //这里截取出来的 $tstr 刚好是*/
        $tstr = trim(substr($str,$l,$cd-$l));
        $tstr = str_replace("\r","",$tstr);
        $tstr = str_replace("\n","",$tstr);
        $tstr = str_replace(" ","",$tstr);


       // echo $tstr,"<===\r\n";
        if( strlen($tstr) < 10 ) {
            preg_match_all("/\/\*[\s\S]{1,}?\*\//", $fd, $fdoc);
            $func_doc = array_pop($fdoc[0]);
            //var_dump($func_doc);
        }else{
            //echo "没有注释\r\n";
        }
        //var_dump($func_doc);

        $func = str_replace("\r"," ",$func);
        $func = str_replace("\n"," ",$func);
        $func = preg_replace("/[\s]+/"," ", $func );

        $func = str_replace(" (","(",$func);

        $t     = explode(" ",$func,2);
        $t2    = explode("(",$t[1]);
        $t2[1] = rtrim($t2[1],")");
        $argvs = explode(",",$t2[1]);

        $functions[$class_name][] = [
            "func"   => $t2[0],
            "params" => $argvs
        ];
    }


}

//var_dump($functions);