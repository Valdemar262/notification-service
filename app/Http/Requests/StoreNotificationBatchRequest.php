<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreNotificationBatchRequest extends FormRequest
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
            'idempotency_key' => ['required', 'string', 'max:255'],
            'channel' => ['required', Rule::enum(NotificationChannel::class)],
            'priority' => ['required', Rule::enum(NotificationPriority::class)],
            'message' => ['required', 'string'],
            'recipient_ids' => ['required', 'array', 'min:1', 'max:'.config('notifications.max_recipients_per_batch')],
            'recipient_ids.*' => ['required', 'string', 'distinct'],
            'initiator' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $channel = $this->string('channel')->toString();
                $limit = config("notifications.message_limits.{$channel}");

                if (is_int($limit) && mb_strlen($this->string('message')->toString()) > $limit) {
                    $validator->errors()->add('message', "The message must not be greater than {$limit} characters for {$channel}.");
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'idempotency_key' => $this->header('Idempotency-Key'),
        ]);
    }
}
