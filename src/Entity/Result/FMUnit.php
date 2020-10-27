<?php
namespace TetraFileManager\Entity\Result;
use Explorer\File;
use Explorer\Folder;


class FMUnit{

    public const DIR = "dir";

    public const FILE = "file";

    public $date;

    public $name;

    public $rights;

    public $user;

    public $size;

    public $type;

    public static function ExplorerFile(File $file){

        $me = new self();

        $me->name = $file->getName();
        $me->size = $file->getSize();
        $me->type = self::FILE;
        $me->date = $file->getDate();

        return $me;
        
    }

    public static function ExplorerFolder(Folder $folder){

        $me = new self();

        $me->name = $folder->getName();
        $me->size = $folder->getSize();
        $me->type = self::DIR;
        $me->date = $folder->getDate();

        return $me;

    }

}