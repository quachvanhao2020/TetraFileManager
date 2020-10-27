<?php


namespace TetraFileManager;

use Yes;

use Magento;
use Zend;
use ReactComponent;
use ReactComponent\SplitFileHepler;

use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ObjectManager;
use Zend\Http\Headers;

class FileManagerSftp extends SftpServer implements FileManagerCommandInterface
{

	public $auth;
	private $validation;
    public function __construct(AuthConnect $auth,FileManagerValidation $validation = null) {
		$this->auth = $auth;
		$this->validation = $validation;
		parent::__construct();
	}

	public function connect(){

		try {
			$cn = $this->curlGet("connect",array());
		} catch (\Exception $ex) {	
			return false;
		}
		$cn = $this->curlGet("invoke",array(
			"function"=>"pwd",
			"params"=>""
		));
		$this->handleError();
	}
	
	public function handleError(){
		$result = $this->curlGet("invoke",array(
			"function"=>"getSFTPErrors",
			"params"=>""
		));
		if(is_array($result->result) && \count($result->result)>0){
			$this->reconnect();	
		}
	}

	public function reconnect(){
		$result = $this->curlGet("refresh",array(
			"function"=>"reconnect",
			"params"=>""
		));
		return $result->result;
	}

	public function disConnect(){
		$result = $this->curlGet("remove",array(
			"function"=>"remove",
			"params"=>""
		));
		return $result->result;
	}

    function rawlsTo($data){
        if($data["filename"]=="." || $data["filename"]=="..") return null;
        return array(
            "date"=>date("Y-m-d H:i:s", $data["atime"]),
            "name"=>$data["filename"],
            "rights"=>755//$data["permissions"]
            ,
            "user"=>$data["uid"],
            "size"=>$data["size"],
            "type"=>$data["type"] == 1 ? "file" : "dir",
    
        );
    }

    public function curlGet(string $command,array $data){
		$command = $this->command($command,$data,$this->auth->toArray());
		$this->curl->get($command);
		$result = new Yes\RFFDataObject($this->curl->getBody());
        return $result;
	}
	
	public function curlPost(string $command,array $data,$content){
        $this->curl->post($this->command($command,$data,$this->auth),array("content"=>$content));
        return $this->curl->getBody();
	}
	

	public function curlPostFile(string $command,array $data,$source){
        $this->curl->postFile($this->command($command,$data,$this->auth),"file",$source);
        return $this->curl->getBody();
	}

	public function clearDirectory($list){
        $split = new ReactComponent\SplitFileHepler($list);
        $commandServer = CommandSshServer::getInstance($this->auth);
        foreach ($split->group as $key => $value) {
			$key = $commandServer->makePathUser($key); 
            $_result = $commandServer->runGetCommand($commandServer->command(
                "test",array(
                    "function"=>"catCommand",
                    "params"=>array(
                        "regex"=>$key."*",
                        "filename"=>$key
                    )
                )
    
			));	
			$result = json_decode($_result,true);
			if(!$result["result"]) return false;
			$_result = $commandServer->runGetCommand($commandServer->command(
                "test",array(
                    "function"=>"deleteCommandLine",
                    "params"=>array(
                        "source"=>SplitFileHepler::getFlagPart($key)."*",
                    )
                )
    
			));
			$result = json_decode($_result,true);
			if(!$result["result"]) return false;
		}	
		return true;
	}
	
	public function edit(&$item,&$content){
		if(!$this->validation->edit($item,$content)) return;
		$base64 = base64_encode($content);
		$params = array(
			"filename"=>$item,
			"source"=>"{content}"
		);
		$result = $this->curlPost("invoke",array(
			"function"=>"writebase64",
			"params"=>$params,
		),$base64);	        
        return $result->result;
	}

	public function copy(&$items,&$path,&$singleFilename = null){
		if(!$this->validation->copy($items,$path,$singleFilename)) return false;
		$commandServer = CommandSshServer::getInstance($this->auth);
		$path = $commandServer->makePathUser($path);
		if($singleFilename!=null){
			$paths = \explode("/",$path);
			//\array_pop($paths);
			\array_push($paths,$singleFilename);
			$path = \implode("/",$paths);
		};
		foreach ($items as $key => $value) {
			$value = $commandServer->makePathUser($value);
			$_result = $commandServer->runGetCommand($commandServer->command(
                "test",array(
                    "function"=>"copyCommandLine",
                    "params"=>array(
                        "source"=>$value,
                        "destination"=>$path
                    )
                )
    
			));	
			$result = json_decode($_result,true);
			if(!$result["result"]) return false;	
		}
		return true;
    }

