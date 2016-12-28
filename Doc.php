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

    //使用base64图片 避免相对路径引起无法加载
    private $dpng      = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAYCAMAAADAi10DAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAABO1BMVEWtQDitQDitQDitQDitQDisPTXEdW+tQDitQDitQDitQDitQDiuQjvgtrOtQDitQDitQDitQDitQDiwRj/r0M6tQDitQDitQDitQDitPzetPzesPTSwSECzTkayS0PaqaXoycavRT3SlZH89/esPjWuQTnPkIutQDitPzetQTmtQDitPzetQDitQDitPzetQTnPj4v79fStQDitPzeuQjrUm5atQDirPDOsPjatQDitQDiwRz/r0M6tQDiwRz/r0M6tQDitQDisPzetQDi4WVKsPjatQDitQDjqzcu4WVKsPjatQDiwRz/8+PfqzcusPTWwRz/r0M7PkIvqzsu3WFGvRD3r0M6tQTnPkIv8+Pfqzcu7YFnqzsytPzeuQTnPkIv89/fs0tHy4eCtQDitQTnPkIv79/b///8AAACOR7RBAAAAOXRSTlMGGBoaGhoaLsfX1tbW1jfv//////837f837f837f837f837f837f837f/t/////////9bW1tYaGhq1VBoTAAAAAWJLR0Roy2z0IgAAAAlwSFlzAAABLAAAASwAc4jpUgAAAAd0SU1FB+AJGhccJ7IwOWsAAAENSURBVBjTzZHVloIAFEWvYmJid3d3K2KjYrdix///wYDM/MO87bWfzjobgMdHEEQgFImEAgb4PACxRIqiqEyuUMhlDEglYlCq1BiGabQ6nVbDgFqlBL3BWC6XK9VarVphwGjQ/6p6A8cb9f+kTGZLk/hTRNNiNoHVZm+1CU4R7ZbdZgWH09Xp9vqs6ve6HZfTAW6Pd0AOR2McH4+G5MDrcYPPH6Am5HQ2n8+m5IQK+H0QDIUX1HK13mzWqyW1CIeCoI9Et7v94Xg6HQ/73TYaYXfF6PPlervfb9fLmY5xUx/08/X+fN6vJ/1gp7JHxxPJVDqdSibi36O/OTLZXD6fy2a4HFy0QrFUKha4aD+l0E1b20sdNwAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxNi0wOS0yNlQyMzoyODozOSswODowMJjaWjgAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTYtMDktMjZUMjM6Mjg6MzkrMDg6MDDph+KEAAAATXRFWHRzb2Z0d2FyZQBJbWFnZU1hZ2ljayA3LjAuMS02IFExNiB4ODZfNjQgMjAxNi0wOS0xNyBodHRwOi8vd3d3LmltYWdlbWFnaWNrLm9yZ93ZpU4AAABjdEVYdHN2Zzpjb21tZW50ACBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE4LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgINPdg9wAAAAYdEVYdFRodW1iOjpEb2N1bWVudDo6UGFnZXMAMaf/uy8AAAAYdEVYdFRodW1iOjpJbWFnZTo6SGVpZ2h0ADEzNr3fXcYAAAAXdEVYdFRodW1iOjpJbWFnZTo6V2lkdGgAMTAyAm6aQQAAABl0RVh0VGh1bWI6Ok1pbWV0eXBlAGltYWdlL3BuZz+yVk4AAAAXdEVYdFRodW1iOjpNVGltZQAxNDc0OTAzNzE5gkEbqQAAABF0RVh0VGh1bWI6OlNpemUAMS42S0LmRDDjAAAAX3RFWHRUaHVtYjo6VVJJAGZpbGU6Ly8vaG9tZS93d3dyb290L3NpdGUvd3d3LmVhc3lpY29uLm5ldC9jZG4taW1nLmVhc3lpY29uLmNuL3NyYy8xMjA1NC8xMjA1NDA4LnBuZ8DSWZ0AAAAASUVORK5CYII=";
    private $docpng    = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAYCAMAAADAi10DAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAABJlBMVEUAAADsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVH///9qOsXXAAAAYHRSTlMApvv///93IOKIBAAA+P//IOb2dAMAAPz/IOX/9nQDAPz/5f//9nMEdyDm////9oV3G8ni4ODi1v93ABgbGxsa//+8fn5+fn55///////4//X8///1+P////Cm+///+Z2bFHHwAAAAAWJLR0RhsrBMhgAAAAd0SU1FB94MBwwoNMk0oTwAAACSSURBVBjTY2BkYk4AARZWNnYOTi5uHgYGXj5+mJCAoJCwiKgYg7hEAkxIUkpaRlZOnkFBESGkpKyiqqaOJKShqaWto6unjyRkYGhkbGJqZo4QsrC0sraxtbWzhws5ODo5u7i6ubl7wIUSPL28fYDA1w8hBAX+AcNPKDAIVSg4hCE0jB9ZJDwikiEqOgZZKDYuHgABkX/pH93PkgAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxNi0wOS0xN1QxNToyMToyNCswODowMOjEIL8AAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTQtMTItMDdUMTI6NDA6NTIrMDg6MDDs7qGsAAAATXRFWHRzb2Z0d2FyZQBJbWFnZU1hZ2ljayA3LjAuMS02IFExNiB4ODZfNjQgMjAxNi0wOS0xNyBodHRwOi8vd3d3LmltYWdlbWFnaWNrLm9yZ93ZpU4AAAAYdEVYdFRodW1iOjpEb2N1bWVudDo6UGFnZXMAMaf/uy8AAAAYdEVYdFRodW1iOjpJbWFnZTo6SGVpZ2h0ADYwMHrevbUAAAAXdEVYdFRodW1iOjpJbWFnZTo6V2lkdGgANDUyedKs7wAAABl0RVh0VGh1bWI6Ok1pbWV0eXBlAGltYWdlL3BuZz+yVk4AAAAXdEVYdFRodW1iOjpNVGltZQAxNDE3OTI3MjUydRES4wAAABJ0RVh0VGh1bWI6OlNpemUANC4wOEtCgeab7AAAAF90RVh0VGh1bWI6OlVSSQBmaWxlOi8vL2hvbWUvd3d3cm9vdC9zaXRlL3d3dy5lYXN5aWNvbi5uZXQvY2RuLWltZy5lYXN5aWNvbi5jbi9zcmMvMTE4MTQvMTE4MTQwMC5wbmfb6vLTAAAAAElFTkSuQmCC";
    private $apng      = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAYCAMAAADAi10DAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAABJlBMVEUAAADsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVHsbVH///9qOsXXAAAAYHRSTlMApvv///93IOKIBAAA+P//IOb2dAMAAPz/IOX/9nQDAPz/5f//9nMEdyDm////9oV3G8ni4ODi1v93ABgbGxsa//+8fn5+fn55///////4//X8///1+P////Cm+///+Z2bFHHwAAAAAWJLR0RhsrBMhgAAAAd0SU1FB94MBwwoNMk0oTwAAACSSURBVBjTY2BkYk4AARZWNnYOTi5uHgYGXj5+mJCAoJCwiKgYg7hEAkxIUkpaRlZOnkFBESGkpKyiqqaOJKShqaWto6unjyRkYGhkbGJqZo4QsrC0sraxtbWzhws5ODo5u7i6ubl7wIUSPL28fYDA1w8hBAX+AcNPKDAIVSg4hCE0jB9ZJDwikiEqOgZZKDYuHgABkX/pH93PkgAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxNi0wOS0xN1QxNToyMToyNCswODowMOjEIL8AAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTQtMTItMDdUMTI6NDA6NTIrMDg6MDDs7qGsAAAATXRFWHRzb2Z0d2FyZQBJbWFnZU1hZ2ljayA3LjAuMS02IFExNiB4ODZfNjQgMjAxNi0wOS0xNyBodHRwOi8vd3d3LmltYWdlbWFnaWNrLm9yZ93ZpU4AAAAYdEVYdFRodW1iOjpEb2N1bWVudDo6UGFnZXMAMaf/uy8AAAAYdEVYdFRodW1iOjpJbWFnZTo6SGVpZ2h0ADYwMHrevbUAAAAXdEVYdFRodW1iOjpJbWFnZTo6V2lkdGgANDUyedKs7wAAABl0RVh0VGh1bWI6Ok1pbWV0eXBlAGltYWdlL3BuZz+yVk4AAAAXdEVYdFRodW1iOjpNVGltZQAxNDE3OTI3MjUydRES4wAAABJ0RVh0VGh1bWI6OlNpemUANC4wOEtCgeab7AAAAF90RVh0VGh1bWI6OlVSSQBmaWxlOi8vL2hvbWUvd3d3cm9vdC9zaXRlL3d3dy5lYXN5aWNvbi5uZXQvY2RuLWltZy5lYXN5aWNvbi5jbi9zcmMvMTE4MTQvMTE4MTQwMC5wbmfb6vLTAAAAAElFTkSuQmCC";
    private $githubpng = "data:image/png;base64,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dHdhcmUASW1hZ2VNYWdpY2sgNy4wLjEtNiBRMTYgeDg2XzY0IDIwMTYtMDktMTcgaHR0cDovL3d3dy5pbWFnZW1hZ2ljay5vcmfd2aVOAAAAY3RFWHRzdmc6Y29tbWVudAAgR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjQsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAOSYPjAAAAGHRFWHRUaHVtYjo6RG9jdW1lbnQ6OlBhZ2VzADGn/7svAAAAGHRFWHRUaHVtYjo6SW1hZ2U6OkhlaWdodAAyNTeexHSPAAAAF3RFWHRUaHVtYjo6SW1hZ2U6OldpZHRoADI2MyF1swgAAAAZdEVYdFRodW1iOjpNaW1ldHlwZQBpbWFnZS9wbmc/slZOAAAAF3RFWHRUaHVtYjo6TVRpbWUAMTM5ODY1NjYyORCN+kUAAAASdEVYdFRodW1iOjpTaXplADUuNDJLQshPWsgAAABfdEVYdFRodW1iOjpVUkkAZmlsZTovLy9ob21lL3d3d3Jvb3Qvc2l0ZS93d3cuZWFzeWljb24ubmV0L2Nkbi1pbWcuZWFzeWljb24uY24vc3JjLzExNTQ2LzExNTQ2NjIucG5nQbITngAAAABJRU5ErkJggg==";

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
     * @构造函数
     *
     * @param string $input_dir 输入目录
     * @param string $output_dir 输出目录
     */
    public function __construct($input_dir, $output_dir)
    {
        $input_dir       = str_replace("\\","/",$input_dir);
        $this->input_dir = rtrim($input_dir,"/");
        $this->out_dir   = $output_dir;
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

        //$class_html = "";
        $this->helperScandir();

        $html     = file_get_contents(__DIR__."/template/index.html");
        $datas    = $this->filesDataFormat();
        $left_nav = $this->htmlFormat( $datas );
        $html     = str_replace('{$left_nav}',$left_nav,$html);
        $html     = str_replace('{$dpng}',$this->dpng,$html);
        $html     = str_replace('{$apng}',$this->apng,$html);
        $html     = str_replace('{$githubpng}',$this->githubpng,$html);

        $template_dir = new WDir(__DIR__."/template");
        $template_dir->copyTo( $this->out_dir, true );

        $file_count = 0;
        foreach( $this->files as $file ){

            echo $file,"\r\n";
            $target_file = new WFile(
                $this->out_dir."/".strtolower(str_replace($this->input_dir,"",$file)).".html"
            );
            $target_file->touch();


            $wfile      = new \Wing\Doc\WFile($file);
            $classes    = $wfile->getClasses();
            $file_name  = md5($file);


            $class_html = '<div data-file="'.$file.'" class="class_tap '.md5($file).'">';
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
<img src="'.$this->docpng.'"><div class="class-doc">'.$class->getDocFormat().'</div></div>';

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

                    $class_html .= '<div class="class-func"><label class="index-count">'.($index+1)."、</label>".$access.$static."function ".$function->getFunctionName().'</div>';
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

                    $return      = $function->getDocReturn();
                    $return      = str_replace("\n","<br/>",$return);
                    $class_html .= '<div class="return p22">返回值：'.$return.'</div>';

                    //如果配置了多个url 则返回的是数组
                    $url = $func_doc->url;
                    if( $url ){
                        if( !is_array( $url ))
                            $url = [$url];
                        foreach ( $url as $_url)
                        {
                            $class_html .= '<div class="visit-url p22"><label class="http-tip">url：</label><label class="url">'.$_url.'</label></div>';
                        }
                    }

                    $requests = $func_doc->request;
                    if( $requests && !is_array( $requests ))
                        $requests = [ $requests ];

                    if( $requests ) {
                        foreach ($requests as $request) {
                            $class_html .= '<div class="visit-url p22"><label class="http-tip">request：</label><label class="url">' . $request . '</label></div>';

                        }
                    }

                }
                unset($functions,$class_name);
            }
            $class_html .= '</div>';


            if( $file_count == 0 ){
                $file_content = str_replace('{$class_html}',$class_html,$html);
                file_put_contents($this->out_dir."/index.html",$file_content);
            }

            $file_content = str_replace('{$class_html}',$class_html,$html);
            //file_put_contents($this->out_dir."/$file_name.html",$file_content);
            $target_file->write($file_content);
            $file_count++;

            unset( $class_html, $wfile, $classes );

        }


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
                $html .= '<li class="is-dir h bg"><img src="'.$this->dpng.'"/><span>'.$dir.'</span></li>';
                $html .= '<li class="is-dir">'.$this->htmlFormat( $data ).'</li>';
            }
            else
            {
                list($name,$file) = explode("|",$data);

                $target_link = strtolower(str_replace($this->input_dir,"",$file)).".html";
                $target_link = ltrim($target_link,"/");

                $link = md5($file);
                $html .= '<li class="is-file h li-'.$link.'" data-tab="'.md5($file).'" data-file="'.$file.'">
                <span><a href="'.$target_link.'#'.$link.'">'.$name.'</a></span>
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