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
        $postsCollection = \DB::connection('mongodb')->getCollection('posts');

        $usersCollection->dropSearchIndex(User::SEARCH_INDEX);
        $postsCollection->dropSearchIndex(Post::SEARCH_INDEX);
    }
}
