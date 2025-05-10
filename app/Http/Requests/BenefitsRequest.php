<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BenefitsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'minAmount' => 'required|integer',
        'maxAmount' => 'required|integer|gt:minAmount',
        ];
    }

    public function messages(): array
    {
        return [
            'minAmount.required' => 'El monto mínimo es requerido',
            'minAmount.integer' => 'El monto mínimo debe ser numérico ej: 1000',
            'maxAmount.required' => 'El monto máximo es requerido',
            'maxAmount.integer' => 'El monto máximo debe ser numérico ej: 90000',
            'maxAmount.gt' => 'El monto máximo debe ser mayor al monto mínimo.'
        ];
    }
    // Forzamos que muestre la validación dado que estamos llamando a una api
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Los datos proporcionados no son válidos.',
            'errors' => $validator->errors(),
        ], 422));
    }

}
