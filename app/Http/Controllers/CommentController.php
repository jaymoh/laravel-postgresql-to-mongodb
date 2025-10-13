<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required',
            'body' => 'required|string|max:1000',
        ]);

        $post = Post::findByMixedId($validated['post_id']);

        if (!$post) {
            abort(404, 'Post not found.');
        }

        $comment = [
            'body' => $validated['body'],
            'user_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add comment to the embedded comments array
        $post->push('comments', $comment);

        return redirect()->route('posts.show', $validated['post_id'])
            ->with('success', 'Comment posted successfully!');
    }

    public function update(Request $request, $commentIndex)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
            'post_id' => 'required',
        ]);

        // Find the post by its ID
        $post = Post::findByMixedId($validated['post_id']);

        if (!$post) {
            abort(404, 'Post not found.');
        }

        // Get the comments array
        $comments = $post->comments->toArray();

        if (!isset($comments[$commentIndex])) {
            abort(404, 'Comment not found.');
        }

        // Update the comment body
        $comments[$commentIndex]['body'] = $validated['body'];
        $comments[$commentIndex]['updated_at'] = now();

        // Save the updated comments array back to the post
        $post->comments = $comments;
        $post->save();
    }

    public function destroy($postId, $commentIndex)
    {
        $post = Post::findByMixedId($postId);

        if (!$post) {
            abort(404, 'Post not found.');
        }

        // Get the comment to check ownership
        $comments = $post->comments->toArray();

        if (!isset($comments[$commentIndex])) {
            abort(404, 'Comment not found.');
        }

        $comment = $comments[$commentIndex];

        // Ensure the user owns the comment
        if (auth()->id() !== $comment['user_id']) {
            abort(403, 'Unauthorized action.');
        }

        // Remove comment from the embedded array
        $post->pull('comments', $comment);

        // Re-index comments array properly
        $commentsArray = array_values($post->comments->toArray());
        $post->comments = $commentsArray;
        $post->save();

        // Clear relevant caches
        auth()->user()->clearCommentsCache();

        return redirect()->route('posts.show', $postId)
            ->with('success', 'Comment deleted successfully!');
    }
}
