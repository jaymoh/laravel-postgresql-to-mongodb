<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;
use MongoDB\Laravel\Eloquent\Model;

class Post extends Model
{
    use HasFactory, Searchable;

    public static function findByMixedId($id): Post|null
    {
        $post = static::where('_id', $id)->first();

        if (!$post && is_numeric($id)) {
            $post = static::where('_id', (int)$id)->first();
        }

        return $post;
    }

    protected $fillable = ['title', 'body', 'user_id', 'comments'];

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('user');
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return config('scout.prefix') . 'posts_index';
    }

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
