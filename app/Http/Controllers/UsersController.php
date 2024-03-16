<?php

namespace App\Http\Controllers;

/* use Illuminate\Support\Facades\Auth; */

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class UsersController extends Controller
{
  /**
   * Create a new UserController instance.
   *
   * @return void
   */
  public function __construct()
  {
    /*  $this->middleware('auth:api', ['except' => ['login']]); */
  }

  //Registar utilizador
  public function register(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|unique:users',
      'password' => 'required|confirmed'
    ]);

    $name = $request->input('name');
    $password = Hash::make($request->input('password'));

    // Crie o usuário apenas se as validações passarem
    $user = User::create(['name' => $name, 'password' => $password]);

    // Remova a senha do array antes de enviar a resposta
    $userWithoutPassword = $user->makeHidden(['password']);

    return response()->json(['status' => 'success', 'operation' => 'created', 'user' => $userWithoutPassword]);
  }


  // Update User
  public function update(Request $request, $id)
  {

      // Custom validation rules
      $validator = Validator::make($request->all(), [
          'name' => ['sometimes', 'required', Rule::unique('users')->ignore($id)],
          'password' => 'sometimes|required|confirmed',
      ]);
  
      // Check for validation errors
      if ($validator->fails()) {
          return response()->json(['status' => 'error', 'error' => $validator->errors()->first()], 422);
      }
  
      $user = User::find($id);
  
      if (!$user) {
          return response()->json(['status' => 'error', 'error' => 'User not found'], 404);
      }
  
      // Check if the authenticated user is the owner or has ID 1
      $authenticatedUser = Auth::user();
      if ($authenticatedUser->id !== $user->id && $authenticatedUser->id !== 1) {
          return response()->json(['status' => 'error', 'error' => 'Not authorized to edit this user'], 403);
      }
  
      // Update only the fields sent in the request
      if ($request->has('name')) {
          $user->name = $request->input('name');
      }
  
      if ($request->has('password')) {
          $user->password = Hash::make($request->input('password'));
      }
  
      $user->save();
  
      // Remove the password from the array before sending the response
      $userWithoutPassword = $user->makeHidden(['password']);
  
      return response()->json(['status' => 'success', 'message' => 'User successfully updated.', 'user' => $userWithoutPassword]);
  }

  // Apagar utilizadores
  public function delete($id)
  {
  
    $user = User::find($id);

    if (!$user) {
      return response()->json(['status' => 'error', 'message' => 'Utilizador não encontrado'], 404);
    }

    // Verificar se o utilizador autenticado é o próprio ou o com que tem ID 1
    $authenticatedUser = Auth::user();
    if ($authenticatedUser->id !== $user->id && $authenticatedUser->id !== 1) {
      return response()->json(['status' => 'error', 'message' => 'Não autorizado a excluir este utilizador'], 403);
    }

    $user->delete();

    return response()->json(['status' => 'success', 'operation' => 'deleted', 'user' => $user]);
  }


  public function listUsers()
  {
    $users = User::all();
    $userWithoutPassword = $users->makeHidden(['password']); 
    return response()->json(['users' => $userWithoutPassword], 200);
  }
}
