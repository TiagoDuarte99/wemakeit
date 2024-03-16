<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class UploadController extends Controller
{
    public function upload(Request $request)
    {
      $this->validate($request, [
        'file' => 'required|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path(env('UPLOADS_DIRECTORY')), $fileName);

        $url = url(env('UPLOADS_DIRECTORY') . '/' . $fileName);
        Log::info($url );
        return response()->json([
            'success' => true,
            'message' => 'Upload bem-sucedido.',
            'url' => $url,
            'srcset' => 'http://localhost:8000/uploads/marcacao-de-um-compromisso.webp'
        ]);
    }
}
