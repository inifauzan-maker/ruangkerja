<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAttachments
{
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')
            ->where('is_current', true)
            ->oldest();
    }

    public function allAttachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->oldest();
    }

    public static function bootHasAttachments(): void
    {
        static::deleting(function (self $model): void {
            $model->allAttachments()->get()->each->delete();
        });
    }
}
