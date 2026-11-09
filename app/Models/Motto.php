<?php

namespace App\Models;

use Database\Factories\MottoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'sort_order', 'is_active'])]
class Motto extends Model
{
    /** @use HasFactory<MottoFactory> */
    use HasFactory;

    /** Laravel akan mempluralkan "Motto" menjadi "mottoes", sedangkan tabelnya "mottos". */
    protected $table = 'mottos';

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
