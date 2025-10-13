<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Searchable;

    public static function findByMixedId($id): User|Authenticatable|null
    {
        $user = static::where('_id', $id)->first();

        if (!$user && is_numeric($id)) {
            $user = static::where('_id', (int)$id)->first();
        }

        return $user;
    }


    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('posts');
    }

    public function searchableAs(): string
    {
        return config('scout.prefix') . 'users_index';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // Helper method to get all comments made by this user across all posts
    public function getCommentsAttribute(): Collection
    {
        $userId = is_numeric($this->_id) ? (int)$this->_id : (string)$this->_id;
        $cacheKey = "user:{$userId}:comments";

        return cache()->remember($cacheKey, now()->addHours(1), function () use ($userId) {
            $commentsCollection = collect();

            $posts = Post::whereRaw(['comments.user_id' => $userId])->get();

            foreach ($posts as $post) {
                if (!empty($post->comments)) {
                    foreach ($post->comments as $index => $comment) {
                        // Only include comments by this user
                        if (isset($comment['user_id']) && $comment['user_id'] == $userId) {
                            // Convert to object if it's an array
                            $commentObj = (object)$comment;

                            $commentObj->index = $index;
                            $commentObj->post_id = (string)$post->_id;

                            // Attach the full post object
                            $commentObj->post = $post;

                            // Ensure user object is available
                            $commentObj->user = $this; // Use the current user object

                            // Ensure created_at is a Carbon instance
                            if (isset($comment['created_at']) && !($comment['created_at'] instanceof Carbon)) {
                                $commentObj->created_at = Carbon::parse($comment['created_at']);
                            }

                            $commentsCollection->push($commentObj);
                        }
                    }
                }
            }

            return $commentsCollection;
        });
    }

    // Helper method to get comments count made by this user
    public function getCommentsCountAttribute(): int
    {
        return $this->comments->count();
    }

    // Helper method to get comments on user's own posts
    public function getCommentsOnOwnPostsAttribute(): Collection
    {
        $userId = is_numeric($this->_id) ? (int)$this->_id : (string)$this->_id;

        $cacheKey = "user:{$userId}:comments_on_own_posts";

        return cache()->remember($cacheKey, now()->addHours(1), function () {
            $commentsCollection = collect();
            $userIds = [];

            // Get all posts by the user
            $posts = $this->posts()->get();

            // First pass: collect comments and unique user IDs
            foreach ($posts as $post) {
                if (!empty($post->comments)) {
                    foreach ($post->comments as $index => $comment) {
                        if (isset($comment['user_id'])) {
                            $userIds[] = $comment['user_id'];
                        }

                        // Prepare comment object with post reference
                        $commentObj = (object)$comment;
                        $commentObj->index = $index;
                        $commentObj->post_id = (string)$post->_id;
                        $commentObj->post = $post;

                        // Handle created_at date
                        if (isset($comment['created_at']) && !($comment['created_at'] instanceof Carbon)) {
                            $commentObj->created_at = Carbon::parse($comment['created_at']);
                        }

                        $commentsCollection->push($commentObj);
                    }
                }
            }

            // Batch load all users in one query to prevent N+1 problem
            $uniqueUserIds = array_unique($userIds);
            $users = User::whereIn('_id', $uniqueUserIds)->get()->keyBy(function ($user) {
                return (string)$user->_id;
            });

            // Second pass: attach user objects to comments
            foreach ($commentsCollection as $commentObj) {
                $commentUserId = $commentObj->user_id ?? null;
                $commentObj->user = $commentUserId ? ($users[(string)$commentUserId] ?? null) : null;
            }

            return $commentsCollection;
        });
    }

    // Helper method to get comments count on user's own posts
    public function getCommentsOnOwnPostsCountAttribute(): int
    {
        return $this->commentsOnOwnPosts->count();
    }

    public function clearCommentsCache(): void
    {
        cache()->forget("user:{$this->_id}:comments");
        cache()->forget("user:{$this->_id}:comments_on_own_posts");
    }

}
