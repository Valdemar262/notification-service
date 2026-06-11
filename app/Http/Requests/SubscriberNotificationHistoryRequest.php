<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriberNotificationHistoryRequest extends FormRequest
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
            'channel' => ['nullable', Rule::enum(NotificationChannel::class)],
            'status' => ['nullable', Rule::enum(NotificationStatus::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
