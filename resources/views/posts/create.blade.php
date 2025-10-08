@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Post</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('posts.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="body" class="form-label">Body</label>
                            <textarea class="form-control @error('body') is-invalid @enderror"
                                      id="body"
                                      name="body"
                                      rows="8"
                                      required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('posts.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Post</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
