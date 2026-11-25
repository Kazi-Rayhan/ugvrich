<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    protected $fillable = ['key', 'title', 'title_bn', 'image'];

    protected static function booted(): void
    {
        static::updated(function (self $signature): void {
            if ($signature->wasChanged('image')) {
                $signature->removeImageAfterCommit($signature->getOriginal('image'));
            }
        });

        static::deleted(fn (self $signature) => $signature->removeImageAfterCommit($signature->image));
    }

    private function removeImageAfterCommit(string $path): void
    {
        $this->getConnection()->afterCommit(fn () => Storage::disk('local')->delete($path));
    }
}
