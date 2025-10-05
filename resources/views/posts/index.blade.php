{{-- resources/views/posts/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Posts</h1>
        <form class="form-inline" method="GET" action="{{ url()->current() }}">
            <input type="search" name="q" class="form-control mr-2" placeholder="Search..." value="{{ request('q') }}">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    @if($posts->count())
    <div class="list-group">
        @foreach($posts as $post)
        <a href="{{ route('posts.show', $post->id ?? $post) }}" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">{{ $post->title }}</h5>
                <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1 text-muted">{{ \Illuminate\Support\Str::limit($post->body, 150) }}</p>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">
                    <i class="bi bi-person"></i> By {{ $post->user->name ?? 'Unknown' }}
                </small>
                <small class="text-muted">
                    <i class="bi bi-chat"></i> {{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}
                </small>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-3">
        {{ $posts->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="alert alert-info">No posts found.</div>
    @endif
</div>
@endsection
