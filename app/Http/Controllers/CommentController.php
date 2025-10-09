<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'body' => 'required|string|max:1000',
        ]);

        $comment = auth()->user()->comments()->create($validated);

        return redirect()->route('posts.show', $validated['post_id'])
            ->with('success', 'Comment posted successfully!');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Ensure the user owns the comment
        if (auth()->id() !== $comment->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $postId = $comment->post_id;
        $comment->delete();

        return redirect()->route('posts.show', $postId)
            ->with('success', 'Comment deleted successfully!');
    }

}
