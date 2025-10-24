<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use MongoDB\Laravel\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public const SEARCH_INDEX = 'posts_search_index';

    public static function findByMixedId($id): Post|null
    {
        $post = static::where('_id', $id)->first();

        if (!$post && is_numeric($id)) {
            $post = static::where('_id', (int)$id)->first();
        }

        return $post;
    }

    protected $fillable = ['title', 'body', 'user_id', 'comments', 'owner_name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to get comments count
    public function getCommentsCountAttribute(): int
    {
        return count($this->comments ?? []);
    }

    // Helper method to get comments as a collection
    public function getCommentsAttribute($value): Collection
    {
        return collect($value ?? []);
    }
}
