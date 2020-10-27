<?php

require_once("../vendor/autoload.php");

use FileManager as FileManagers;

use Magento\Framework\ObjectManager;

use Magento\Framework\App\ObjectManager as AppObjectManager;

use Magento\Framework\HTTP\PhpEnvironment;

use Magento\Framework\Stdlib;

$stu = new Stdlib\StringUtils;

$fileManagerValidation = new FileManagers\FileManagerValidation();

$fileManager = new FileManagers\FileManager(array(
	"host"=>"35.224.14.243",
    "username"=>"tom88",
    "password"=>"aaaa4444"
),$fileManagerValidation);

$path = "/disk";

$path2 = "/disk/haooo";


$items = array(

    "/disk/haooo",
);

$nname = //null;
 "R4";

// var_dump($fileManager->remove($items));

var_dump($fileManager->list($path));