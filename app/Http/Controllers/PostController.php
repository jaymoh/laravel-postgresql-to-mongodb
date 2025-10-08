<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse |\Illuminate\Http\Response |\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        if ($request->filled('q')) {
            $term = $request->input('q');
            $posts = Post::search($term)->latest()->paginate(10)->withQueryString();
            $posts->getCollection()->load('user')->loadCount('comments');
        } else {
            $query = Post::query();
            $query = $query->with('user')->withCount('comments');
            $posts = $query->latest()->paginate(10)->withQueryString();
        }

        // Return JSON for API requests, otherwise render the posts index view
        if ($request->wantsJson()) {
            return response()->json($posts);
        }

        return view('posts.index', compact('posts'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Create the post with the authenticated user's ID
        $post = Post::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        // Return JSON response for API requests
        if ($request->wantsJson()) {
            return response()->json($post->load('user'), 201);
        }

        // Redirect to the post's show page with success message
        return redirect()->route('posts.show', $post->id)
            ->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function show($id)
    {
        $post = Post::with('user', 'comments.user')->findOrFail($id);

        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function edit($id)
    {
        $post = Post::findOrFail($id);

        return view('posts.edit', compact('post'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Ensure the authenticated user is the owner of the post
        if ($post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Validate the request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Update the post with validated data
        $post->update($validated);

        // Return JSON response for API requests
        if ($request->wantsJson()) {
            return response()->json($post->load('user'));
        }

        // Redirect to the post's show page with success message
        return redirect()->route('posts.show', $post->id)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Ensure the authenticated user is the owner of the post
        if ($post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}
