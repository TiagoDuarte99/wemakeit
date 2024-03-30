<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Imagick;

class ProcessImageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $file = $request->file('file');

        $fileName = time() . '_' . $file->getClientOriginalName();

        $originalPath = public_path(env('UPLOADS_DIRECTORY')) . '/' . $fileName;

        $file->move(public_path(env('UPLOADS_DIRECTORY')), $fileName);

        $webpPath = url(env('UPLOADS_DIRECTORY')) . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '.webp';

        try {
            $imagick = new Imagick($originalPath);

            $imagick->setImageFormat('webp');
            $imagick->writeImage($webpPath);

            $imagick->clear();
            $imagick->destroy();

            $request->merge([
                'original_path' => $originalPath,
                'webp_path' => $webpPath,
            ]);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Erro ao processar a imagem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar a imagem. Por favor, tente novamente mais tarde.',
            ], 500);
        }
    }
}
