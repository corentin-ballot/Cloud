<?php

namespace App\Controller\Cloud\API;

use App\Services\Cloud\FileManager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;

class DirController
{
    /**
     * @Route("/dir", name="get_dir", methods={"GET"})
     * 
     * Get directory data in request attachement.
     * @param url <PATH> The directory relative url in the server.
     */
    public function GET_dir(FileManager $fm)
    {
        $request = Request::createFromGlobals();

        // Retrieve params
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide relative file path.');
        }

        $data = $fm->scandir($path);

        if(!$data) 
            return self::JSONResponse(404, 'Directory <code>' . $path . '</code> was not found in the server.');

        $response = new Response();
        $response->setContent(json_encode($data, JSON_UNESCAPED_SLASHES));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/dir", name="post_dir", methods={"POST"})
     * 
     * Create an empty dir in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     */
    public function POST_dir(FileManager $fm)
    {
        $request = Request::createFromGlobals();

        // Retrieve params
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide relative file path.');
        }

        // create empty file
        if(!$fm->create_dir($path))
            return self::JSONResponse(500, 'An error occured will trying to create <code>' . $path . '</code>');
        return self::JSONResponse(201, '<code>' . $path . '</code> was successfully created in the server.');

    }

    /**
     * @Route("/dir", name="put_dir", methods={"PUT"})
     * 
     * Update dir path in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     * @param newpath <PATH> The new file path in the server.
     */
    public function PUT_dir(FileManager $fm) {
        $request = Request::createFromGlobals();

        // Retrieve path param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide relative directory path.');
        }

        // Retrieve newpath param
        if (!empty(($request->request->get('newurl')))) { 
            $newpath = $request->request->get('newurl');  // body passed params
        } else if (!empty($request->query->get('newurl'))) {
            $newpath = $request->query->get('newurl');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide new relative directory path.');
        }

        // Rename dir
        if(!empty($newpath)) {
            if($fm->rename($path, $newpath))
                return self::JSONResponse(202, '<code>' . $path . '</code> was successfully renamed as <code>' . $newpath . '</code>.');
        }

        return self::JSONResponse(500, 'An error occured will trying to rename directory.');
    }

    /**
     * @Route("/dir", name="delete_dir", methods={"DELETE"})
     * 
     * Delete file in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     */
    public function DELETE_dir(FileManager $fm) {
        $request = Request::createFromGlobals();

        // Retrieve path param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide relative file path.');
        }

        // Delete dir
        if($fm->delete_dir($path))
            return self::JSONResponse(200, '<code>' . $path . '</code> was successfully deleted.');
        else
            return self::JSONResponse(500, 'An error occured will trying to delete <code>' . $path . '</code>.');
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