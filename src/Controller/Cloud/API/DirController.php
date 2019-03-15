<?php

namespace App\Controller\Cloud\API;

use App\Services\Cloud\FileManager;
use App\Services\Cloud\Notifications;

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
    public function GET_dir(FileManager $fm, Notifications $notifications)
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

        $data = $fm->scandir($path);

        if(!$data) 
            return $notifications->JSONResponse(404, 'Directory <code>' . $path . '</code> was not found in the server.');

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
    public function POST_dir(FileManager $fm, Notifications $notifications)
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

        // create empty file
        if(!$fm->create_dir($path))
            return $notifications->JSONResponse(500, false, 'An error occured will trying to create <code>' . $path . '</code>');
        return $notifications->JSONResponse(201, '<code>' . $path . '</code> was successfully created in the server.');

    }

    /**
     * @Route("/dir", name="put_dir", methods={"PUT"})
     * 
     * Update dir path in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     * @param newpath <PATH> The new file path in the server.
     */
    public function PUT_dir(FileManager $fm, Notifications $notifications) {
        $request = Request::createFromGlobals();

        // Retrieve path param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide relative directory path.');
        }

        // Retrieve newpath param
        if (!empty(($request->request->get('newurl')))) { 
            $newpath = $request->request->get('newurl');  // body passed params
        } else if (!empty($request->query->get('newurl'))) {
            $newpath = $request->query->get('newurl');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide new relative directory path.');
        }

        // Rename dir
        if(!empty($newpath)) {
            if($fm->rename($path, $newpath))
                return $notifications->JSONResponse(202, '<code>' . $path . '</code> was successfully renamed as <code>' . $newpath . '</code>.');
        }

        return $notifications->JSONResponse(500, false, 'An error occured will trying to rename directory.');
    }

    /**
     * @Route("/dir", name="delete_dir", methods={"DELETE"})
     * 
     * Delete file in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     */
    public function DELETE_dir(FileManager $fm, Notifications $notifications) {
        $request = Request::createFromGlobals();

        // Retrieve path param
        if (!empty(($request->request->get('url')))) { 
            $path = $request->request->get('url');  // body passed params
        } else if (!empty($request->query->get('url'))) {
            $path = $request->query->get('url');    // url passed params
        } else {
            return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        }

        // Delete dir
        if($fm->delete_dir($path))
            return $notifications->JSONResponse(200, false, '<code>' . $path . '</code> was successfully deleted.');
        else
            return $notifications->JSONResponse(500, false, 'An error occured will trying to delete <code>' . $path . '</code>.');
    }
}