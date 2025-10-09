<?php

namespace App\Http\Controllers;

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
            $users->getCollection()->loadCount('posts', 'comments');
        } else {
            $query = User::query();
            $query = $query->withCount('posts', 'comments');
            $users = $query->latest()->paginate(10)->withQueryString();
        }

        // Return JSON for API requests, otherwise render the users index view
        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return view('users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::with('posts', 'commentsOnOwnPosts')
            ->withCount('posts', 'comments', 'commentsOnOwnPosts')
            ->findOrFail($id);

        return view('users.show', compact('user'));
    }
}
