<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Imagick;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $file = $request->file('file');
        $fileName =  $file->getClientOriginalName();
        $originalPath = public_path(env('UPLOADS_DIRECTORY')) . '/' . $fileName;
        $fileExtension = $request->file('file')->getClientOriginalExtension();

        if (file_exists($originalPath)) {
            // O arquivo já existe
            if ($fileExtension === 'gif') {
                // Se for GIF, retorna apenas o URL original
                $urlOriginal = url(env('UPLOADS_DIRECTORY') . '/' . $fileName);
                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo já existe.',
                    'url' => $urlOriginal,
                ]);
            } else {
                // Se não for GIF, retorna o URL original e o URL do WebP
                $urlOriginal = url(env('UPLOADS_DIRECTORY') . '/' . $fileName);
                $webpFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
                $webpPath = public_path(env('UPLOADS_DIRECTORY')) . '/' . $webpFileName;
                $urlWebp = url(env('UPLOADS_DIRECTORY') . '/' . $webpFileName);

                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo já existe.',
                    'url' => $urlOriginal,
                    'srcset' => $urlWebp,
                ]);
            }
        } else {
            $file->move(public_path(env('UPLOADS_DIRECTORY')), $fileName);

            if ($fileExtension !== 'gif') {
                $webpFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
                $webpPath = public_path(env('UPLOADS_DIRECTORY')) . '/' . $webpFileName;
                $imagick = new Imagick($originalPath);
                $imagick->setImageFormat('webp');
                $imagick->writeImage($webpPath);
                $imagick->clear();
                $imagick->destroy();

                $urlWebp = url(env('UPLOADS_DIRECTORY') . '/' . $webpFileName);
            }

            $urlOriginal = url(env('UPLOADS_DIRECTORY') . '/' . $fileName);
            return response()->json([
                'success' => true,
                'message' => 'Upload bem-sucedido.',
                'url' => $urlOriginal,
                'srcset' => isset($urlWebp) ? $urlWebp : null,
            ]);
        }
    }
}
