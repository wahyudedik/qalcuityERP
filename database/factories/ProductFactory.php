<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->words(2, true),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{4}'),
            'barcode' => $this->faker->unique()->ean13(),
            'category' => $this->faker->randomElement(['Electronics', 'Clothing', 'Food', 'Books', 'Tools']),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'liter', 'box', 'pack']),
            'price_buy' => $this->faker->randomFloat(2, 10, 1000),
            'price_sell' => $this->faker->randomFloat(2, 15, 1500),
            'stock_min' => $this->faker->numberBetween(1, 50),
            'description' => $this->faker->optional()->paragraph(),
            'image' => $this->faker->optional()->imageUrl(400, 400, 'products'),
            'is_active' => $this->faker->boolean(90),
            'has_expiry' => $this->faker->boolean(30),
            'expiry_alert_days' => $this->faker->numberBetween(7, 30),
            'qr_code_path' => $this->faker->optional()->filePath(),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product has expiry.
     */
    public function withExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_expiry' => true,
            'expiry_alert_days' => $this->faker->numberBetween(7, 30),
        ]);
    }

    /**
     * Indicate that the product doesn't have expiry.
     */
    public function withoutExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_expiry' => false,
            'expiry_alert_days' => null,
        ]);
    }
}
