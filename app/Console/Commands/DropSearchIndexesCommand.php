<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;

class DropSearchIndexesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:drop-search-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop search indexes for users and posts collections';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $usersCollection = \DB::connection('mongodb')->getCollection('users');

        try {
            $usersCollection->dropSearchIndex(User::SEARCH_INDEX);
            $this->info('Dropped search index for users collection.');
        } catch (\Throwable $e) {
            $this->warn('Users search index "' . User::SEARCH_INDEX . '" not found or could not be dropped: ' . $e->getMessage());
        }

        $postsCollection = \DB::connection('mongodb')->getCollection('posts');

        try {
            $postsCollection->dropSearchIndex(Post::SEARCH_INDEX);
            $this->info('Dropped search index for posts collection.');
        } catch (\Throwable $e) {
            $this->warn('Posts search index "' . Post::SEARCH_INDEX . '" not found or could not be dropped: ' . $e->getMessage());
        }
    }
}
