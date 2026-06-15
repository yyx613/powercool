<?php

namespace Database\Factories;

use App\Models\SaleEnquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleEnquiry>
 */
class SaleEnquiryFactory extends Factory
{
    protected $model = SaleEnquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'ENQ' . fake()->unique()->numberBetween(10000, 99999),
            'enquiry_date' => now(),
            'enquiry_source' => SaleEnquiry::SOURCE_WEBSITE,
            'name' => fake()->name(),
            'phone_number' => fake()->numerify('01#########'),
            'email' => fake()->safeEmail(),
            'preferred_contact_method' => SaleEnquiry::CONTACT_WHATSAPP,
            'category' => SaleEnquiry::TYPE_PRODUCT_PRICING,
            'description' => fake()->sentence(),
            'product_service_interested' => fake()->words(3, true),
            'assigned_user_id' => User::factory(),
            'priority' => SaleEnquiry::PRIORITY_MEDIUM,
            'status' => SaleEnquiry::STATUS_NEW,
            'quality' => SaleEnquiry::QUALITY_SEEN_AND_REPLY,
            'created_by' => User::factory(),
        ];
    }
}
