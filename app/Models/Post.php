<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JeroenG\Explorer\Application\Explored;
use Laravel\Scout\Searchable;

class Post extends Model implements Explored
{
    use HasFactory, Searchable;

    protected $fillable = ['title', 'body', 'user_id'];

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at,
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('user')->withCount('comments');
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'posts_index';
    }

    public function mappableAs(): array
    {
        return [
            'id' => 'keyword',
            'title' => 'text',
            'created_at' => 'date',
            'user' => [
                'name' => 'text',
                'email' => 'keyword',
            ],
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
