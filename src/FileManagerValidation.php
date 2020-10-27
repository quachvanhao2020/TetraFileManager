<?php
namespace TetraFileManager;

class FileManagerValidation implements FileManagerCommandInterface, Validation\ValidationInterface
{

    public $regex = '/^[(\/)]{1,}disk[(\/)]{1,}(Recent|Favorite|Netdisk:C)[(\/)]{0,}$/';
    function fillerPath($path,$pro = false){
        $newPath = str_replace("/public_html","/",$path);
        if($pro){
            return (!preg_match($this->regex,$newPath) ? $newPath : false );
        }
        return $newPath;
    }
 
    public function __construct() {

    }

    public function edit(&$item,&$content){
        return $item = $this->fillerPath($item);
    }

    public function copy(&$items,&$newPath,&$singleFilename = null){
        return $newPath = $this->fillerPath($newPath);
    }


    public function list(&$path){
        return $path = $this->fillerPath($path);
    }

    public function upload(&$item,&$content){
       return $item = $this->fillerPath($item);
    }

    public function move(&$items,&$path){
        foreach ($items as $key => $value) {
            $items[$key] = $this->fillerPath($value,true);
            if(!$items[$key]) return false;  
        }
       return $path = $this->fillerPath($path);
    }

    public function remove(&$items){
        foreach ($items as $key => $value) {
            $items[$key] = $this->fillerPath($value,true);
            if(!$items[$key]) return false;
        }
        return $items;
    }

    public function rename(&$item,&$newPath){
        $newPath = $this->fillerPath($newPath);
        return $item = $this->fillerPath($item,true);
    }

    public function mkdir(&$newPath){
       return $newPath = $this->fillerPath($newPath);
    }

    public function createFile(&$fileName){
       return $fileName = $this->fillerPath($fileName);
    }

    public function appinfo(){


    }

    public function download(&$path){
       return $path = $this->fillerPath($path);
    }

    public function get(&$item){
        return $item = $this->fillerPath($item);
    }

}