<?php

namespace TetraFileManager;

use Magento;

class SftpServer
{
    public $curl ;

    public $host = "http://localhost:8080/sftp";

    public function __construct(){

        $this->curl = new Magento\Framework\HTTP\Client\Curl();

    }

    function query($q){

        return \_return($this->host.$q);
    }
    
    public function command($method,array $command, array $data = array()){
    
        $command = json_encode($command);
        $data = json_encode($data);
        return $this->query("?method={$method}&command={$command}&data={$data}");
    
    }

    public function runGetCommand($request){

        $this->curl->get($request);

        return $this->curl->getBody();

    }

}