    public function list(&$path){
		if(!$this->validation->list($path)) return;  
        $this->curlGet("invoke",array(
			"function"=>"cd",
			"params"=>$path,
		));
		$result = $this->curlGet("invoke",array(
			"function"=>"ls",
			"params"=>"",
		));
		if(!$this->clearDirectory($result->result)) return false;
		$result = $this->curlGet("invoke",array(
			"function"=>"rawls",
			"params"=>"",
		));
		if(!\is_array($result->result)) return;
		$res = array();
		foreach ($result->result as $key => $value) {
			$value = $this->rawlsTo($value);
			if($value !=null) array_push($res,$value);
		}
		return $res;
    }

    public function upload(&$item,&$content){
		if(!$this->validation->upload($item,$content)) return;
		$base64 = base64_encode($content);
		$params = array(
			"filename"=>$item,
			"source"=>"{content}"
		);
		$result = $this->curlPost("uploadLarger",array(
			"function"=>"writebase64",
			"params"=>$params,
		),$base64);
        return $result;
	}
	
	public function uploadLarger(&$item,&$content){
		if(!$this->validation->upload($item,$content)) return;
		$params = array(
			"source"=>$item,
			"fileId"=>"file"
		);
		$result = $this->curlPostFile("invoke",array(
			"function"=>"uploadLarger",
			"params"=>$params,
		),$content);       
        return $result;
    }

    public function move(&$items,&$path){
		if(!$this->validation->move($items,$path)) return false;
		$commandServer = CommandSshServer::getInstance($this->auth);
		$path = $commandServer->makePathUser($path);
		foreach ($items as $key => $value) {
			$value = $commandServer->makePathUser($value);
			$_result = $commandServer->runGetCommand($commandServer->command(
                "test",array(
                    "function"=>"copyCommandLine",
                    "params"=>array(
                        "source"=>$value,
                        "destination"=>$path
                    )
                )
    
			));
			$result = json_decode($_result,true);
			if(!$result["result"]) return false;
			$_result = $commandServer->runGetCommand($commandServer->command(
                "test",array(
                    "function"=>"deleteCommandLine",
                    "params"=>array(
                        "source"=>$value,
                    )
                )
    
			));
			$result = json_decode($_result,true);
			if(!$result["result"]) return false;
		}
		return true;
    }

    public function remove(&$items){
		if(!$this->validation->remove($items)) return;
		foreach ($items as $key => $value) {
			$_result = $this->curlGet("invoke",array(
				"function"=>"rm",
				"params"=>array(
					"filename"=>$value,
				),
			));
			$result = json_decode($_result,true);
			if(!$result["result"]) return false;	
		}
		return true;
    }

    public function rename(&$item,&$newPath){
		if(!$this->validation->rename($item,$newPath)) return;
		$result = $this->curlGet("invoke",array(
			"function"=>"mv",
			"params"=>array(
				"curent"=>$item,
				"new"=>$newPath

			),

        ));
        return $result;
    }

    public function mkdir(&$newPath){
		if(!$this->validation->mkdir($newPath)) return;
		$dirList = explode('/', $newPath);
		$result = $this->curlGet("invoke",array(
			"function"=>"mkdirs",
			"params"=>array(

				"dir"=>//$path,//
				"/".end($dirList),
				"mode"=>0775

			),

        ));
        return $result;
    }

    public function createFile(&$filename){
		if(!$this->validation->mkdir($filename)) return;
		$result = $this->curlGet("invoke",array(
			"function"=>"write",
			"params"=>array(

				"filename"=>$filename,
				"source"=>"Empty"

			),

		));
        return $result->result;
    }

    public function appinfo(){
        return array(
			"appName"=>"u911354432.wcetg.com"
		);
    }

    public function download(&$path){
		if(!$this->validation->download($path)) return;
		$_result = $this->curlGet("invoke",array(
			"function"=>"read",
			"params"=>array("filename"=>$path),
		));
		return $_result;
    }

    public function get(&$item){
		if(!$this->validation->get($item)) return;
		$result = $this->curlGet("invoke",array(
			"function"=>"getbase64",
			"params"=>array("filename"=>$item),
		));
		return $result->result;
    }

}