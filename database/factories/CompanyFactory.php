<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $logos = ['facebook.png', 'youtube.png', 'google.png'];

        return [
            'name' => $this->faker->sentence(rand(1,3)),
            'email' => $this->faker->unique()->companyEmail(),
            'logo' => "logos/{$logos[rand(0,2)]}",
            'website' => "https://{$this->faker->unique()->domainName()}"
        ];
    }
}
