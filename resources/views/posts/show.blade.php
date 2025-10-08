@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Post Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $post->title }}</h4>
                    @auth
                        @if(auth()->id() === $post->user_id)
                            <div>
                                <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('posts.destroy', $post->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        By <strong>{{ $post->user->name }}</strong> on {{ $post->created_at->format('F d, Y') }}
                    </p>
                    <div class="mt-3">
                        {{ $post->body }}
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Comments ({{ $post->comments->count() }})</h5>
                </div>
                <div class="card-body">
                    @forelse($post->comments as $comment)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $comment->user->name }}</strong>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                @auth
                                    @if(auth()->id() === $comment->user_id)
                                        <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                            <p class="mt-2 mb-0">{{ $comment->body }}</p>
                        </div>
                    @empty
                        <p class="text-muted">No comments yet. Be the first to comment!</p>
                    @endforelse

                    <!-- Add Comment Form -->
                    @auth
                        <div class="mt-4">
                            <h6>Add a Comment</h6>
                            <form action="{{ route('comments.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="post_id" value="{{ $post->id }}">
                                <div class="mb-3">
                                    <textarea class="form-control @error('body') is-invalid @enderror"
                                              name="body"
                                              rows="3"
                                              placeholder="Write your comment..."
                                              required>{{ old('body') }}</textarea>
                                    @error('body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-info mt-4">
                            Please <a href="{{ route('login') }}">login</a> to leave a comment.
                        </div>
                    @endauth
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('posts.index') }}" class="btn btn-secondary">Back to Posts</a>
            </div>
        </div>
    </div>
</div>
@endsection
