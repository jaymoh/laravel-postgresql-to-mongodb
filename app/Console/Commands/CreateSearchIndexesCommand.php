<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateSearchIndexesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-search-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create search indexes for users and posts collections';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $collection = DB::connection('mongodb')->getCollection('users');

        $collection->createSearchIndex(
            ['mappings' => ['dynamic' => true]],
            ['name' => User::SEARCH_INDEX]
        );

        $collection = DB::connection('mongodb')->getCollection('posts');

        $collection->createSearchIndex(
            ['mappings' => ['dynamic' => true]],
            ['name' => Post::SEARCH_INDEX]
        );
    }
}
