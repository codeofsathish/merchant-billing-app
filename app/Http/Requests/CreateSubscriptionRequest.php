<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateSubscriptionRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['merchant_id'=>['required','integer','exists:merchants,id'],'customer_id'=>['required','integer','exists:customers,id'],'plan_id'=>['required','integer','exists:plans,id'],'starts_at'=>['nullable','date']]; } }
