<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Imagick;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $path =  $request->pageName;

        $uploadDirectory = public_path(env('UPLOADS_DIRECTORY') . '/' . $path);
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }

        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $file = $request->file('file');
        $fileName =  $file->getClientOriginalName();
        $originalPath = $uploadDirectory . '/' . $fileName;
        $fileExtension = $request->file('file')->getClientOriginalExtension();

        if (file_exists($originalPath)) {
            // O arquivo já existe
            if ($fileExtension === 'gif' || $fileExtension === 'svg') {
                $urlOriginal = url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $fileName);
                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo já existe.',
                    'url' => $urlOriginal,
                ]);
            } else {
                $urlOriginal = url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $fileName);

                $webpFileName800 = pathinfo($fileName, PATHINFO_FILENAME) . '-800.webp';
                $webpFileName480 = pathinfo($fileName, PATHINFO_FILENAME) . '-480.webp';
                $webpFileName320 = pathinfo($fileName, PATHINFO_FILENAME) . '-320.webp';

                $urlWebp800 = file_exists(public_path(env('UPLOADS_DIRECTORY')) . '/' . $webpFileName800) ?
                    url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $webpFileName800) : null;

                $urlWebp480 = file_exists(public_path(env('UPLOADS_DIRECTORY')) . '/' . $webpFileName480) ?
                    url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $webpFileName480) : null;

                $urlWebp320 = file_exists(public_path(env('UPLOADS_DIRECTORY')) . '/' . $webpFileName320) ?
                    url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $webpFileName320) : null;

                if ($urlWebp800 && $urlWebp480 && $urlWebp320) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Arquivo já existe.',
                        'url' => $urlOriginal,
                        'srcset' => [
                            '800w' => $urlWebp800,
                            '480w' => $urlWebp480,
                            '320w' => $urlWebp320,
                        ],
                    ]);
                } else {
                    $webpFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
                    $webpPath = public_path(env('UPLOADS_DIRECTORY')) . '/' . $webpFileName;
                    $urlWebp = url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $webpFileName);

                    return response()->json([
                        'success' => true,
                        'message' => 'Arquivo já existe.',
                        'url' => $urlOriginal,
                        'srcset' => $urlWebp,
                    ]);
                }
            }
        } else {
            $file->move(public_path(env('UPLOADS_DIRECTORY') . '/' . $path), $fileName);

            if ($fileExtension !== 'gif' && $fileExtension !== 'svg') {
                $imageSize = getimagesize($originalPath);
                log::info($imageSize[0]);
                $imageWidth = $imageSize[0];


                if ($imageWidth < 300) {
                    $webpFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
                    $webpPath = public_path(env('UPLOADS_DIRECTORY')) . '/' . $path . '/' . $webpFileName;
                    $imagick = new Imagick($originalPath);
                    $imagick->setImageFormat('webp');
                    $imagick->writeImage($webpPath);
                    $imagick->clear();
                    $imagick->destroy();

                    $urlWebp = url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $webpFileName);


                } else {
                    $webpFileName320 = pathinfo($fileName, PATHINFO_FILENAME) . '-320.webp';
                    $webpFileName480 = pathinfo($fileName, PATHINFO_FILENAME) . '-480.webp';
                    $webpFileName800 = pathinfo($fileName, PATHINFO_FILENAME) . '-800.webp';

                    $webpPath320 = public_path(env('UPLOADS_DIRECTORY')) . '/' . $path . '/' .$webpFileName320;
                    $webpPath480 = public_path(env('UPLOADS_DIRECTORY')) . '/' . $path . '/' .$webpFileName480;
                    $webpPath800 = public_path(env('UPLOADS_DIRECTORY')) . '/' . $path . '/' .$webpFileName800;

                    $imagick = new Imagick($originalPath);


                    $imagick->scaleImage(320, 0);
                    $imagick->setImageFormat('webp');
                    $imagick->writeImage($webpPath320);


                    $imagick->clear();
                    $imagick->readImage($originalPath);
                    $imagick->scaleImage(480, 0);
                    $imagick->setImageFormat('webp');
                    $imagick->writeImage($webpPath480);

                    // Redimensionar para 800px
                    $imagick->clear();
                    $imagick->readImage($originalPath);
                    $imagick->scaleImage(800, 0);
                    $imagick->setImageFormat('webp');
                    $imagick->writeImage($webpPath800);

                    $imagick->clear();
                    $imagick->destroy();

                    $urlWebp320 = url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $webpFileName320);
                    $urlWebp480 = url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $webpFileName480);
                    $urlWebp800 = url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $webpFileName800);
                }
            }

            $urlOriginal = url(env('UPLOADS_DIRECTORY') . '/' . $path . '/' . $fileName);
            if ($fileExtension === 'gif' || $fileExtension === 'svg') {
                return response()->json([
                    'success' => true,
                    'message' => 'Upload bem-sucedido.',
                    'url' => $urlOriginal,
                    'srcset' => null,
                ]);
            }if($imageWidth < 300) {
                return response()->json([
                    'success' => true,
                    'message' => 'Upload bem-sucedido.',
                    'url' => $urlOriginal,
                    'srcset' => $urlWebp,
                ]);
            } else {
                $response = response()->json([
                    'success' => true,
                    'message' => 'Upload bem-sucedido.',
                    'url' => $urlOriginal,
                    'srcset' => [
                        '320w' => $urlWebp320,
                        '480w' => $urlWebp480,
                        '800w' => $urlWebp800,
                    ],
                ]);

                log::info($response);
                return $response;
            }
        }
    }
}
