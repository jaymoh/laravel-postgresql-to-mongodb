<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;

class PostTableSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::truncate();

        $users = User::all();

        if ($users->isEmpty()) {
            User::factory()->count(10)->create();
            $users = User::all();
        }

        // Create first post with embedded comments
        Post::create([
            'title' => 'Laravel and MongoDB: A Practical Migration Guide',
            'body' => "In this tutorial, we will take a sample Laravel blog app (with users, posts, and comments) that's running on PostgreSQL and migrate it to MongoDB. Along the way, you'll see how to remodel your data from tables into documents, and how this change affects querying, relationships, and performance. Additionally, we'll explore how MongoDB Atlas can double as both your database and your search engine—cutting out the need for third-party tools like ElasticSearch, to handle full-text search needs.",
            'user_id' => $users->first()->id,
            'owner_name' => $users->first()->name,
            'comments' => [
                [
                    'body' => 'Great tutorial! Very helpful for understanding the migration process.',
                    'user_id' => $users->random()->id,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ],
                [
                    'body' => 'I was looking for something like this. Thank you!',
                    'user_id' => $users->random()->id,
                    'created_at' => now()->subDays(1),
                    'updated_at' => now()->subDays(1),
                ],
            ],
        ]);

        // Create 50 posts with random comments embedded
        Post::factory()->count(50)->make()->each(function ($post) use ($users) {
            $postOwner = $users->random();
            $post->user_id = $postOwner->id;
            $post->owner_name = $postOwner->name;

            // Generate 0-5 random comments for each post
            $commentCount = rand(0, 5);
            $comments = [];

            for ($i = 0; $i < $commentCount; $i++) {
                $comments[] = [
                    'body' => fake()->paragraph(),
                    'user_id' => $users->random()->id,
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ];
            }

            $post->comments = $comments;
            $post->save();
        });
    }
}
