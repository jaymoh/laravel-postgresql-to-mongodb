<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use MongoDB\Builder\Search;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        if ($request->filled('q')) {
            $term = $request->input('q');
            $perPage = $request->input('perPage', 10);
            $page = $request->input('page', 1);
            $skip = ($page - 1) * $perPage;

            // Cache the total count for the search term
            $keyTerm = preg_replace('/\s+/', '_', strtolower($term));

            $total = Cache::remember("search_total_{$keyTerm}_users_search_index", 3600, function () use ($term) {
                return User::search(
                    operator: Search::text(
                        path: ['name', 'email'],
                        query: $term
                    ),
                    index: User::SEARCH_INDEX
                )->count();
            });

            // Get paginated results using aggregation pipeline
            $rawResults = User::aggregate()
                ->search(Search::text(path: ['name', 'email'], query: $term), index: User::SEARCH_INDEX)
                ->sort(created_at: -1)
                ->skip($skip)
                ->limit($perPage)
                ->get();

            // Hydrate results into models
            $results = User::hydrate($rawResults->toArray());

            // Create Laravel paginator
            $users = new LengthAwarePaginator(
                $results,
                $total,
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query()
                ]
            );
        } else {
            $users = User::query()
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }

        // Get user IDs
        $userIds = $users->pluck('_id')->toArray();

        // Run a native MongoDB aggregation on the `posts` collection
        // using MongoDB's aggregation pipeline to group by user_id and count posts
        $postsCounts = Post::raw(function ($collection) use ($userIds) {
            return $collection->aggregate([
                [
                    '$match' => ['user_id' => ['$in' => $userIds]]
                ],
                [
                    '$group' => [
                        '_id' => '$user_id',
                        'count' => ['$sum' => 1]
                    ]
                ]
            ]);
        })
            ->pluck('count', '_id')->toArray();

        // Get comments counts
        $commentsCounts = Post::raw(function ($collection) use ($userIds) {
            return $collection->aggregate([
                [
                    '$match' => ['comments.user_id' => ['$in' => $userIds]]
                ],
                [
                    '$unwind' => '$comments'
                ],
                [
                    '$match' => ['comments.user_id' => ['$in' => $userIds]]
                ],
                [
                    '$group' => [
                        '_id' => '$comments.user_id',
                        'count' => ['$sum' => 1]
                    ]
                ]
            ]);
        })->pluck('count', '_id')->toArray();

        // Transform the collection
        $users->getCollection()->transform(function ($user) use ($postsCounts, $commentsCounts) {
            $userId = (string)$user->_id;
            $user->posts_count = $postsCounts[$userId] ?? 0;
            $user->comments_count = $commentsCounts[$userId] ?? 0;
            return $user;
        });

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return view('users.index', compact('users'));
    }


    public function show($id)
    {
        $user = User::findByMixedId($id);

        if (!$user) {
            abort(404);
        }

        // Eager load posts to avoid N+1 queries
        $user->load('posts');

        return view('users.show', compact('user'));
    }
}
