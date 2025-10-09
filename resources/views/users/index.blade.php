{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Users</h1>
        <div class="d-flex align-items-center">
            <form class="form-inline d-flex align-items-center" method="GET" action="{{ url()->current() }}">
                <input type="search" name="q" class="form-control me-2" placeholder="Search..." value="{{ request('q') }}" oninput="if(this.value === '') this.form.submit();">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    @if($users->count())
    <div class="list-group">
        @foreach($users as $user)
        <a href="{{ route('users.show', $user->id ?? $user) }}" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">{{ $user->name }}</h5>
                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1 text-muted">{{ $user->email }}</p>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">
                    <i class="bi bi-file-text"></i> {{ $user->posts_count }} {{ Str::plural('post', $user->posts_count) }}
                </small>
                <small class="text-muted">
                    <i class="bi bi-chat"></i> {{ $user->comments_count }} {{ Str::plural('comment', $user->comments_count) }}
                </small>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="alert alert-info">No users found.</div>
    @endif
</div>
@endsection
