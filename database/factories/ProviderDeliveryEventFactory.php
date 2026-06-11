<?php

namespace Database\Factories;

use App\Models\ProviderDeliveryEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderDeliveryEvent>
 */
class ProviderDeliveryEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'fake-sms',
            'provider_message_id' => 'fake-sms-'.$this->faker->unique()->uuid(),
            'event_type' => 'delivered',
            'payload' => ['status' => 'delivered'],
        ];
    }
}
