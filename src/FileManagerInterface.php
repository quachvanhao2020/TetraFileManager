<?php
namespace TetraFileManager;

interface FileManagerInterface 
{

    /**
     * @return mixed
     */
    function list($path = "");
        /**
     * @return mixed
     */
    function copy($items,$newPath,$singleFilename = null);
    /**
     * @return mixed
     */
    function edit($item,$content);
    /**
     * @return mixed
     */
    function upload($item);
    /**
     * @return mixed
     */
    function move($items,$path);
    /**
     * @return mixed
     */
    function remove($items);
    /**
     * @return mixed
     */
    function rename($item,$newPath);
    /**
     * @return mixed
     */
    function mkdir($newPath);
    /**
     * @return mixed
     */
    function createFile($fileName);
    /**
     * @return mixed
     */
    function appinfo();
    /**
     * @return mixed
     */
    function download($path);
    /**
     * @return mixed
     */
    function get($item);
}