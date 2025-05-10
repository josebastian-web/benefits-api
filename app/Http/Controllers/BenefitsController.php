<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use App\Http\Requests\BenefitsRequest;
use Illuminate\Support\Facades\Validator;


class BenefitsController extends Controller
{

    public function benefitsList(BenefitsRequest $request)
    {
        try {
            // Obtenemos toda la data
            $benefitsJson = $this->getJsonFromApi("https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02");
            $filterBenefit = $this->getJsonFromApi("https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf");
            $filesFilter = $this->getJsonFromApi("https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687");
            // Convertimos a colection la data de los beneficios, filtros y fichas para poder manejarlos mejor
            $benefits = collect($benefitsJson["data"]);
            $filterBenefitData = collect($filterBenefit["data"]);
            $filesfilterData = collect($filesFilter["data"]);
            // Obtenemos los montos mínimos y máximos
            $minAmount = $request->minAmount;
            $maxAmount = $request->maxAmount;

            // Primero filtramos por montos mínimos y máximos
            $benefitsFiltered = $benefits->filter(function ($benefit) use ($minAmount, $maxAmount) {
                return $benefit["monto"] >= $minAmount && $benefit["monto"] <= $maxAmount;
            });

            $groupedByYear = $benefitsFiltered->groupBy(function ($item) {
                // Agrupamos solo por año
                return Carbon::parse($item["fecha"])->format("Y");
                // Usamos filter para que laravel no tome keys nulos
            })->filter()->map(function ($items, $anio) use ($filterBenefitData, $filesfilterData) {
                return [
                    "year" => $anio,
                    "num" => $items->count(),
                    "beneficios" => $items->map(function($item) use ($filterBenefitData, $filesfilterData, $anio) {
                        // Obtenemos el primer filtro
                        $findFilter = $filterBenefitData->firstWhere("id_programa", $item["id_programa"]);
                        // Obtenemos la primera ficha
                        $findFile = $filesfilterData->firstWhere("id", $findFilter["ficha_id"]);
                        return [
                            "id_programa" => $item["id_programa"] ?? null,
                            "monto" => $item["monto"] ?? null,
                            "fecha_recepcion" => $item["fecha_recepcion"] ?? null,
                            "fecha" => $item["fecha"] ?? null,
                            "ano" => $anio,
                            "view" => true,
                            "ficha" => [
                                "id" => $findFile["id"] ?? null,
                                "nombre" => $findFile["nombre"] ?? null,
                                "id_programa" => $findFile["id_programa"] ?? null,
                                "url" => $findFile["url"] ?? null,
                                "categoria" => $findFile["categoria"] ?? null,
                                "descripcion" => $findFile["descripcion"] ?? null
                            ]
                        ];
                    })->values()
                ];
            });

            // Usamos sortByDesc para ordenar y values para evitar keys numéricas
            $results = $groupedByYear->sortByDesc("year")->values()->toArray();

            return response()->json([
                "code" => 200,
                "success" => true,
                "data" => $results
            ], 200);

        } catch (Exception $e) {
            // Registramos el error
            Log::error("Error de petición: " . $e->getMessage(), ['exception' => $e]);
            // Retornamos un mensaje de error
            return response()->json(["error" => "Ocurrió un error al hacer la petición"], 500);
        }

    }

    public function getJsonFromApi($url) {
        try {
            $response = Http::get($url)->json();
            if (!isset($response["data"])) {
                throw new Exception("La respuesta no contiene datos.");
            }
            return $response;
        } catch (Exception $e) {
            Log::error("Error en solicitud a $url: " . $e->getMessage());
            return ["error" => $e->getMessage()];
        }
    }

}
