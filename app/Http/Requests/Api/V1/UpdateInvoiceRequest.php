<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:unpaid,paid,partially_paid,cancelled',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
