<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Create a user for this task if no user is provided
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement(['to-do', 'in-progress', 'done']),
            'is_published' => $this->faker->boolean(),
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Configure the model factory to create tasks with 'to-do' status.
     *
     * @return $this
     */
    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'to-do',
        ]);
    }

    /**
     * Configure the model factory to create tasks with 'in-progress' status.
     *
     * @return $this
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in-progress',
        ]);
    }

    /**
     * Configure the model factory to create tasks with 'done' status.
     *
     * @return $this
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
        ]);
    }

    /**
     * Configure the model factory to create published tasks.
     *
     * @return $this
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    /**
     * Configure the model factory to create unpublished tasks.
     *
     * @return $this
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
} 