<?php


namespace TetraFileManager\Ajax;

use Magento;
use Zend;
use FileManager\Transfer\Adapter;
use ReactComponent;
use ReactComponent\SplitFileHepler;

use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\App\ObjectManager;
use Zend\Http\Headers;

class Handler
{
    private $fileManager;

    private $http;

    private $response;

    private $request;

    public function __construct(
        \FileManager\FileManager $fileManager,
        Response $response,
        Request $request = null
    ) {
        $this->response = $response;
        //$objectManager = ObjectManager::getInstance();
        $this->request = $request;// ?: $objectManager->get(Request::class);
        $this->http = new Adapter\Http($response,$request,\FileManager\Instance::$mimeTypes);
        $this->fileManager = $fileManager;

    }

    public function exit(){

        $result = array();

        $file = false;

        $content = $this->request->getContent();

        $request = json_decode($content);

        $gaction= $this->request->getParam("action");

        if($this->request->getParam("request")!==null){

            $request = json_decode($this->request->getParam("request"));

        }

        switch($gaction){
	
            case "list":

                $result = $this->fileManager->list($request->path);
        
            break;

            case "edit":

                $result = $this->fileManager->edit($request->item,$request->content);
        
            break;
        
            case "upload":

                $upload = new ReactComponent\Uploader("file-0");

                $local = $this->request->getParam("destination")."/".$upload->getFileName();

                $local = SplitFileHepler::getPartString($local,(int)$this->request->getParam("_chunkNumber"));

               // $result = $this->fileManager->upload($local."/".$upload->getFileName(),$upload->getContent());

                $source = $upload->getFileResource();

                $result = $this->fileManager->uploadLarger($local,$source);
               
            break;
        
            case "move":

                $result = $this->fileManager->move($request->items,$request->newPath);
        
            break;
        
            case "remove":

                $result = $this->fileManager->remove($request->items);
 
            break;
        
            case "rename":

                $result = $this->fileManager->rename($request->item,$request->newItemPath);

            break;
        
            case "mkdir":

                $result = $this->fileManager->mkdir($request->newPath);
   
            break;
        
            case "createFile":

                $result = $this->fileManager->createFile($request->newPath);

            break;
              
            case "appinfo":

                $result = $this->fileManager->appinfo();
      
            break;
        
            case 'download?action=download':

                $path= $this->request->getParam("path");

                $result = $this->fileManager->download($path);

                $file = true;
    
            break;
            
            case "get":

                $path = $request->item;

                $result = $this->fileManager->get($path);

            break;
             
        }

        if(!$file){

            $this->response->setBody(\json_encode(array(

                "result"=>$result
        
            )));

            return $this->http->send();

        }else{

            $this->response->setBody($result);

            return $this->http->sendFile($path);

        }
        
        return $this->http->send();

    }

}