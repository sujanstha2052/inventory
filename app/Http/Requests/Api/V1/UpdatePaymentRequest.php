<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_method' => 'sometimes|in:cash,bank_transfer,mobile_money,cheque,other',
            'reference' => 'nullable|string|max:255',
            'payment_date' => 'sometimes|date',
            'notes' => 'nullable|string',
        ];
    }
}
