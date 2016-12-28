<?php namespace Wing\Doc;


/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/11/8
 * Time: 08:05
 *
 * @文件操作类
 * @property string $__file_name
 * @property string $file_name 文件名带扩展
 * @property string $ext 扩展
 * @property WDir $path 文件所在路径
 * @property int $size 文件大小 字节数
 */
class WFileHelper{

    private $__file_name;
    public $file_name;
    public $ext;
    public $path;
    public $size; //字节数

    public function __construct( $file_name )
    {
        if( $file_name instanceof self )
            $file_name = $file_name->get();
        $file_name = str_replace("\\","/",$file_name);
        $this->init( $file_name );
    }


    private function init($file_name){
        unset($this->path);
        $info              = pathinfo( $file_name );
        $this->file_name   = $info["basename"];
        $this->ext         = isset($info["extension"])?$info["extension"]:"";
        $this->path        = new WDir($info["dirname"]);
        $this->__file_name = $file_name;
        $this->size        = file_exists( $file_name ) ? filesize( $file_name ) : 0;
    }

    public function get(){
        return $this->__file_name;
    }

    /**
     * @深度创建文件
     *
     * @param string $file_name 需要创建的文件路径
     * @return bool
     */
    public function touch(){
        if( file_exists( $this->__file_name ))
            return true;
        $this->path->mkdir();
        $success    = touch( $this->__file_name );
        $this->size = file_exists( $this->__file_name ) ? filesize( $this->__file_name ) : 0;
        return $success;
    }

    public function exists(){
        return file_exists($this->__file_name);
    }

    /**
     * @复制到
     *
     * @param string|static $file_name 可以是目录，也可以是完整路径（包含文件名）
     * @param bool $rf 如果已存在 是否覆盖 默认为false 不覆盖
     * @return bool
     */
    public function copyTo( $file_name, $rf = false ){

        if( $file_name instanceof self )
            $file_name = $file_name->get();

        $file_name = str_replace("\\","/",$file_name);
        if( is_dir( $file_name ))
        {
            $file_name = rtrim( $file_name,"/");
            $file_name = $file_name."/".$this->file_name;
        }

        if( !$rf && file_exists( $file_name ))
            return false;

        if(!$this->exists())
            $this->touch();

        $file = new self($file_name);
        $file->path->mkdir();

        return copy( $this->__file_name, $file_name );
    }

    /**
     * @文件移动到
     *
     * @param string $file_name 目标文件路径 如D:/123.txt
     * @param bool $rf 如果文件已存在是否覆盖，默认为否
     */
    public function moveTo( $file_name, $rf = false ){

        if( $file_name instanceof self )
            $file_name = $file_name->get();

        if( file_exists( $file_name ) && !$rf )
            return false;

        $file_name = str_replace("\\","/",$file_name);
        $file = new self($file_name);
        $file->path->mkdir();


        $res = rename($this->__file_name, $file_name);

        if( $res ){
            $this->init( $file_name );
        }
        return $res;
    }

    public function write( $content, $append = true ){
        $this->touch();
        if( $append )
            return file_put_contents( $this->__file_name, $content, FILE_APPEND ) !== false;
        else
            return file_put_contents( $this->__file_name, $content ) !== false;
    }

}