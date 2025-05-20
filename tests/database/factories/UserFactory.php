<?php
namespace Database\Factories;

use Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'username' => $this->faker->userName,
            'email'    => $this->faker->unique()->safeEmail,
            'mobile'   => $this->faker->phoneNumber,
            'avatar'   => $this->faker->imageUrl(),
            'password' => bcrypt('123456'),
        ];
    }
}

