<?php namespace Wing\Doc;

/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/23
 * Time: 14:21
 * @author yuyi
 */
class WDoc{

    private $raw_doc;
//    public $api;
//    public $author;
//    public $category;
//    public $copyright;
//    public $deprecated;
//    public $example;
//    public $filesource;
//    public $global;
//    public $ignore;
//    public $internal;
//    public $license;
//    public $link;
//    public $method;
//    public $package;
//    public $param;
//    public $property;
//    public $property_read;
//    public $property_write;
//    public $return;
//    public $see;
//    public $since;
//    public $source;
//    public $subpackage;
//    public $throws;
//    public $todo;
//    public $uses;
//    public $var;
//    public $version;
//    public $doc;

private $prev = null;

private $attr = [];
    public function __construct($raw_doc)
    {
        $this->raw_doc = $raw_doc;
        $this->parse();
    }

    private function parse(){
        $datas = explode("\n",$this->raw_doc);
        foreach ( $datas as $key => &$data ){
            $data = trim($data,"/");
            $data = trim($data,"*");
            $data = trim($data);
            if( !$data )
                unset($datas[$key]);
        }
        $this->attr["doc"] = "";
        //在注释的最前面 没有以@开头的行 都认为是函数或者类的描述性注释
        foreach ( $datas as $key => $value ) {
            $value = trim($value, "*");
            $value = trim($value);


            preg_match("/@[a-zA-Z0-9]{1,}[\s\S]{1,}?/",$value,$match);
            if($match){
               break;
            }

            $value = trim($value,"@");
            $value = trim($value);

            $this->attr["doc"] .= $value . "<br/>";
            unset( $datas[$key] );
        }

        foreach ( $datas as $value ){
            $value = trim($value,"*");
            $value = trim($value);

            if (strpos($value, "@") !== 0) {
                if( $this->prev )
                    $this->prev.="\r\n".$value;
                continue;
            }


            preg_match("/@[a-zA-Z0-9]{1,}[\s\S]{1,}?/",$value,$match);

            if(!$match){
                $value = trim($value,"@");
                $value = trim($value);
                if( $this->prev )
                    $this->prev.="\r\n".$value;
                continue;
            }


            $v   = str_replace($match[0],"",$value);
            $v   = trim($v);
            $tag = trim($match[0],"@");
            $tag = trim($tag);

            $this->attr[$tag][] = $v;

            //使用引用记住上一行的tag 用于换行的doc拼接
            $this->prev = &$this->attr[$tag][count($this->attr[$tag])-1];

            //var_dump( $this->$tag );
        }

        //var_dump($this);
    }

    public function __set($name, $value)
    {
        $this->attr[$name][] = $value;
    }
    public function __get($name)
    {
        if(!isset($this->attr[$name]) || !$name )return null;
        if( !is_array($this->attr[$name]))
            return $this->attr[$name];
        if( count($this->attr[$name]) == 1 )
            return $this->attr[$name][0];

        return $this->attr[$name];
    }

}


//$app = new WDoc("/Users/yuyi/Web/activity/app/Logic/Lib2",__DIR__."/doc");
//$app->run();