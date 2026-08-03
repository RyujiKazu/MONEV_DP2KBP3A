<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        return [
            'nama_lengkap' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'PKK',
            'remember_token' => Str::random(10),
            'created_at' => now(),
        ];
    }

    /** Create an administrator account. */
    public function admin(): static
    {
        return $this->state(fn (): array => ['role' => User::ROLE_ADMIN]);
    }

    /** Create a PKK account. */
    public function pkk(): static
    {
        return $this->state(fn (): array => ['role' => User::ROLE_PKK]);
    }
}
