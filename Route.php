<?php namespace Wing\Doc;
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/25
 * Time: 10:22
 *
 * @如果需要生成可以直接调试的api文档，必须实现此接口
 * @根据参数返回完整的url
 */
interface Route{
    /**
     * @return string 返回完整的url
     */
    public function getUrl($file,$namespace,$class_name,$function_name);
}