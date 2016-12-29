<?php namespace Wing\Doc;
use Wing\FileSystem\WDir;
use Wing\FileSystem\WFile;

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
     * @var array 排除的文件名
     */
    private $exclude_filename = [];

    /**
     * @var array 排除的文件
     */
    private $exclude_file = [];

    /**
     * @请保证此文件的可写和可读
     */
    private $cache_path;// = __DIR__."/cache";

    /**
     * @构造函数
     *
     * @param string $input_dir 输入目录
     * @param string $output_dir 输出目录
     */
    public function __construct($input_dir, $output_dir)
    {
        $input_dir        = str_replace("\\","/",$input_dir);
        $this->input_dir  = rtrim($input_dir,"/");
        $this->out_dir    = $output_dir;
        $this->cache_path = __DIR__."/cache";
    }

    public function setCachePath( $path ){
        $this->cache_path = $path;
    }

    /**
     * @添加支持的文件后缀
     *
     * @param string|array $ext
     */
    public function addSupportFileExtension( $ext ){
        if( is_array($ext) )
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
     * @添加排除的文件名，可以包含扩展，也可以不含扩展
     */
    public function addExcludeFileName($file_name){
        if( is_array( $file_name ) )
        {
            $this->exclude_filename = array_merge( $this->exclude_filename, $file_name );
        }
        else
        {
            $this->exclude_filename[] = $file_name;
        }
    }


    /**
     * @添加排除的文件
     */
    public function addExcludeFile($file){
        if( is_array($file) ){
            $this->exclude_file = array_merge( $this->exclude_file, $file );
        }
        else{
            $this->exclude_file[] = $file;
        }
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

        $this->helperScandir();

        $cache_dir = new WDir($this->cache_path);
        $cache_dir->mkdir();

        $cache_file = new WFile($this->cache_path."/wing_doc");
        $cache_file->delete();

        $html     = file_get_contents(__DIR__."/template/index.html");
        $datas    = $this->filesDataFormat();
        $left_nav = $this->htmlFormat( $datas );
        $html     = str_replace('{$left_nav}',$left_nav,$html);

        $template_dir = new WDir(__DIR__."/template");
        $template_dir->copyTo( $this->out_dir, true );

        foreach( $this->files as $file ){


            $wfile      = new \Wing\Doc\WFile($file);
            $classes    = $wfile->getClasses();

            $class_html = '<div data-file="'.$file.'" class="hide class_tap '.md5($file).'">';
            foreach ( $classes as $class ){
                if( !$class instanceof WClass )
                    continue;
                $namespace = $class->getNamespace();
                if( $namespace )
                    $namespace.="\\";
                $class_name = $namespace.$class->getClassName();
                $class_html .= '<h2 class="class-name">'.$class_name.'</h2>';
                $class_html .= '<div class="file-path">'.$file.'</div>';
                $class_html .= '<div class="doc p22">
                                <img src="img/doc.png">
                                <div class="doc-tab">'.$class->getDocFormat().'</div>
                                </div>';

                $functions = $class->getFunctions();
                foreach ( $functions as $index => $function ){
                    if(!$function instanceof WFunction )
                        continue;
                    $static = $function->getStatic();
                    if( $static )
                        $static.=" ";

                    $func_doc = $function->getDoc();

                    $access = $function->getAccess();
                    if( $access )
                        $access.=" ";

                    $func_doc_str = trim( $func_doc->doc );
                    $func_doc_str = str_replace("\n","<br/>",$func_doc_str);

                    $class_html .= '<div class="class-func"><label class="index-count">'.($index+1)."、</label>".$access.$static."function ".$function->getFunctionName().'</div>';
                    $class_html .= '<div class="doc p22"><div class="doc-tab">'.$func_doc_str.'</div></div>';

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

                    $return      = $function->getDocReturn();
                    $return      = str_replace("\n","<br/>",$return);
                    $class_html .= '<div class="return p22">返回值：'.$return.'</div>';

                    //如果配置了多个url 则返回的是数组
                    $url = $func_doc->url;
                    if( $url ){

                        $randc = substr(md5(rand(0,99999999)),rand(0,16),16);
                        $class_html .= '<div class="request-tab '.$randc.'" randc="'.$randc.'">';

                        $class_html .= '<div class="http-api-tip p22"><span>如果是数值类型的表单，最小长度、最大长度代表的意思是最小值与最大值，最大值为0代表不限</span></div>';


                        if( !is_array( $url ))
                            $url = [$url];


//                        $requests = $func_doc->request;
//                        if( $requests && !is_array( $requests ))
//                            $requests = [ $requests ];

                        $class_html .=
                            '<div class="request p22">
                                    <label class="r-item data-key">表单字段</label>
                                    <label class="r-item data-type">值类型</label>
                                    <label class="r-item data-min" title="如果是数值类型的数据，则是最小值">最小长度</label>
                                    <label class="r-item data-max" title="如果是数值类型的数据，则是最大值，最大值是0代表不限">最大长度</label>
                                    <label class="r-item data-doc">说明文档</label>
                                    </div>';
                        $requests = $function->getRequest();
                        if( $requests ) {
                            foreach ($requests as $request) {
                                $class_html .=
                                    '<div class="request p22 request-datas">
                                    <label class="r-item data-key">' . $request["key"] .'</label>
                                    <label class="r-item data-type">'. $request["type"] .'</label>
                                    <label class="r-item data-min">' . $request["min"] . '</label>
                                    <label class="r-item data-max">' . $request["max"] . '</label>
                                    <label class="r-item data-doc">' . $request["doc"] . '</label>
                                    </div>';

                                if( $request["type"] == "json" ) {
                                    $class_html .= '<div class="request p22 request-template">
                                    <label class="r-item data-key">数据模板：</label><label class="r-item data-template">' . $request["template"] . '</label>
                                    </div>';
                                }

                            }
                        }
                        $class_html .= '<div class="http-test p22">';
                        $class_html .= '<div class="http-api"><span>http接口</span><span class="http-api-test-btn">测试</span></div>';

                        foreach ( $url as $_url)
                        {
                            $response_format = $function->getResponseFormat();
                            $class_html .= '<div class="visit-url"><label title="输出格式为'.$response_format.'" class="response">'.$response_format.'</label>：<label class="url">'.$_url.'</label></div>';
                        }

                        $class_html .= '<div class="info">
                                            <label>urls(<a>'.count($url).'</a>)</label>
                                            <label>forms(<a>'.count($requests).')</a></label>

                                            </div>';

                        $class_html .= '<div class="process"></div>';
                        if( $requests ) {
                            foreach ($requests as $request) {
                                $rname = $request["key"].substr(md5(rand(0,99999999)),rand(0,16),16);
                                $class_html .= '<div class="input-form">
                                                    <label class="data-key">' . $request["key"] . '</label>
                                                    <span>
                                                        <label><input name="'.$rname.'" checked type="radio"/>随机</label>
                                                        <label><input name="'.$rname.'" type="radio"/>递增</label>
                                                        <label><input name="'.$rname.'" type="radio"/>指定</label>
                                                    </span>
                                                    <input class="data-value '.$request["key"].'" type="text"/>

                                                </div>';
                            }
                        }

                        $class_html .= '<div class="info result-info" style="padding-top: 8px">
                                            <label>status(<a class="status">0</a>)</label>
                                            <label>headers(<a class="headers">0</a>)</label>

                                            </div>';

                        $class_html .= '<div class="http-result">
                                        <textarea></textarea>
                                        </div>';

                        $class_html .= '</div>';

                        //$class_html .= '<div class="request-response p22">输出：<label>'.$function->getResponseFormat().'</label></div>';
                        $class_html .= '</div>';

                    }


                }
                unset($functions,$class_name);
            }
            $class_html .= '</div>';

            $cache_file->append($class_html);

            unset( $class_html, $wfile, $classes );

        }

        $html     = str_replace('{$class_html}',$cache_file->read(),$html);

        file_put_contents($this->out_dir."/index.html",$html);

    }


    /**
     * @目录遍历
     *
     * @return void
     */
    private function helperScandir(){
       // $this->files[] =
         //   "/Users/yuyi/Web/xiaoan/api/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/Reader.php";
            //"/Users/yuyi/Web/xiaoan/api/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationException.php";
       //return;
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
                        break;
                    }
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

                    $is_pass = false;
                    foreach ( $this->exclude_filename as $ex_file_name){
                        if( $ex_file_name ==  $info["basename"] || $ex_file_name == $info["filename"] ){
                            $is_pass = true;
                            break;
                        }
                    }

                    foreach ( $this->exclude_file as $ex_file ) {
                        $ex_file = str_replace("\\","/",$ex_file );
                        if( $ex_file == str_replace("\\","/", $item ) )
                        {
                            $is_pass = true;
                            break;
                        }
                    }

                    if( $is_pass )
                        continue;

                    $ext  = "";
                    if( isset($info["extension"]) )
                        $ext = $info["extension"];
                    if( in_array($ext,$this->support_file_ext) )
                        $this->files[] = $item;
                }
            }
        }


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

                //$target_link = strtolower(str_replace($this->input_dir,"",$file)).".html";
                //$target_link = ltrim($target_link,"/");

                $link = md5($file);
                $html .= '<li class="is-file h li-'.$link.'" data-tab="'.md5($file).'" data-file="'.$file.'">
                <span>'.$name.'</span>
                </li>';
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