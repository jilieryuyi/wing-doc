<?php namespace Wing\Doc;
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/24
 * Time: 06:48
 */
class WClass{
    private $class_name;
    private $namespace;
    private $doc;

    private $raw;

    public function __construct($class_name,array $data)
    {
        $this->raw        = $data;
        $this->class_name = $class_name;
        $this->namespace  = isset($data["namespace"])?$data["namespace"]:"";
        $this->doc        = isset($data["doc"])?$data["doc"]:"";
    }

    /**
     * @return WDoc
     */
    public function getDoc(){
        return new WDoc($this->doc);
    }

    /**
     * @获取格式化后的类doc文档
     */
    public function getDocFormat(){

        $doc = $this->doc;
        $doc = str_replace(["/*","*/","@"],"",$doc);
        $doc = str_replace("*","",$doc);
        $doc = trim( $doc );
        $doc = str_replace("\n","<br/>",$doc);
        $doc = preg_replace("/http[s]?\:\/\/[\.a-zA-Z0-9\/\-]{1,}/",'<a target="__blank" href="$0">$0</a>',$doc);

        return $doc;
    }

    public function getClassName(){
        return $this->class_name;
    }
    public function getNamespace(){
        return $this->namespace;
    }
    public function getRawDoc(){
        return $this->doc;
    }
    public function getDirName(){
        return $this->raw["dirname"];
    }
    public function getBaseName(){
        return $this->raw["basename"];
    }
    public function getExtension(){
        return $this->raw["extension"];
    }
    public function getFileName(){
        return $this->raw["filename"];
    }


    private function methodsFormat(){

        $methods = $this->getDoc()->method;
        if( !is_array($methods))
            return [];

        $functions = [];
        foreach ( $methods as $doc ) {
                //$doc = "bool isSelf(int \$user_id) 判断是否为指定用户所有 如果是返回true";
                preg_match("/[\s\S]{1,}\)/", $doc, $match);

                if( count($match) <= 0 )
                    continue;

                $func_str = $match[0];

                preg_match("/[\s].+?[a-zA-Z0-9_]{1,}\(/", $func_str, $match);


                if( !$match )
                    continue;

                $function_name = trim($match[0]);
                $function_name = trim($function_name, "(");

                preg_match("/\([\s\S]{1,}\)/", $func_str, $match);
                $params = [];

                if( count($match) > 0 ) {
                    $params_str = trim($match[0], "(");
                    $params_str = trim($params_str, ")");
                    $params_str = trim($params_str);
                    $params = explode(",", $params_str);
                }


            preg_match("/\)[\s\S]{1,}/", $doc, $match);
            $doc = "";
            if( count($match) > 0 ) {
                $doc = trim($match[0], ")");
                $doc = trim($doc);
                $doc = "/**\r\n *" . $doc . "\r\n */";
            }

            $functions[$function_name] = [
                "doc"    => $doc,
                "params" => $params,
                "access" => "public",
                "static" => ""
            ];
        }
        return $functions;
    }

    /**
     * @return array WFunction
     */
    public function getFunctions(){

        $methods   = $this->methodsFormat();
        $functions = array_merge($this->raw["functions"],$methods);
        $res       = [];

        foreach ( $functions as $function_name => $function ){
            $res[] = new WFunction( $function_name, $function, $this->class_name );
        }

        return $res;
    }

}