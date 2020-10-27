<?php

namespace TetraFileManager;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Formatter\LineFormatter;

class Instance{

    public static $NamespaceLogger = null;

    public static $mimeTypes = null;

    public function __construct(){

        if(self::$NamespaceLogger == null){

            $dateFormat = "Y n j, g:i a";
            $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
            $formatter = new LineFormatter($output, $dateFormat);
            $logger = new Logger('ReactBind');
            $logger->pushHandler(new FirePHPHandler());
    
            $stream = new StreamHandler(__DIR__.'/reactbind.log', Logger::DEBUG);
    
            $logger->pushHandler($stream);
    
            $logger->info("Instance reactbind.log");
    
            self::$NamespaceLogger = $logger;

        }

        if(self::$mimeTypes == null){

            self::$mimeTypes = new \Mimey\MimeTypes();

            Instance::$NamespaceLogger->info("Instance \Mimey\MimeTypes");

        }

    }

}