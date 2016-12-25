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
    public function __construct($function_name,array $raw_data)
    {
        var_dump($function_name,$raw_data);
        $this->raw_data = $raw_data;
        $this->function_name = $function_name;
    }
    public function getStatic(){
        return $this->raw_data["static"];
    }
    public function getAccess(){
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
            $temp = explode("$",$param);
            $type = trim($temp[0]);
            $var_value = $temp[1];
            $temp = explode("=",$var_value);
            $var  = trim($temp[0]);
            $default = isset($temp[1])?$temp[1]:"";
            $default = trim($default);
            if( $default =='""' )
                $default ="空";


            $doc  = isset($doc_params[$var]["doc"])?$doc_params[$var]["doc"]:"";
            if( !$type ){
                $type = isset($doc_params[$var]["type"])?$doc_params[$var]["type"]:"";
            }
            $res[$var] = [
                "doc"     => $doc,
                "type"    => $type,
                "default" => $default
            ];
        }
        return $res;
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
            $str = str_replace(["\r", "\n"], " ", $str);
            $str = preg_replace("/[\s]+/", " ", $str);
            $match = preg_split("/[\s]/", $str, 3);

            // $match[0] == param
            // $match[1] == 参数类型
            // $match[2] == 参数
            // $match[3] == 参数描述

            $res[trim(trim($match[1],"$"))] = [
                "type" => trim( $match[0] ),
                "doc"  => trim( $match[2] )
            ];
        }
        var_dump($res);
        return $res;
    }
}