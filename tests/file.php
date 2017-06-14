<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/28
 * Time: 13:08
 */
//include __DIR__."/../vendor/autoload.php";
//$fs = new \Symfony\Component\Filesystem\Filesystem();
//
//echo $fs->makePathRelative(
//    "/Users/yuyi/Web/xiaoan/api",
//"/Users/yuyi/Web/xiaoan/api"
//   // "/Users/yuyi/Web/xiaoan/api/artisan"
//);

function helperStrFormat($c){
    $c = str_replace(["\n","\r"]," ",$c);
    $c = preg_replace("/[\s]+/"," ",$c);
    return $c;
}

function matchNamespace( $content ){
    preg_match("/namespace[\s\S].+?;/",$content,$match);
    if( count($match) > 0 ) {
        $namespace = $match[0];
        $namespace = helperStrFormat($namespace);
        $namespace = rtrim($namespace,";");
        $temp = explode(" ",$namespace);
        if( isset($temp[1]) )
            return $temp[1];
    }
    return "";
}

 function matchAllFunctions( $content ){
    preg_match_all("/function[\s]{1,}?[\s\S].+?[1-9a-zA-Z]{1,}?\(([\s\S].+?)?\)/",
        $content,
        $funcs
    );


    $functions = [];
    foreach ( $funcs[0] as $func){

        $raw_func = $func;

        $func = helperStrFormat( $func );
        $func = str_replace("("," (",$func);
        $func = preg_replace("/[\s]+/"," ", $func );
        $func = trim( $func );


        $function_items = explode(" ",$func);
        $function_name  = "";
        foreach ( $function_items as $key => $item ){
            if( trim($item) == "function")
            {
                $function_name = $function_items[ $key+1 ];
                $functions[$function_name] = [];
                break;
            }
        }

        $functions[$function_name]["access"] = "public";
        $functions[$function_name]["static"] = "";

        foreach ( $function_items as $key => $item ) {
            if( in_array( $item,["public","private","protected"]))
                $functions[$function_name]["access"] = $item;
            if( $item == "static" )
                $functions[$function_name]["static"] = $item;
        }

        list( $prev_func_content, ) = explode( $raw_func, $content);
        $last_pos  = strrpos($prev_func_content,"*/");
        $func_pos  = strrpos($content,$raw_func);

        $tstr      = trim(substr($content,$last_pos,$func_pos-$last_pos));
        $tstr      = str_replace(["\r","\n"," "],"",$tstr);

        //得到函数的注释
        if( strlen($tstr) < 10 ) {
            preg_match_all("/\/\*[\s\S]{1,}?\*\//", $prev_func_content, $func_doc_match);
            $functions[$function_name]["doc"] = array_pop( $func_doc_match[0] );
        }else{
            $functions[$function_name]["doc"] = "";
        }

        $functions[$function_name]["params"] = [];

        preg_match_all( "/\([\s\S]{1,}\)/", $func, $raw_params );

        $params = $raw_params[0];
        if( count($params) > 0 ){

            $params = trim( $params[0] );
            $params = trim( $params,")");
            $params = trim( $params,"(");
            $params = str_replace(" ","", $params );
            $functions[$function_name]["params"] = explode(",",$params);
        }

    }
    return $functions;
}

