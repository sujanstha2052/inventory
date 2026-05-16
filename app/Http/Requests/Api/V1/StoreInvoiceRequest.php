<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'status' => 'sometimes|in:unpaid,paid,partially_paid,cancelled',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
