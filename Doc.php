<?php namespace Wing\Doc;

/**
 * 文档生成器
 * 与phpdoc不同的是，WingDoc使用的是简单的正则分析，运行时无上下文依赖
 * 因此，WingDoc支持所有的php系统
 *
 * @author yuyi
 * @version V1.0
 * @email 297341015@qq.com
 * @create-at 2016-12
 */
class Doc{
    private $input_dir;
    private $out_dir;
    private $dirs  = [];
    private $files = [];

    /**
     * @var array 支持的文件后缀
     */
    private $support_file_ext = [
        "php","","html","htm"
    ];

    /**
     * @var array 排除的文件路径
     */
    private $exclude_path = [];


    /**
     * @构造函数
     *
     * @param string $input_dir 输入目录
     * @param string $output_dir 输出目录
     */
    public function __construct($input_dir, $output_dir)
    {
        $this->input_dir = $input_dir;
        $this->out_dir   = $output_dir;
    }


    /**
     * @添加排除文件后缀
     *
     * @param string|array $ext
     */
    public function addSupportFileExtension( $ext ){
        if(is_array($ext))
            $this->support_file_ext = array_merge( $this->support_file_ext, $ext );
        else
            $this->support_file_ext[] = $ext;
    }

    /**
     * @添加排除目录
     *
     * @param string|array $path
     */
    public function addExcludePath($path){
        if(is_array($path))
            $this->exclude_path = array_merge( $this->exclude_path, $path );
        else
            $this->exclude_path[] = $path;
    }


    /**
     * @程序入口
     */
    public function run(){
        $this->parse();
    }

    /**
     * @格式化模板文件 生成doc html
     */
    private function parse(){

        $class_html = "";
        $files      = $this->helperScandir();

        foreach( $files as $file ){
            echo $file,"\r\n";
            $wfile     = new WFile($file);
            $classes   = $wfile->getClasses();
            $file_info = pathinfo( $file );
            $class     = md5($file);//str_replace(".","-",$file_info["basename"]);
            $class_html      .= '<div data-file="'.$file.'" class="hide class_tap '.$class.'">';
            foreach ( $classes as $class ){
                if( !$class instanceof WClass )
                    continue;
                $cfile      = $class->getBaseName();
                $dirname    = $class->getDirName();
                $class_name = $class->getNamespace()."\\".$class->getClassName();
                $class_html .= '<h2 class="class-name">'.$class_name.'</h2>';
                $class_html .= '<div class="file-path">'.$file.'</div>';
                $class_html .= '<div class="doc p22"><img src="img/doc.png">'.$class->getDoc()->doc.'</div>';

                $functions = $class->getFunctions();
                //var_dump($functions);
                foreach ( $functions as $index => $function ){
                    if(!$function instanceof WFunction )
                        continue;
                    $static = $function->getStatic();
                    if( $static )
                        $static.=" ";

                    $func_doc = $function->getDoc();

                    //echo "function==>",$function->getFunctionName(),"\r\n";
                    $class_html .= '<div class="class-func"><label class="index-count">'.($index+1)."、</label>".$function->getAccess()." ".$static."function ".$function->getFunctionName().'</div>';
                    $class_html .= '<div class="doc p22">'.$func_doc->doc.'</div>';

                    $params = $function->getParams();
                    if(is_array( $params ) && count($params) > 0 )
                    {
                        $class_html .= '<div class="param p22">
                                                <label class="var">参数</label>
                                                <label class="type">类型</label>
                                                <label class="default">默认值</label>
                                                <label class="pdoc">文档</label>
                                                </div>';
                        foreach ( $params as $var => $param )
                        {
                            if( !$param["type"] )
                                $param["type"] = "&nbsp;";
                            if( !$param["default"] )
                                $param["default"] = "&nbsp;";
                            if( !$param["doc"] )
                                $param["doc"] = "&nbsp;";

                            $class_html .= '<div class="param p22">
                                                <label class="var">'.$var.'</label>
                                                <label class="type" title="'.$param["type"].'">'.$param["type"].'</label>
                                                <label class="default">'.$param["default"].'</label>
                                                <label class="pdoc">'.$param["doc"].'</label>
                                                </div>';
                        }
                    }

                    $return = $function->getDocReturn();
                    if(!$return)
                        $return = "void<label class='tip'>(推测值)</label>";
                    $return = str_replace("\n","<br/>",$return);
                    $class_html .= '<div class="return p22">返回值：'.$return.'</div>';

                }
            }
            $class_html .= '</div>';

            //echo "\r\n\r\n\r\n";
        }

        $datas    = $this->filesDataFormat();
        $left_nav = $this->htmlFormat( $datas );

        $html = file_get_contents(__DIR__."/template/index.html");
        $html = str_replace('{$left_nav}',$left_nav,$html);
        $html = str_replace('{$class_html}',$class_html,$html);

        $template_dir = new WDir(__DIR__."/template");
        $template_dir->copyTo( $this->out_dir, true );

        file_put_contents($this->out_dir."/index.html",$html);
    }


