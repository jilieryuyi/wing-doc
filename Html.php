<?php namespace Wing\Doc;
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 17/3/4
 * Time: 19:26
 */
class Html
{
    private $tag_name;
    private $attr;

    public $html;

    public function __construct($tag_name)
    {
        $this->tag_name = $tag_name;
        $this->html     = "";
    }

    private function isClose()
    {
        if (in_array(strtolower($this->tag_name),[
            "img","br","hr","input","link"
        ]))
            return false;
        return true;
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
        $this->attr[$name] = $value;
    }
    public function __get($name)
    {
        // TODO: Implement __get() method.
        if (!isset($this->attr[$name]))
            return "";
        return $this->attr[$name];
    }

    public function getTtml()
    {
        $html = "<".$this->tag_name;
        foreach ($this->attr as $attr => $value) {
            $html .= " ".$attr."=\"".$value."\" ";
        }
        if (!$this->isClose())
            $html.="/>";
        else {
            $html.=">";
            $html.=$this->html;
            $html.="</".$this->tag_name.">";
        }
        return $html;
    }

    public function append($content)
    {
        if ($content instanceof self)
            $this->html .= $content->getTtml();
        else
            $this->html .= $content;
    }
}