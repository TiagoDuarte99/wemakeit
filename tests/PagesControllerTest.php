<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\PagesController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Pages;
use Illuminate\Http\Request;


class PagesControllerTest extends TestCase
{
    /**
     * Testa se o método getPageData retorna os dados corretos da página.
     *
     * @return void
     */

     public function test_getPageData_returns_correct_data()
     {
         $controller = new PagesController();
     
         $response = $controller->getPageData('QuiosqueEs');
     
         $responseData = json_decode($response->getContent());
          
         $this->assertTrue($responseData->success);
     
         $this->assertIsArray($responseData->data);
     
         foreach ($responseData->data as $item) {
             $this->assertTrue(property_exists($item, 'section'));
             $this->assertTrue(property_exists($item, 'content'));
         }
     }
 
     public function testInsertPageDataSuccess()
     {
         // Configuração do mock para Validator
         log::info('dsdasdds');
         Validator::shouldReceive('make')
             ->once()
             ->andReturn(collect());
 
         // Configuração do mock para Cache
         Cache::shouldReceive('forget')
             ->once();
 
         // Configuração do mock para Pages
         Pages::shouldReceive('create')
             ->once()
             ->andReturn(new Pages());
 
         $requestData = [
             'namePage' => 'examplePage',
             'section' => 'exampleSection',
             'content' => ['exampleContent']
         ];
 
         $controller = new PagesController();
         $request = new Request([], $requestData); // Criar instância do Request
         $response = $controller->insertPageData($request);
 
         $this->assertEquals(201, $response->getStatusCode());
         $this->assertJson($response->getContent());
         $responseData = json_decode($response->getContent(), true);
         $this->assertEquals(['success' => true, 'data' => $requestData], $responseData);
     }
 
     public function testInsertPageDataValidationFailed()
     {
         // Configuração do mock para Validator
         Validator::shouldReceive('make')
             ->once()
             ->andReturn(collect(['namePage' => 'The namePage field is required.']));
 
         $requestData = [
             'section' => 'exampleSection',
             'content' => ['exampleContent']
         ];
 
         $controller = new PagesController();
         $request = new Request([], $requestData); // Criar instância do Request
         $response = $controller->insertPageData($request);
 
         $this->assertEquals(422, $response->getStatusCode());
         $this->assertJson($response->getContent());
         $responseData = json_decode($response->getContent(), true);
         $this->assertEquals(['success' => false, 'errors' => ['namePage' => 'The namePage field is required.']], $responseData);
     }
     
}


