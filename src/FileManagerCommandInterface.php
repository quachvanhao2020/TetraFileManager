<?php


namespace TetraFileManager;

use Magento;
use Zend;


interface FileManagerCommandInterface 
{

    public function list(&$path);

    public function copy(&$items,&$newPath,&$singleFilename = null);

    public function edit(&$item,&$content);

    public function upload(&$item,&$content);

    public function move(&$items,&$path);

    public function remove(&$items);

    public function rename(&$item,&$newPath);

    public function mkdir(&$newPath);

    public function createFile(&$fileName);

    public function appinfo();

    public function download(&$path);

    public function get(&$item);

}