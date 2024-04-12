<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class PagesController extends Controller
{

  public function getPageData($namePage)
  {
    // Verifique se os dados estão presentes no cache
    $cachedData = Cache::get('page_data_' . $namePage);

    if ($cachedData) {
      return response()->json(['success' => true, 'data' => $cachedData]);
    }

    // Se os dados não estiverem no cache, consulte o banco de dados
    $pages = Pages::where('namePage', $namePage)->get();

    if ($pages->isNotEmpty()) {
      $data = $pages->map(function ($page) {
        return [
          'section' => $page->section,
          'content' => $page->content,
        ];
      });

      // Armazene os dados no cache
      Cache::put('page_data_' . $namePage, $data, 60); // Cache válido por 60 minutos

      return response()->json(['success' => true, 'data' => $data]);
    } else {
      return response()->json(['success' => false, 'message' => 'Páginas não encontradas'], 404);
    }
  }

  public function insertPageData(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'namePage' => 'required|string',
      'section' => 'required|string',
      'content' => 'required|array',
    ]);

    if ($validator->fails()) {
      return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $page = Pages::create($request->all());

    $cacheKey = 'page_data_' . $request->input('namePage');
    Cache::forget($cacheKey);

    return response()->json(['success' => true, 'data' => $page], 201);
  }

  public function updatePage(Request $request, $namePage, $section)
  {

    $sectionToUpdate = $request->input('section');
    $dataToUpdate = $request->input('content');

    $page = Pages::where(['namePage' => $namePage, 'section' => $section])->first();

    if ($page) {
      // Atualiza o registro específico
      $page->update(['content' => $dataToUpdate, 'section' => $sectionToUpdate]);

      $cacheKey = 'page_data_' . $request->input('namePage');

      Cache::forget($cacheKey);
      return response()->json(['success' => true, 'message' => 'Página atualizada com sucesso']);
    } else {
      return response()->json(['success' => false, 'message' => 'Registro não encontrado'], 404);
    }
  }

  public function deletePage(Request $request, $namePage)
  {
      $pages = Pages::where('namePage', $namePage)->get();
  
      if ($pages->isNotEmpty()) {
          // Exclui cada página com o nome fornecido
          foreach ($pages as $page) {
              $page->delete();
          }
  
          // Remove os dados relacionados ao cache
          $cacheKey = 'page_data_' . $namePage;
          Cache::forget($cacheKey);
  
          return response()->json(['success' => true, 'message' => 'Páginas excluídas com sucesso']);
      } else {
          return response()->json(['success' => false, 'message' => 'Nenhuma página encontrada com o nome especificado'], 404);
      }
  }

  public function deleteSection(Request $request, $namePage, $section)
  {
      $page = Pages::where(['namePage' => $namePage, 'section' => $section])->first();
  
      if ($page) {
          // Exclui apenas a seção específica
          $page->where('section', $section)->delete();
  
          $cacheKey = 'page_data_' . $namePage;
          Cache::forget($cacheKey);
  
          return response()->json(['success' => true, 'message' => 'Seção excluída com sucesso']);
      } else {
          return response()->json(['success' => false, 'message' => 'Seção não encontrada'], 404);
      }
  }
  
}
