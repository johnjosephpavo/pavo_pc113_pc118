<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * The name of the corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 2)->inRandomOrder()->first()?->id ?? User::factory()->create(['role' => 2])->id,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'age' => $this->faker->numberBetween(18, 25), // Generates a random age between 18 and 25
            'gender' => $this->faker->randomElement(['male', 'female']), // Random gender
            'address' => $this->faker->address(), // Generates a random address
            'contact_number' => $this->faker->phoneNumber(), // Generates a random phone number
            'course' => $this->faker->randomElement(['BSIT', 'BSCS', 'BSBA', 'BSED', 'BSN']), // Sample courses
        ];
    }
}
