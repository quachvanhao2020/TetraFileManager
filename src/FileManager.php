<?php
namespace TetraFileManager;
use Explorer\Folder;
use Explorer\File;
use Explorer\FileUpload;
use Explorer\Meta\HostMeta;
use Explorer\File\FileMultiple;
use TetraFileManager\Entity\Result\FMUnit;
use YPHP\SERVER;

class FileManager extends Folder implements FileManagerInterface{

    const ROOT = "ROOT";
    const MAX_SIZE_UPLOAD_FILE = 10*1024*1024;

    public $currentFolder;

    public function __construct($id){
        parent::__construct($id);
        $this->getInfo();
        $this->currentFolder = $this;
    }

    public function cd($id){
        $folder = $this->getFolder($id);
        if($folder = alive($folder)){
            $this->currentFolder = $folder;
            return true;
        }else{
        }
        return false;
    }

    public function newId($id){
        return $this->getId().DIRECTORY_SEPARATOR.$id;
    }

    public function getFile($id) : File {
        return new File($this->newId($id));
    }

    public function getFolder($id) : Folder {
        return new Folder($this->newId($id));
    }

    public function list($path = ""){
        $fMUnits = [];
        if(!empty($path)){
            $this->cd($path);
        }
        foreach ($this->currentFolder->getChilds() as $key => $value) {
            $enity = $value->getInfo();
            if($fmu = \convert($enity,FMUnit::class)){
                \array_push($fMUnits,$fmu);
            }
        }
        return $fMUnits;
    }

    public function copy($items,$newPath,$singleFilename = null){
        $folder = $this->getFolder($newPath);
        if(!\alive($folder)) return;
        foreach ($items as $key => $value) {
            $file = $this->getFile($value);
            if(\alive($file)){
                $file->getName();
                if($singleFilename) $file->setName($singleFilename);
                return $file->copyTo($folder);
            }
            $nfolder = $this->getFolder($value);
            if(\alive($nfolder)){
                return $nfolder->copyTo($folder);
            }
        }
        return;
    }

    public function edit($fileName,$content){
        $file = new File($this->newId($fileName));
        if($file = alive($file)){
            $file->setContent($content);
            \save($file);
            return $file;
        };
        return;
    }

    protected function _upload($id = null,$des = "",$hostMultiple = null){
        $files = SERVER::FILES_UPLOAD();
        $folder = $this->getFolder($des);
        if($folder = \alive($folder,true)){
            foreach ($files as $key => $value) {
                $fileUpload = FileUpload::arrayTo($value);
                if(\alive($fileUpload) && $fileUpload->getSize() < self::MAX_SIZE_UPLOAD_FILE){
                    if($hostMultiple){
                        $fileUpload->toHidden();
                        $name = $fileUpload->getName();
                        $fileUpload->setName($fileUpload->getName().$hostMultiple->getPart());
                        $fileUpload->setDirname($folder);
                        \save($fileUpload);
                        $fileMultiple = new FileMultiple($folder->getId()."/".$name,$hostMultiple);
                        if($fileMultiple = alive($fileMultiple,true)){
                            $fileMultiple->getInfo();
                            $fileMultiple->toHidden();
                            if($fileMultiple->canJoin()){
                                $file = $fileMultiple->join(true);
                                $file->toShow();
                                save($fileMultiple);
                                destroy($fileMultiple);
                            }else{
                                save($fileMultiple);
                            }
                        }      
                    }else{

                    }
                }else return false;
            }
        }

        return true;
    }

    public function upload($id = null,$des = "",$options = []){
        if(isset($options["host"]) && $host = $options["host"]){
            if(is_a($host,HostMeta::class)){
                if($host->canMultipleFile()){
                    return $this->_upload($id,$des,$host);
                };
            }

        }else{
        }
        return $this->_upload($id,$des);
        return true;
    }

    public function move($items,$newPath){
        $folder = $this->getFolder($newPath);
        if(!\alive($folder)) return;
        foreach ($items as $key => $value) {
            $unit = $this->getFile($value);
            if(!\alive($unit)){
                $unit = $this->getFolder($value);
            }
            if(\alive($unit)){
                $unit->getName();
                $unit->setDirname($folder);
                return \save($unit);
            }
        }
        return;
    }

    public function remove($items){
        foreach ($items as $key => $value) {
            $unit = $this->getFile($value);
            if(!\alive($unit)){
                $unit = $this->getFolder($value);
            }
            if($unit = \alive($unit)){
                \destroy($unit);
            }
        }
        return true;
    }

    public function rename($fileName,$newName){
        $file = $this->getFile($fileName);
        $nfile = $this->getFile($newName);
        if($file = \alive($file)){
            $file->setName($nfile->getName());
            $file->getDirname();
            \save($file);
            return $file;
        }
        $folder = $this->getFolder($fileName);
        $nfolder = $this->getFolder($newName);
        if($folder = \alive($folder)){
            $folder->setName($nfolder->getName());
            $folder->getDirname();
            \save($folder);
            return $folder;
        }
    }

    public function mkdir($newPath){
        $unit = $this->getFolder($newPath);
        return \alive($unit,true);
    }

    public function createFile($fileName){
        $file = new File($this->newId($fileName));
        return alive($file,true);
    }

    public function appinfo(){
        return;
    }

    public function download($fileName){
        $file = new File($this->newId($fileName));
        return alive($file);
    }

    public function get($fileName){
        $file = $this->getFile($fileName);
        if($file = alive($file)){
            return $file->getContent();
        };
        return;
    }

}