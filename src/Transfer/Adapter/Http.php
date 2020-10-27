<?php


namespace TetraFileManager\Transfer\Adapter;

use Magento;
use Zend;
use Mimey;

use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\App\ObjectManager;
use Zend\Http\Headers;

/**
 * File adapter to send the file to the client.
 */
class Http
{

    private $mimeType;

    private $response;

    private $request;

    public function __construct(
        Response $response,
        Request $request = null,
        Mimey\MimeTypesInterface $mimeType = null
    ) {
        $this->response = $response;
        //$objectManager = ObjectManager::getInstance();
        $this->request = $request;// ?: $objectManager->get(HttpRequest::class);
        $this->mimeType = $mimeType;
    }

    public function send($options = null)
    {

        $this->prepareResponse($options);

        if ($this->request->isHead()) {

            return;
        }

        return $this->response->sendResponse();

    }

    public function sendFile($filename,$options = null)
    {

        $this->prepareResponseFile($options,$filename);

        if ($this->request->isHead()) {

            return;
        }

        return $this->response->sendResponse();


    }

    private function prepareResponseFile($options, string $filepath): void
    {
        $mimeType = $this->mimeType->getMimeType($filepath);
        if (is_array($options) && isset($options['headers']) && $options['headers'] instanceof Headers) {
            $this->response->setHeaders($options['headers']);
        }
        //$this->response->setHeader('Content-length', filesize($filepath));
        $this->response->setHeader('Content-Type', $mimeType);

        $this->response->sendHeaders();
    }

    private function getFilePath($options): string
    {
        if (is_string($options)) {
            $filePath = $options;
        } elseif (is_array($options) && isset($options['filepath'])) {
            $filePath = $options['filepath'];
        } else {
            throw new \InvalidArgumentException("Filename is not set.");
        }

        return $filePath;
    }

    private function prepareResponse($options): void
    {

        if (is_array($options) && isset($options['headers']) && $options['headers'] instanceof Headers) {
            $this->response->setHeaders($options['headers']);
        }
        $this->response->setHeader('Content-length', '20');
        $this->response->setHeader('Content-Type', 'application/json');

        $this->response->sendHeaders();
    }

}