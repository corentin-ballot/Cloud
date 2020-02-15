<?php

namespace App\Controller\API;

use App\Services\FileManager;
use App\Services\Notifications;

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
        $data = json_decode($request->getContent(), true); // json body passed params
        if(isset($data['url'])) $path = $data['url']; else return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        if(isset($data['file_list'])) $newpath = $data['files']; else return $notifications->JSONResponse(400, false, 'You must provide a file list.');
        
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
        $data = json_decode($request->getContent(), true); // json body passed params
        if(isset($data['url'])) $path = $data['url']; else return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
                
        $unziped_path = $fm->unzip_file($path);

        if($unziped_path) {
            return $notifications->JSONResponse(200, false, '<code>' . $path . '</code> was successfully unzipped in <code>' . $unziped_path . '</code>.');
        }

        return $notifications->JSONResponse(500, false, 'Failed to unzip <code>' . $path . '</code>.');
    }
}