<?php

namespace TetraFileManager;

class AuthConnect
{

    public $host;

    public $username;

    public $password;

    public function __construct($host,$username,$password){

        $this->host = $host;
        $this->setAccount($username,$password);
        
    }

    public function setAccount($username,$password){

        $this->username = $username;
        $this->password = $password;

    }

    public function toArray(){

        return self::makeMeArray($this->host,$this->username,$this->password);
    }

    public static function makeMeArray($host,$username,$password){

        return [
            "host"=>$host,
            "username"=>$username,
            "password"=>$password,
        ];

    }

    public static function arrayTo($data){

        return self::makeMe($data["host"],$data["username"],$data["password"]);

    }

    public static function makeMe($host,$username,$password){

        return new self($host,$username,$password);

    }

}