<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = $this->faker->company();
        
        return [
            'name' => $companyName,
            'slug' => \Str::slug($companyName) . '-' . $this->faker->randomNumber(4),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'logo' => $this->faker->optional()->imageUrl(200, 200, 'business'),
            'plan' => $this->faker->randomElement(['trial', 'basic', 'professional', 'enterprise']),
            'is_active' => $this->faker->boolean(90),
            'trial_ends_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'plan_expires_at' => $this->faker->optional()->dateTimeBetween('+1 month', '+1 year'),
            'business_type' => $this->faker->randomElement(['manufacturing', 'retail', 'service', 'trading']),
            'business_description' => $this->faker->optional()->paragraph(),
            'onboarding_completed' => $this->faker->boolean(70),
            'costing_method' => $this->faker->randomElement(['simple', 'avco', 'fifo']),
            'npwp' => $this->faker->optional()->numerify('##.###.###.#-###.###'),
            'website' => $this->faker->optional()->url(),
            'city' => $this->faker->city(),
            'province' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'bank_name' => $this->faker->optional()->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'bank_account' => $this->faker->optional()->bankAccountNumber(),
            'bank_account_name' => $this->faker->optional()->name(),
            'tagline' => $this->faker->optional()->catchPhrase(),
            'stamp_image' => $this->faker->optional()->imageUrl(100, 100, 'business'),
            'director_signature' => $this->faker->optional()->imageUrl(200, 100, 'business'),
            'invoice_footer_notes' => $this->faker->optional()->sentence(),
            'invoice_payment_terms' => $this->faker->optional()->randomElement(['Net 30', 'Net 15', 'Due on Receipt']),
            'letter_head_color' => $this->faker->hexColor(),
            'doc_number_prefix' => $this->faker->optional()->randomElement(['INV', 'SO', 'PO']),
            'enabled_modules' => $this->faker->randomElements([
                'accounting', 'inventory', 'sales', 'purchasing', 'hrm', 'pos', 'manufacturing'
            ], $this->faker->numberBetween(3, 7)),
        ];
    }

    /**
     * Indicate that the tenant is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the tenant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the tenant is on trial.
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'trial',
            'trial_ends_at' => $this->faker->dateTimeBetween('now', '+30 days'),
        ]);
    }

    /**
     * Indicate that the tenant has completed onboarding.
     */
    public function onboarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed' => true,
        ]);
    }
}