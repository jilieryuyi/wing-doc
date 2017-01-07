<?php namespace Wing\Doc;
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/24
 * Time: 18:20
 */
class WFunction{
    private $doc;
    private $raw_data;
    private $function_name;
    private $class_name = "";
    public function __construct(
        $function_name,
        array $raw_data,
        $class_name = ""
    )
    {
        $this->raw_data      = $raw_data;
        $this->function_name = $function_name;
        $this->class_name    = $class_name;
    }
    public function getStatic(){
        return $this->raw_data["static"];
    }
    public function getAccess(){
        if( !$this->class_name )
            return "";
        return $this->raw_data["access"];
    }
    public function isPublic(){
        return $this->raw_data["access"] == "public";
    }
    public function isPrivate(){
        return $this->raw_data["access"] == "private";
    }
    public function isProtected(){
        return $this->raw_data["access"] == "protected";
    }
    public function isStatic(){
        return $this->raw_data["static"] == "static";
    }
    public function getRawDoc(){
        return $this->raw_data["doc"];
    }
    public function getParams(){
        $params     = $this->raw_data["params"];
        $doc_params = $this->getDocParam();
        $res = [];
        foreach ( $params as $param ){
            $param = trim($param,"&");
            $temp = explode("$",$param);
            $type = trim($temp[0]);
            if( !isset($temp[1]) )
                $temp[1] = "";
            $var_value = $temp[1];
            $temp = explode("=",$var_value);
            $var  = trim($temp[0]);
            $default = isset($temp[1])?$temp[1]:"";
            $default = trim($default);



            $doc  = isset($doc_params[$var]["doc"])?$doc_params[$var]["doc"]:"";
            if( !$type ){
                $type = isset($doc_params[$var]["type"])?$doc_params[$var]["type"]:"";
            }

            if( $default =='""' )
            {
                $default ="空";
                if( !$type )
                    $type = "string";
            }

            if( $default == "true" || $default == "false" ){
                if( !$type )
                    $type = "bool";
            }

            if( $default == "[]" )
            {
                if( !$type )
                    $type = "array";
            }

            $res[$var] = [
                "doc"     => $doc,
                "type"    => $type,
                "default" => $default
            ];
        }
        return $res;
    }
    public function getRequest(){
        $doc      = $this->getDoc();
        $requests = $doc->request;

        $res = [];
        if(is_array($requests)) {
            foreach ($requests as $request) {
                $res[] = $this->requestFormat($request);
            }
        }
        return $res;
    }

    public function getResponseFormat(){
        $response = $this->getDoc()->response;
        $response = trim($response);
        $res = preg_split("/[\s]+/",$response,2);

        if( count($res) > 0 )
        {
            $format = strtolower(trim($res[0]));
            return $format;
        }

        return "string";


    }

    protected function requestFormat($str){
        $str = trim($str);

        preg_match("/\([\S\s]{1,}\)/",$str,$match);
        if( count($match) > 0 ){
            $match = preg_replace("/[\s]+/","",$match[0]);
            $str   = preg_replace("/\([\S\s]{1,}\)/",$match, $str);
        }

        $match = preg_split("/[\s]+/",$str,3);
        $type  = "string";
        $key   = "";
        $doc   = "";
        $min   = 0;
        $max   = 0;

        if( count( $match ) == 3 ){
            $type = $match[0];
            $key  = $match[1];
            $doc  = $match[2];

            preg_match_all("/[\d]+/",$type,$range);
            list($type,) = explode("(",$type);
            if( count($range[0]) == 1 )
                $min = $max = $range[0][0];
            else if( count( $range[0] ) == 2 ){
                $min = $range[0][0];
                $max = $range[0][1];
            }
        }else{
            $key = $match[0];
            $doc = $match[1];
        }

        $template = "";
        if( $type == "json" ){
            //$doc =' {"id":0,"name":"123"} srdfsdf';
            preg_match("/(\[|\{)[\s\S]{1,}(\}|\])+/",$doc,$jmatch);

            if( isset($jmatch[0]) )
                $template = $jmatch[0];
            $doc = preg_replace("/(\[|\{)[\s\S]{1,}(\}|\])+/","",$doc);
            $doc = trim($doc);
        }
        else if( $type == "datetime" ){

            preg_match("/\\$\{[\s\S]{1,}\}/",$doc,$dmatch);

            if( isset($dmatch[0]) )
            {
                $format   = $dmatch[0];
                $format   = ltrim($format,"$");
                $format   = ltrim($format,"{");
                $format   = rtrim($format,"}");
                $template = trim($format);
                $doc      = preg_replace("/\\$\{[\s\S]{1,}\}/","",$doc);
                $doc      = trim($doc);
            }
            else
            {
                $template = "int";
            }

        }

        return [
            "type"     => $type,
            "key"      => $key,
            "doc"      => $doc,
            "min"      => $min,
            "max"      => $max,
            "template" => $template
        ];
    }


    public function getFunctionName(){
        return $this->function_name;
    }

    /**
     * @return WDoc
     */
    public function getDoc(){
        if( !$this->doc )
            $this->doc = new WDoc($this->raw_data["doc"]);
        return $this->doc;
    }
    public function getDocReturn(){
        $return = $this->getDoc()->return;

        if( is_array($return) )
            return $return[0];

        if( $return )
            return $return;

        if( $this->function_name == "__construct" ||
            $this->function_name == "__destruct"){
            return "void";
        }

        if( stripos($this->function_name,"is") === 0 ||
            stripos($this->function_name,"check") === 0 ||
            stripos($this->function_name,"bool") !== false ||
            stripos($this->function_name,"exists") !== false

        )
            return "bool";

        if( stripos($this->function_name,"str") !== false )
            return "string";

        if( stripos($this->function_name,"int") !== false )
            return "int";

        if( stripos($this->function_name,"arr") !== false )
            return "array";

        return "mixed";
    }
    public function getDocParam(){
        $params = $this->getDoc()->param;
        if( !is_array( $params ))
            return [];
        $res = [];
        foreach ( $params as $str ) {
            $str   = str_replace(["\r", "\n"], " ", $str);
            $str   = preg_replace("/[\s]+/", " ", $str);
            $match = preg_split("/[\s]/", $str, 3);

            if( !isset($match[1]) )
                continue;
            // $match[0] == param
            // $match[1] == 参数类型
            // $match[2] == 参数
            // $match[3] == 参数描述

            if( !isset($match[2]) )
                $match[2] = "";

            $res[trim(trim($match[1],"$"))] = [
                "type" => trim( $match[0] ),
                "doc"  => trim( $match[2] )
            ];
        }
        return $res;
    }

    /**
     * @获取格式化后的类doc文档
     */
    public function getDocFormat(){

        $doc = $this->raw_data["doc"];
        $doc = str_replace(["/*","*/"],"",$doc);
        $doc = str_replace("*","",$doc);
        $doc = trim( $doc );
        $doc = str_replace("\n","<br/>",$doc);
        $doc = preg_replace("/http[s]?\:\/\/[\.a-zA-Z0-9\/\-]{1,}/",'<a target="__blank" href="$0">$0</a>',$doc);

        return $doc;
    }

}