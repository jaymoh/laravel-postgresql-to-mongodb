@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- User Profile Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">{{ $user->name }}</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="text-muted mb-2"><strong>Member since:</strong> {{ $user->created_at->format('F d, Y') }}
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-primary me-2">{{ $user->posts->count() }} Posts</span>
                        <span class="badge bg-success me-2">{{ $user->comments_count }} Comments</span>
                        <span class="badge bg-info">{{ $user->comments_on_own_posts_count }} Comments on Posts</span>
                    </div>
                </div>
            </div>

            <!-- User's Posts -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Posts ({{ $user->posts->count() }})</h5>
                </div>
                <div class="card-body">
                    @forelse($user->posts as $post)
                    <div class="mb-3 pb-3 border-bottom">
                        <h6>
                            <a href="{{ route('posts.show', $post->id) }}" class="text-decoration-none">
                                {{ $post->title }}
                            </a>
                        </h6>
                        <p class="text-muted small mb-1">{{ $post->created_at->format('F d, Y') }}</p>
                        <p class="mb-0 text-truncate">{{ Str::limit($post->body, 150) }}</p>
                    </div>
                    @empty
                    <p class="text-muted">No posts yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Comments on User's Posts -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Comments on Posts ({{ $user->commentsOnOwnPosts->count() }})</h5>
                </div>
                <div class="card-body">
                    @forelse($user->commentsOnOwnPosts as $comment)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $comment->user->name }} </strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                <span class="text-muted"> on </span>
                                <a href="{{ route('posts.show', $comment->post_id) }}" class="text-decoration-none">
                                    {{ $comment->post->title }}
                                </a>
                            </div>
                            @auth
                            @if(Auth::id() === $comment->user_id)
                            <form action="{{ route('comments.destroy', [$comment->post_id, $comment->index]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this comment?')">
                                    Delete
                                </button>
                            </form>
                            @endif
                            @endauth
                        </div>
                        <p class="mt-2 mb-0">{{ $comment->body }}</p>
                    </div>
                    @empty
                    <p class="text-muted">No comments on posts yet.</p>
                    @endforelse
                </div>
            </div>


            <div class="mt-3">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
            </div>
        </div>
    </div>
</div>
@endsection
