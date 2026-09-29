<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordUsageRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'merchant_id' => ['required','integer','exists:merchants,id'],
            'customer_id' => ['required','integer','exists:customers,id'],
            'event_key' => ['required','string','max:191'],
            'occurred_at' => ['required','date'],
            'units' => ['required','integer','min:1','max:1000000'],
            'type' => ['nullable','string','max:50'],
            'metadata' => ['nullable','array'],
        ];
    }
}