function parse(){

    $content = file_get_contents(__DIR__."/class.php");

    //匹配命名空间
    $__nam = matchNamespace( $content );

    //匹配所有的class类
    preg_match_all("/(class|interface)[\s,a-zA-Z0-9_\\\\]{1,}\{/",$content,$match);

    //var_dump($match);
    if( !$match[0] ){
        //如果没有匹配到类，函数类的php文件
        $functions = matchAllFunctions( $content );
        return [""=>["functions"=>$functions]];
    }

    $res = [];
    $file_info = pathinfo(__DIR__."/class.php");


    //解析class类获取类的注释、函数、函数参数以及注释
    foreach ( $match[0] as $class ){

        $format_class = helperStrFormat( $class );

        preg_match("/(class|interface)[\s][a-zA-Z_0-9]{1,}/",$format_class,$raw_class);
        if( count($raw_class) <= 0 )
            continue;

        list(,$class_name) = explode(" ",$raw_class[0]);
        $res[$class_name]  = $file_info;


        $res[$class_name]["namespace"] = $__nam;

        list( $prev_class_content, )  = explode( $class, $content, 2 );
        $last_pos  = strrpos($prev_class_content,"*/");
        $class_pos = strrpos($content, $class);


        $split_content = trim(substr( $content, $last_pos, $class_pos-$last_pos ));
        $split_content = str_replace(["\r","\n"," "],"",$split_content);

        //获取类的注释
        if( strlen( $split_content ) < 10 ) {
            preg_match_all("/\/\*[\s\S]{1,}?\*\//", $prev_class_content, $doc);
            //这里只获取最后一个 /* */ 包含的注释作为类的注释
            $res[$class_name]["doc"] = array_pop($doc[0]);
        }else{
            $res[$class_name]["doc"] = "";
        }

        //这里获取完整的类的代码 执行完之后 $class 就是一个完成的php类的代码了
        $count = 1;
        $pos   = strpos($content, $class)+strlen( $class )+1;
        while(true){

            if (!isset($content[$pos])) {
                break;
            }

            //echo "===>",$content[$pos],"\r\n";
            //过滤掉注释
            if ($content[$pos] == "/" && $content[$pos+1] == "/" ) {
                $pos+=2;
                while ($content[$pos] != "\n") {
                    $pos++;
                    continue;
                }
            }
            //过滤掉注释
            if ($content[$pos] == "/" && $content[$pos+1] == "*") {
                $pos+=2;
                while (!($content[$pos] == "*" && $content[$pos+1] == "/")) {
                    $pos++;
                    continue;
                }
                $pos+=2;
            }

            $class.=$content[$pos];

            if ($content[$pos] == "{") {
                $count++;
            }

            else if( $content[$pos] == "}"){
                $count--;
            }

            if( $count <= 0 )
                break;

            $pos++;
        }


        var_dump($class);
        exit;
        //正则匹配获取类里面的所有的函数 一般的函数组成
        //public|private|protected static function 函数名称
        //preg_match_all("/((^[a-zA-Z0-9_]{1,}?)?[\sa-zA-Z0-9_\r\n]{1,}?)?function[\s\S]{1,}?\)/",
        //preg_match_all("/((^[a-zA-Z0-9_]{1,}?)?[\sa-zA-Z0-9_\r\n]{1,}?)?function[\s\S].+?\([\s\S]{1,}?\)/",
        //preg_match_all("/((^[a-zA-Z0-9_]{1,}?)?[\sa-zA-Z0-9_\r\n]{1,}?)?function[\s\S].+?\)/",
        preg_match_all("/((^[a-zA-Z0-9_]{1,}?)?[\sa-zA-Z0-9_\r\n]{1,}?)?function[\s\S].+?([1-9a-zA-Z]{1,})?\(([\s\S].+?)?\)/",
            //preg_match_all("/((^[a-zA-Z0-9_]{1,}?)?[\sa-zA-Z0-9_\r\n]{1,}?)?function[\s\S]{1,}?\)/",
            $class,
            $funcs
        );


        $functions = [];
        foreach ( $funcs[0] as $func){

            $raw_func = $func;

            $func = helperStrFormat( $func );
            $func = str_replace("("," (",$func);
            $func = preg_replace("/[\s]+/"," ", $func );
            $func = trim( $func );


            $function_items = explode(" ",$func);
            $function_name  = "";
            foreach ( $function_items as $key => $item ){
                if( trim($item) == "function")
                {
                    $function_name = $function_items[ $key+1 ];
                    $functions[$function_name] = [];
                    break;
                }
            }

            $functions[$function_name]["access"] = "public";
            $functions[$function_name]["static"] = "";

            foreach ( $function_items as $key => $item ) {
                if( in_array( $item,["public","private","protected"]))
                    $functions[$function_name]["access"] = $item;
                if( $item == "static" )
                    $functions[$function_name]["static"] = $item;
            }

            list( $prev_func_content, ) = explode( $raw_func, $class);
            $last_pos  = strrpos($prev_func_content,"*/");
            $func_pos  = strrpos($class,$raw_func);

            $tstr = trim(substr($class,$last_pos,$func_pos-$last_pos));
            $tstr = str_replace(["\r","\n"," "],"",$tstr);

            //得到函数的注释
            if( strlen($tstr) < 10 ) {
                preg_match_all("/\/\*[\s\S]{1,}?\*\//", $prev_func_content, $func_doc_match);
                $functions[$function_name]["doc"] = array_pop( $func_doc_match[0] );
            }else{
                $functions[$function_name]["doc"] = "";
            }

            $functions[$function_name]["params"] = [];

            preg_match_all( "/\([\s\S]{1,}\)/", $func, $raw_params );

            $params = $raw_params[0];
            if( count($params) > 0 ){

                $params = trim( $params[0] );
                $params = trim( $params,")");
                $params = trim( $params,"(");
                $params = str_replace(" ","", $params );
                $functions[$function_name]["params"] = explode(",",$params);
            }

        }

        $res[$class_name]["functions"] = $functions;
    }

    return $res;

}

echo "{//\r\n";
echo parse();