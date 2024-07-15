<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class RecaptchaMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('recaptchaToken'),
            'remoteip' => $request->ip(),
        ]);
        error_log(print_r($response, true));
        if ($response->failed() || !$response['success']) {
            error_log(print_r('success', true));
            return response()->json(['success' => false, 'message' => 'Falha na verificação reCAPTCHA'], 422);
        }
        
        return $next($request);
    }
}
