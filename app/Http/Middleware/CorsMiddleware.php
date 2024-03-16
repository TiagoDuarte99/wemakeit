<?php

namespace App\Http\Middleware;

class CorsMiddleware
{
  public function handle($request, \Closure $next)
  {


    $headers = [
      'Access-Control-Allow-Origin' => '*',
      'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
      'Access-Control-Allow-Credentials' => 'true'
    ];

    if($request->isMethod('OPTIONS')){
      return response()->json('ok', 200, $headers);
    }

    $response = $next($request);

    if(\method_exists($response, 'headers')){
      foreach($headers as $key => $value){
        $response->header($key, $value);
      }
      return $response;
    }

    if($response instanceof \Symfony\Component\HttpFoundation\Response){
      foreach($headers as $key => $value){
        $response->headers->set($key, $value);
      }
      return $response;
    }

    return $response;

  }
}
