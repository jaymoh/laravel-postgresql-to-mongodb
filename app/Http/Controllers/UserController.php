<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->filled('q')) {
            $term = $request->input('q');
            $users = User::search($term)->latest()->paginate(10)->withQueryString();
        } else {
            $users = User::latest()->paginate(10)->withQueryString();
        }

        // For both search and non-search cases, manually add the counts
        $users->getCollection()->transform(function ($user) {
            $user->posts_count = $user->posts()->count();
            $user->comments_count = $user->commentsCount; // Using the accessor defined in User model
            return $user;
        });

        // Return JSON for API requests, otherwise render the users index view
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
