<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{

  public function send(Request $request)
  {
    LOG::info($request);
        $rules = [
          'name' => 'required|string',
          'email' => 'required|email',
          'city' => 'required|string',
          'phoneNumber' => 'required|numeric',
          'company' => 'required|string',
          'message' => 'required|string',
          'politics' => 'required|boolean',
      ];
  
      $messagesPT = [
        'name.required' => 'O campo nome é obrigatório.',
        'name.string' => 'O campo nome deve ser uma string.',
        
        'email.required' => 'O campo email é obrigatório.',
        'email.email' => 'O campo email deve ser um endereço de e-mail válido.',
        
        'city.required' => 'O campo cidade é obrigatório.',
        'city.string' => 'O campo cidade deve ser uma string.',
        
        'phoneNumber.required' => 'O campo número de telefone é obrigatório.',
        'phoneNumber.numeric' => 'O campo número de telefone deve ser um número.',
        
        'company.required' => 'O campo empresa é obrigatório.',
        'company.string' => 'O campo empresa deve ser uma string.',
        
        'message.required' => 'O campo mensagem é obrigatório.',
        'message.string' => 'O campo mensagem deve ser uma string.',
        
        'politics.required' => 'O campo políticas é obrigatório.',
        'politics.boolean' => 'O campo políticas deve ser um valor booleano.',
    ];

    $messagesES = [
      'name.required' => 'El campo nombre es obligatorio.',
      'name.string' => 'El campo nombre debe ser una cadena de texto.',
      
      'email.required' => 'El campo correo electrónico es obligatorio.',
      'email.email' => 'El campo correo electrónico debe ser una dirección de correo electrónico válida.',
      
      'city.required' => 'El campo ciudad es obligatorio.',
      'city.string' => 'El campo ciudad debe ser una cadena de texto.',
      
      'phoneNumber.required' => 'El campo número de teléfono es obligatorio.',
      'phoneNumber.numeric' => 'El campo número de teléfono debe ser un número.',
      
      'company.required' => 'El campo empresa es obligatorio.',
      'company.string' => 'El campo empresa debe ser una cadena de texto.',
      
      'message.required' => 'El campo mensaje es obligatorio.',
      'message.string' => 'El campo mensaje debe ser una cadena de texto.',
      
      'politics.required' => 'El campo políticas es obligatorio.',
      'politics.boolean' => 'El campo políticas debe ser un valor booleano.',
  ];
  
  
      if($request->input('valueLang') === 'pt'){
        $validator = Validator::make($request->all(), $rules, $messagesPT);
      }else{
        $validator = Validator::make($request->all(), $rules, $messagesES);
      }
  
      // Verifique se a validação falhou
      if ($validator->fails()) {
          return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
      }
    $data = [
      "name" => $request->input('name'),
      "email" => $request->input('email'),
      "city" => $request->input('city'),
      "phoneNumber" => $request->input('phoneNumber'),
      "company" => $request->input('company'),
      "message2" => $request->input('message'),
      "politics" => $request->input('politics'),
    ];

    $page = $request->input('page');

    Mail::send('mail', $data, function ($message) use ($data, $page) {
      $message->to($data['email'], $data['name'])
        ->subject("Email from $page");
      $message->from(env('MAIL_FROM_ADDRESS'), 'your Name');
    });

    if($request->input('valueLang') === 'pt'){
      return response()->json(['success' => true, 'message' => 'Email enviado com secesso']);
    }else{
      return response()->json(['success' => true, 'message' => 'Correo electrónico enviado con éxito']);
    }
    
  }
}
