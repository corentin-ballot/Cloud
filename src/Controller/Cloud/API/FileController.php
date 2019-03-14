<?php

namespace App\Controller\Cloud\API;

use App\Services\Cloud\FileManager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\Routing\Annotation\Route;

class FileController
{
    /**
     * @Route("/file", name="get_file", methods={"GET"})
     * 
     * Get file data in request attachement.
     * @param url <PATH> The file relative url in the server.
     */
    public function GET_file(FileManager $fm)
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

        if(!$fm->file_exists($path)) {
            return self::JSONResponse(404, '<code>' . $path . '</code> was not found in the server.');
        }

        $response = new BinaryFileResponse($fm->file_path($path));

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            basename($fm->file_path($path))
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/file", name="post_file", methods={"POST"})
     * 
     * Create an empty file in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     * @param file <FILE> (Optional) The new file to be uploaded in the server.
     */
    public function POST_file(FileManager $fm)
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
        $file = $request->files->get('file');

        if(!empty($file)) {
            // move uploaded file
            $fm->move_uploded_file($file, $path);
            return self::JSONResponse(202, '<code>' . $file . '</code> was successfully uploaded in the server.');
        } else {
            // create empty file
            $fm->create_file($path);
            return self::JSONResponse(201, '<code>' . $path . '</code> was successfully created in the server.');
        }
    }

    /**
     * @Route("/file", name="put_file", methods={"PUT"})
     * 
     * Update file path/content (depending on passed params) in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     * @param newpath <PATH> (Optional) The new file path in the server.
     * @param content <STRING> (Optional) The new file content.
     */
    public function PUT_file(FileManager $fm) {
        $request = Request::createFromGlobals();

        // Retrieve path param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide relative file path.');
        }

        // Retrieve newpath param
        if (!empty(($request->request->get('newurl')))) { 
            $newpath = $request->request->get('newurl');  // body passed params
        } else if (!empty($request->query->get('newurl'))) {
            $newpath = $request->query->get('newurl');    // url passed params
        }

        // Rename file
        if(!empty($newpath)) {
            if($fm->rename($path, $newpath))
                return self::JSONResponse(202, '<code>' . $path . '</code> was successfully renamed as <code>' . $newpath . '</code>.');
        }

        // Retrieve content param
        if (!empty(($request->request->get('content')))) { 
            $content = $request->request->get('content');  // body passed params
        } else if (!empty($request->query->get('content'))) {
            $content = $request->query->get('content');    // url passed params
        }

        // Update file content
        if(!empty($content)) {
            if($fm->update_file($path, $content))
                return self::JSONResponse(202, 'Content of <code>' . $path . '</code> was successfully updated.');
        }

        return self::JSONResponse(500, 'An error occured will trying to update file.');
    }

    /**
     * @Route("/file", name="delete_file", methods={"DELETE"})
     * 
     * Delete file in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     */
    public function DELETE_file(FileManager $fm) {
        $request = Request::createFromGlobals();

        // Retrieve path param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return self::JSONResponse(400, 'You must provide relative file path.');
        }

        // Delete file
        if($fm->delete_file($path))
            return self::JSONResponse(200, '<code>' . $path . '</code> was successfully deleted.');
        else
            return self::JSONResponse(500, 'An error occured will trying to delete file.');
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