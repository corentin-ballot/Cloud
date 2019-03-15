<?php

namespace App\Controller\Cloud\API;

use App\Services\Cloud\FileManager;
use App\Services\Cloud\Notifications;

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
    public function GET_file(FileManager $fm, Notifications $notifications)
    {
        $request = Request::createFromGlobals();

        // Retrieve params
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        }

        if(!$fm->file_exists($path)) {
            return $notifications->JSONResponse(404, '<code>' . $path . '</code> was not found in the server.');
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
    public function POST_file(FileManager $fm, Notifications $notifications)
    {
        $request = Request::createFromGlobals();

        // Retrieve params
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        }
        $file = $request->files->get('file');

        if(!empty($file)) {
            // move uploaded file
            $fm->move_uploded_file($file, $path);
            return $notifications->JSONResponse(202, '<code>' . $file . '</code> was successfully uploaded in the server.');
        } else {
            // create empty file
            $fm->create_file($path);
            return $notifications->JSONResponse(201, '<code>' . $path . '</code> was successfully created in the server.');
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
    public function PUT_file(FileManager $fm, Notifications $notifications) {
        $request = Request::createFromGlobals();

        // Retrieve path param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
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
                return $notifications->JSONResponse(202, '<code>' . $path . '</code> was successfully renamed as <code>' . $newpath . '</code>.');
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
                return $notifications->JSONResponse(202, 'Content of <code>' . $path . '</code> was successfully updated.');
        }

        return $notifications->JSONResponse(500, false, 'An error occured will trying to update file.');
    }

    /**
     * @Route("/file", name="delete_file", methods={"DELETE"})
     * 
     * Delete file in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     */
    public function DELETE_file(FileManager $fm, Notifications $notifications) {
        $request = Request::createFromGlobals();

        // Retrieve path param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        }

        // Delete file
        if($fm->delete_file($path))
            return $notifications->JSONResponse(200, false, '<code>' . $path . '</code> was successfully deleted.');
        else
            return $notifications->JSONResponse(500, false, 'An error occured will trying to delete file.');
    }
}