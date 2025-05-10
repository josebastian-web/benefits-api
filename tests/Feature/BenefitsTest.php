<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\BenefitsController;
use App\Http\Requests\BenefitsRequest;

class BenefitsTest extends TestCase
{
    public function test_datos_correctos_lista_beneficios()
    {
        // Cargar datos desde el archivo JSON
        $benefitsJson = json_decode(file_get_contents(storage_path('app/tests/Fixtures/benefits.json')), true);
        $filterBenefitJson = json_decode(file_get_contents(storage_path('app/tests/Fixtures/filtersBenefits.json')), true);
        $filesFilterJson = json_decode(file_get_contents(storage_path('app/tests/Fixtures/filesFilter.json')), true);

        // Simular respuestas de la API con datos falsos
         Http::fake([
            'https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response($benefitsJson, 200),
            'https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response($filterBenefitJson, 200),
            'https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687' => Http::response($filesFilterJson, 200)
        ]);

        // Crear una instancia del controlador
        $controller = new BenefitsController();
        $request = new BenefitsRequest([
            'minAmount' => 4000,
            'maxAmount' => 8000
        ]);

        // Llamar a la función y capturar la respuesta
        $response = $controller->benefitsList($request);
        $jsonResponse = json_decode($response->getContent(), true);

        // Verificar que los datos fueron obtenidos correctamente
        $this->assertEquals(200, $jsonResponse["code"]);
        $this->assertTrue($jsonResponse["success"]);
        $this->assertArrayHasKey("data", $jsonResponse);
    }

    public function test_error_lista_beneficios()
    {
        // Simular error en la API con código 500
        Http::fake([
            'https://run.mocky.io/v3/*' => Http::response([], 500)
        ]);

        // Crear instancia del controlador y request simulado
        $controller = new BenefitsController();
        $request = new BenefitsRequest([
            'minAmount' => 4000,
            'maxAmount' => 8000
        ]);

        // Llamar a la función y capturar respuesta
        $response = $controller->benefitsList($request);
        $jsonResponse = json_decode($response->getContent(), true);

        // Verificar que devuelve un mensaje de error
        $this->assertArrayHasKey("error", $jsonResponse);
        $this->assertEquals("Ocurrió un error al hacer la petición", $jsonResponse["error"]);
    }

    public function test_datos_correctos_beneficios(): void
    {
        $benefitsJson = json_decode(file_get_contents(storage_path('app/tests/Fixtures/benefits.json')), true);
        // Simula respuestas de la API con datos falsos
        Http::fake([
            'https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response($benefitsJson, 200)
        ]);

        // Instanciar el controlador y llamar a la función
        $controller = new BenefitsController();
        $benefitsJson = $controller->getJsonFromApi("https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02");

        // Verificar que los datos fueron obtenidos correctamente
        $this->assertIsArray($benefitsJson);
        $this->assertArrayHasKey('data', $benefitsJson);
        $this->assertCount(14, $benefitsJson['data']);

    }

    public function test_datos_vacios_beneficios(): void
    {
        // Simula una respuesta sin la clave "data"
        Http::fake([
            'https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response([], 200)
        ]);

        $controller = new BenefitsController();
        $response = $controller->getJsonFromApi("https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02");

        // Verificar que devuelve un mensaje de error
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals("La respuesta no contiene datos.", $response['error']);
    }

    public function test_error_en_respuesta_fallida_beneficios(): void
    {
        // Simula una API fallando con código 500
        Http::fake([
            'https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response([], 500)
        ]);

        $controller = new BenefitsController();
        $response = $controller->getJsonFromApi("https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02");

        // Verificar que devuelve un mensaje de error
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
    }

    public function test_datos_correctos_filtros(): void
    {
        $filterBenefitJson = json_decode(file_get_contents(storage_path('app/tests/Fixtures/filtersBenefits.json')), true);
        // Simula respuestas de la API con datos falsos
        Http::fake([
            'https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response($filterBenefitJson, 200)
        ]);

        // Instanciar el controlador y llamar a la función
        $controller = new BenefitsController();
        $filterBenefit = $controller->getJsonFromApi("https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf");

        // Verificar que los datos fueron obtenidos correctamente
        $this->assertIsArray($filterBenefit);
        $this->assertArrayHasKey('data', $filterBenefit);
        $this->assertCount(3, $filterBenefit['data']);

    }

    public function test_datos_vacios_filtros(): void
    {
        // Simula una respuesta sin la clave "data"
        Http::fake([
            'https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response([], 200)
        ]);

        $controller = new BenefitsController();
        $response = $controller->getJsonFromApi("https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf");

        // Verificar que devuelve un mensaje de error
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals("La respuesta no contiene datos.", $response['error']);
    }

    public function test_error_en_respuesta_fallida_filtros(): void
    {
        // Simula una API fallando con código 500
        Http::fake([
            'https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response([], 500)
        ]);

        $controller = new BenefitsController();
        $response = $controller->getJsonFromApi("https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf");

        // Verificar que devuelve un mensaje de error
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
    }

    public function test_datos_correctos_fichas(): void
    {
        $filesFilterJson = json_decode(file_get_contents(storage_path('app/tests/Fixtures/filesFilter.json')), true);
        // Simula respuestas de la API con datos falsos
        Http::fake([
            'https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687' => Http::response($filesFilterJson, 200)
        ]);

        // Instanciar el controlador y llamar a la función
        $controller = new BenefitsController();
        $filesFilter = $controller->getJsonFromApi("https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687");

        // Verificar que los datos fueron obtenidos correctamente
        $this->assertIsArray($filesFilter);
        $this->assertArrayHasKey('data', $filesFilter);
        $this->assertCount(3, $filesFilter['data']);

    }

    public function test_datos_vacios_fichas(): void
    {
        // Simula una respuesta sin la clave "data"
        Http::fake([
            'https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687' => Http::response([], 200)
        ]);

        $controller = new BenefitsController();
        $response = $controller->getJsonFromApi("https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687");

        // Verificar que devuelve un mensaje de error
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals("La respuesta no contiene datos.", $response['error']);
    }

    public function test_error_en_respuesta_fallida_fichas(): void
    {
        // Simula una API fallando con código 500
        Http::fake([
            'https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687' => Http::response([], 500)
        ]);

        $controller = new BenefitsController();
        $response = $controller->getJsonFromApi("https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687");

        // Verificar que devuelve un mensaje de error
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
    }
}
