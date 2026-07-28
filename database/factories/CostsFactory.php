<?php

namespace Database\Factories;

use App\Models\Costs;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Costs>
 */
class CostsFactory extends Factory
{
    protected $model = Costs::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 1500),
            'description' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
