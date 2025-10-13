<?php

namespace App\Http\Controllers;

use App\Models\User;
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

            $posts->getCollection()->load('user');
        } else {
            $posts = Post::query()
                ->with('user')
                ->latest()
                ->paginate(10)
                ->withQueryString();
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
            'comments' => [], // Initialize comments as an empty array
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
        $post = Post::findByMixedId($id);

        if (!$post) {
            abort(404);
        }

        // Load the post author's information
        $post->load('user');

        // Handle embedded comments and their users efficiently
        if (count($post->comments) > 0) {
            // Extract all unique user IDs from comments
            $userIds = $post->comments->pluck('user_id')->unique()->filter()->toArray();

            // Fetch all required users in a single query
            $users = User::whereIn('_id', $userIds)->get()->keyBy('_id');

            $comments = $post->comments;

            // Map the comments collection to attach user objects
            $comments = $comments->map(function($comment, $index) use ($users, $post) {
                // Decode if comment is a JSON string
                $commentData = is_string($comment) ? json_decode($comment, true) : $comment;
                // Convert the comment array to an object
                $commentObj = (object) $commentData;
                $commentObj->index = $index; // Add index for reference when deleting
                $commentObj->post_id = $post->id;
                if (isset($commentData['user_id']) && $users->has($commentData['user_id'])) {
                    $commentObj->user = $users[$commentData['user_id']];
                }
                return $commentObj;
            });

            // Replace the comments with the enriched version
            $post->commentObjects = $comments;
        } else {
            $post->commentObjects = collect();
        }

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
        $post = Post::findByMixedId($id);

        if (!$post) {
            abort(404, 'Post not found.');
        }

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
        $post = Post::findByMixedId($id);

        if (!$post) {
            abort(404, 'Post not found.');
        }

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
        $post = Post::findByMixedId($id);

        if (!$post) {
            abort(404, 'Post not found.');
        }

        // Ensure the authenticated user is the owner of the post
        if ($post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}
