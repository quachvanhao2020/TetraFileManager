<?php

namespace TetraFileManager;

use Magento;

class CommandSshServer extends SftpServer
{
    private static $instance = null;

    public $host = "http://localhost:8080/ssh";

    public $auth = array(

        "host"=>"35.224.14.243",
        "username"=>"jerry",
        "password"=>"linuxpassword"

    );

    public $sftpAuth ;

    public static function getInstance($auth)
    {
      if (self::$instance == null)
      {
        self::$instance = new CommandSshServer($auth);
      }
   
      return self::$instance;
    }

    public function getRootSftpUser(){

      return "/home/".($this->sftpAuth["username"])."/";

    }

    public function makePathUser($path){

      return $this->getRootSftpUser().$path;

    }

    public function __construct($auth){

        $this->sftpAuth = $auth;

        parent::__construct();

    }

    public function command($method,array $command,array $data = null){

        return parent::command($method, $command,$this->auth);

    }

}