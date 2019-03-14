<?php

namespace App\Controller\Cloud\API;

use App\Services\Cloud\FileManager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\Routing\Annotation\Route;

class ZipController
{
    /**
     * @Route("/zip", name="post_zip", methods={"POST"})
     * 
     * Get file data in request attachement.
     * @param url <PATH> The file relative url in the server.
     */
    public function POST_zip(FileManager $fm)
    {
        $request = Request::createFromGlobals();

        // Retrieve url param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide relative file path.');
        }

        // Retrieve file list param
        if (!empty(($request->request->get('files')))) { 
            $file_list = $request->request->get('files');  // body passed params
        } else if (!empty($request->query->get('files'))) {
            $file_list = $request->query->get('files');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide a file list.');
        }
        
        $zip_path = $fm->zip_files($path, json_decode($file_list));

        return self::JSONResponse(200, 'Files were successfully zipped in <code>' . $zip_path . '</code>.');
    }
    /**
     * @Route("/unzip", name="post_unzip", methods={"POST"})
     * 
     * Get file data in request attachement.
     * @param url <PATH> The file relative url in the server.
     */
    public function POST_unzip(FileManager $fm)
    {
        $request = Request::createFromGlobals();

        // Retrieve url param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide relative file path.');
        }
        
        $unziped_path = $fm->unzip_file($path);

        if($unziped_path)
            return self::JSONResponse(200, '<code>' . $path . '</code> was successfully unzipped in <code>' . $unziped_path . '</code>.');
        return self::JSONResponse(500, 'Failed to unzip <code>' . $path . '</code>.');
    }

    private static $http_errors = [100 => 'Continue', 101 => 'Switching Protocols', 200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Moved Temporarily', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Time-out', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Large', 415 => 'Unsupported Media Type', 500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Time-out', 505 => 'HTTP Version not supported'];
        
    private function JSONResponse($code, $msg) {
        $response = new Response();
        $response->setStatusCode($code);
        $response->setContent(json_encode([
            'msg'=> self::$http_errors[$code],
            'detail' => $msg
        ], JSON_UNESCAPED_SLASHES));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}