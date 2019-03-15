<?php

namespace App\Controller\Cloud\API;

use App\Services\Cloud\FileManager;
use App\Services\Cloud\Notifications;

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
    public function POST_zip(FileManager $fm, Notifications $notifications)
    {
        $request = Request::createFromGlobals();

        // Retrieve url param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        }

        // Retrieve file list param
        if (!empty(($request->request->get('files')))) { 
            $file_list = $request->request->get('files');  // body passed params
        } else if (!empty($request->query->get('files'))) {
            $file_list = $request->query->get('files');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide a file list.');
        }
        
        $zip_path = $fm->zip_files($path, json_decode($file_list));

        return $notifications->JSONResponse(200, false, 'Files were successfully zipped in <code>' . $zip_path . '</code>.');
    }
    /**
     * @Route("/unzip", name="post_unzip", methods={"POST"})
     * 
     * Get file data in request attachement.
     * @param url <PATH> The file relative url in the server.
     */
    public function POST_unzip(FileManager $fm, Notifications $notifications)
    {
        $request = Request::createFromGlobals();

        // Retrieve url param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        }
        
        $unziped_path = $fm->unzip_file($path);

        if($unziped_path)
            return $notifications->JSONResponse(200, false, '<code>' . $path . '</code> was successfully unzipped in <code>' . $unziped_path . '</code>.');
        return $notifications->JSONResponse(500, false, 'Failed to unzip <code>' . $path . '</code>.');
    }
}