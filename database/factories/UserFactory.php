<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $studentRole = Role::firstOrCreate(
            ['slug' => 'student'],
            ['name' => 'Student'],
        );

        $courses = ['BSIT', 'BSCE', 'BSEE', 'BSECE', 'BSAE'];
        $years = ['1', '2', '3', '4', '5'];
        $sections = ['A', 'B', null];

        return [
            'name' => fake()->name(),
            'id_number' => fake()->unique()->numerify('2025-####'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'course' => fake()->randomElement($courses),
            'section' => fake()->randomElement($sections),
            'year_level' => fake()->randomElement($years),
            'department' => 'CEIT',
            'qr_raw_text' => null,
            'role_id' => $studentRole->id,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