    /**
     * @目录遍历
     *
     * @return array
     */
    private function helperScandir(){
        $path[] = $this->input_dir.'/*';
        while(count($path) != 0)
        {
            $v = array_shift($path);
            foreach(glob($v) as $item)
            {

                $is_match = false;
                foreach ( $this->exclude_path as $c) {
                    $c     = str_replace("/", "\/", $c);
                    $c     = str_replace("*", ".*", $c);
                    $is_match = preg_match("/$c/", $item);
                    if( $is_match )
                    {
                        echo "---match";
                        echo "\r\n\r\n";
                        break;
                    }else{
                        echo "---not match";
                    }
                    echo "\r\n\r\n";
                }

                if($is_match) continue;

                if (is_dir($item))
                {
                    $this->dirs[] = $item;
                    $path[] = $item . '/*';
                }
                elseif (is_file($item))
                {
                    $info = pathinfo( $item );
                    $ext = "";
                    if( isset($info["extension"]) )
                        $ext = $info["extension"];
                    if( in_array($ext,$this->support_file_ext) )
                        $this->files[] = $item;
                }
            }
        }

        return $this->files;

    }

    /**
     * @树形结构html生成
     *
     * @return string
     */
    private function htmlFormat(array $datas){
        $html = '<ul class="file-list">';
        foreach ( $datas as $dir=>$data ){
            if(is_array($data))
            {
                $html .= '<li class="is-dir h bg"><img src="img/d.png"/><span>'.$dir.'</span></li>';
                $html .= '<li class="is-dir">'.$this->htmlFormat( $data ).'</li>';
            }
            else
            {
                list($name,$file) = explode("|",$data);
                $html .= '<li class="is-file h" data-tab="'.md5($file).'" data-file="'.$file.'"><span>'.$name.'</span></li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * @获取所有的目录
     *
     * @return array
     */
    public function getDirs(){
        return $this->dirs;
    }

    /**
     * @获取所有的文件
     *
     * @return array
     */
    public function getFiles(){
        return $this->files;
    }

    /**
     * @文件路径树型结构生成算法
     *
     * @return array
     */
    private function filesDataFormat(){

        $pdir  = $this->input_dir;
        $files = $this->files;
        $datas = [];

        foreach ( $files as $file ){
            $raw_file = $file;
            $file = ltrim(str_replace($pdir,"",$file),"/");
            $info = pathinfo($file);
            //dirname basename
            $sp = explode("/",$info["dirname"]);
            $tt = &$datas;
            $last = array_pop($sp);
            foreach ( $sp as $d){
                if(!isset($tt[$d]))
                    $tt[$d] = [];
                $tt = &$tt[$d];
            }
            $tt[$last][] = $info["basename"]."|".$raw_file;
            $tt = &$tt[$last];
        }

        return $datas;
    }
}