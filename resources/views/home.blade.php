@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Welcome, Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Welcome back, {{ Auth::user()->name }}!</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Member since {{ Auth::user()->created_at->format('F d, Y') }}</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-primary">{{ Auth::user()->posts()->count() }}</h2>
                            <p class="text-muted mb-0">Your Posts</p>
                            <a href="{{ route('users.show', Auth::id()) }}" class="btn btn-sm btn-outline-primary mt-2">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-success">{{ Auth::user()->comments_count }}</h2>
                            <p class="text-muted mb-0">Comments Made</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-info">{{ Auth::user()->comments_on_own_posts_count }}</h2>
                            <p class="text-muted mb-0">Comments Received</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="{{ route('posts.create') }}" class="btn btn-primary">Create New Post</a>
                        <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary">Browse Posts</a>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Browse Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
