<?php

namespace App\Http\Requests\VisitorInterest;

use App\Models\VisitorInterest;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorInterestRequest extends FormRequest
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
            'interest_type' => ['required', 'string', 'in:'.implode(',', array_keys(VisitorInterest::typeOptions()))],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:25', 'required_without:email'],
            'company' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'investment_range' => ['nullable', 'string', 'max:120'],
            'partnership_area' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:4000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'interest_type.required' => 'Please choose what you are interested in.',
            'interest_type.in' => 'Please choose a valid interest type.',
            'name.required' => 'Please tell us your name.',
            'email.required_without' => 'Please provide an email or phone number.',
            'phone.required_without' => 'Please provide a phone number or email.',
            'message.required' => 'Please share your idea or details.',
        ];
    }
}
