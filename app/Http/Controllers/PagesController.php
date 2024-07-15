<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use SebastianBergmann\Environment\Console;

class PagesController extends Controller
{

  public function getPageData($namePage)
  {
    /* error_log(print_r($namePage, true)); */
    // Verifique se os dados estão presentes no cache
    /*     $cachedData = Cache::get('page_data_' . $namePage);
    error_log(print_r($cachedData, true));
    if ($cachedData) {
      return response()->json(['success' => true, 'data' => $cachedData]);
    } */

    // Se os dados não estiverem no cache, consulte o banco de dados
    $pages = Pages::where('namePage', $namePage)->get();

    if ($pages->isNotEmpty()) {
      $data = $pages->map(function ($page) {
        return [
          'section' => $page->section,
          'content' => $page->content,
          'title' => $page->title,
          'description' => $page->description,
          'metaTitle' => $page->metaTitle,
          'contentPoints' => $page->contentPoints
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
          'section' => 'nullable|string', 
          'content' => 'nullable|array',
          'title' => 'nullable|string',   
          'description' => 'nullable|string',
          'metaTitle' => 'nullable|string', 
          'contentPoints' => 'nullable|array',
      ]);
  
      if ($validator->fails()) {
          return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
      }
  
      // Verificar se já existe um registro com o mesmo namePage
      if (Pages::where('namePage', $request->namePage)->exists()) {
          return response()->json(['success' => false, 'message' => 'O nome da página já existe.'], 409); // 409 Conflict
      }
  
      // Definir valores padrão para campos opcionais se não forem fornecidos
      $data = $request->all();
      $data['section'] = $data['section'] ?? 'default_section';
      $data['content'] = json_encode($data['content'] ?? []); // Convertendo array para JSON
      $data['title'] = $data['title'] ?? 'default_title';
      $data['description'] = $data['description'] ?? 'default_description';
      $data['metaTitle'] = $data['metaTitle'] ?? 'default_metaTitle';
      $data['contentPoints'] = json_encode($data['contentPoints'] ?? []);
  
      $page = Pages::create($data);
  
      $cacheKey = 'page_data_' . $request->input('namePage');
      Cache::forget($cacheKey);
  
      return response()->json(['success' => true, 'data' => $page], 201);
  }
  

  public function updatePage(Request $request, $namePage, $section)
  {

    $sectionToUpdate = $request->input('section');
    $dataToUpdate = $request->input('content');
    $titleToUpdate = $request->input('title');
    $descriptionToUpdate = $request->input('description');
    $metaTitleToUpdate = $request->input('metaTitle');
    $contentPointsToUpdate = $request->input('contentPoints');

    $page = Pages::where(['namePage' => $namePage, 'section' => $section])->first();

    if ($page) {
      // Atualiza o registro específico
      $page->update([
        'content' => $dataToUpdate,
        'section' => $sectionToUpdate,
        'title' => $titleToUpdate,
        'description' => $descriptionToUpdate,
        'metaTitle' => $metaTitleToUpdate,
        'contentPoints' => $contentPointsToUpdate
      ]);
      /* error_log(print_r($page, true)); */
      $cacheKey = 'page_data_' . $request->input('namePage');

      Cache::forget($cacheKey);
      /*       if (Cache::has($cacheKey)) {
        // O item ainda está presente no cache
        error_log("O item ainda está presente no cache");
    } else {
        // O item foi removido do cache
        error_log("O item foi removido do cache");
    } */
      return response()->json(['success' => true, 'message' => 'Página atualizada com sucesso']);
    } else {
      return response()->json(['success' => false, 'message' => 'Registro não encontrado'], 404);
    }
  }

  public function deletePage($namePage)
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

  public function deleteSection($namePage, $section)
  {
    $page = Pages::where(['namePage' => $namePage, 'section' => $section])->first();

    if ($page) {
      $page->delete();

      $cacheKey = 'page_data_' . $namePage;
      Cache::forget($cacheKey);

      return response()->json(['success' => true, 'message' => 'Seção excluída com sucesso']);
    } else {
      return response()->json(['success' => false, 'message' => 'Seção não encontrada'], 404);
    }
  }

  public function getAllNamePages()
  {
    // Consultar o banco de dados para obter todos os valores únicos de namePage
    $namePages = Pages::select('namePage')->distinct()->get();

    if ($namePages->isNotEmpty()) {
      return response()->json(['success' => true, 'data' => $namePages]);
    } else {
      return response()->json(['success' => false, 'message' => 'Nenhuma página encontrada'], 404);
    }
  }
}
