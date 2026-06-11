<?php

namespace App\Http\Requests;

use App\Enums\NotificationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'provider_message_id' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::in([
                NotificationStatus::Delivered->value,
                NotificationStatus::Dropped->value,
            ])],
            'reason' => ['nullable', 'string', 'max:255'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
