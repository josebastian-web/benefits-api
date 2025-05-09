<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class BenefitsController extends Controller
{
  public function benefitsList(Request $request )
  {
    // Obtenemos toda la data
    $benefitsJson = Http::get("https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02")->json();
    $filterBenefit = Http::get("https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf")->json();
    $filesFilter = Http::get("https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687")->json();

    // Convertimos a colection la data de los beneficios para poder manejarlos mejor
    $benefits = collect($benefitsJson["data"]);
    // Obtenemos los montos mínimos y máximos
    $minAmount = 1000; // Reemplazar por $request->min
    $maxAmount = 60000; // Reemplazar por $request->max

    // Primero filtramos por montos mínimos y máximos
    $benefitsFiltered = $benefits->filter(function ($benefit) use ($minAmount, $maxAmount) {
      return $benefit["monto"] >= $minAmount && $benefit["monto"] <= $maxAmount;
    });

    $groupedByYear = $benefitsFiltered->groupBy(function ($item) {
      // Agrupamos solo por año
      return Carbon::parse($item["fecha"])->format("Y");
      // Usamos filter para que laravel no tome keys nulos
    })->filter()->map(function ($items, $anio) use ($filterBenefit, $filesFilter) {
      return [
        "year" => $anio,
        "monto_total" => $items->sum("monto"),
        "num" => $items->count(),
        "beneficios" => $items->map(function($item) use ($filterBenefit, $filesFilter, $anio) {

          $filterBenefitData = collect($filterBenefit["data"]);
          $filesfilterData = collect($filesFilter["data"]);
          // Obtenemos el primer filtro
          $findFilter = $filterBenefitData->firstWhere("id_programa", $item["id_programa"]);
          // Obtenemos la primera ficha
          $findFile = $filesfilterData->firstWhere("id", $findFilter["ficha_id"]);
          // Formamos la data
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
              "descripcion" => $findFile["descripcion"] ?? null,
            ]
          ];
        })->values()
      ];
    });

    // Usamos sortByDesc para ordenar y values para evitar keys numéricas
    $results = $groupedByYear->sortByDesc('year')->values()->toArray();

    return response()->json($results);
  }

}
