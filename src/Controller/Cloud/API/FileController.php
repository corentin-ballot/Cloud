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
        // Get params
        $request = Request::createFromGlobals();
        $data = $request->query->all(); // url passed params
        if(isset($data['url'])) $path = $data['url']; else return $notifications->JSONResponse(400, false, 'You must provide relative file path.');

        // Check file exists
        if(!$fm->file_exists($path)) {
            return $notifications->JSONResponse(404, false, '<code>' . $path . '</code> was not found in the server.');
        }

        // Return file as binary
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
        // Get params
        $request = Request::createFromGlobals();
        $data = $request->request->all(); // form passed params
        if(isset($data['url'])) $path = $data['url']; else return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        $files = $request->files->all();

        if(!empty($files)) {
            // move uploaded file
            foreach ($files as $file){
                var_dump($file);
                $fm->move_uploded_file($file, $path);
            }
            return $notifications->JSONResponse(202, false, '<code>[' . implode(",", $files) . ']</code> were successfully uploaded in the server.');
        } else {
            // create empty file
            $fm->create_file($path);
            return $notifications->JSONResponse(201, false, '<code>' . $path . '</code> was successfully created in the server.');
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
        // Get params
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true); // json body passed params
        if(isset($data['url'])) $path = $data['url']; else return $notifications->JSONResponse(400, false, 'You must provide relative file path.');
        if(isset($data['newurl'])) $newpath = $data['newurl'];
        if(isset($data['content'])) $content = $data['content'];

        // Rename file
        if(isset($data['newurl'])) {
            if($fm->rename($path, $newpath)) {
                return $notifications->JSONResponse(202, false, '<code>' . $path . '</code> was successfully renamed as <code>' . $newpath . '</code>.');
            } else {
                return $notifications->JSONResponse(500, false, 'An error occured will trying to rename <code>' . $path . '</code> into <code>' . $newpath . '</code>.');
            }
        }

        // Update file content
        if(isset($data['content'])) {
            if($fm->update_file($path, $content)) {
                return $notifications->JSONResponse(202, false, 'Content of <code>' . $path . '</code> was successfully updated.');
            } else {
                return $notifications->JSONResponse(500, false, 'An error occured will trying to update file content.');
            }
        }

        return $notifications->JSONResponse(400, false, 'Please provide new file path (<code>newurl</code>) to move/rename it or new file content (<code>content</code>) to update it.');
    }

    /**
     * @Route("/file", name="delete_file", methods={"DELETE"})
     * 
     * Delete file in the server.
     * @param url <PATH> The new file relative url to be created in the server.
     */
    public function DELETE_file(FileManager $fm, Notifications $notifications) {
        // Get params
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true); // json body passed params
        if(isset($data['url'])) $path = $data['url']; else return $notifications->JSONResponse(400, false, 'You must provide relative file path.');

        // Delete file
        if($fm->delete_file($path)) {
            return $notifications->JSONResponse(200, false, '<code>' . $path . '</code> was successfully deleted.');
        }

        return $notifications->JSONResponse(500, false, 'An error occured will trying to delete file.');
    }
}