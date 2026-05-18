<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'reporter_id',
        'content_id',
        'showcase_id',
        'processed_by',
        'reason',
        'description',
        'status',
        'action_taken',
        'admin_note',
        'processed_at',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function showcase()
    {
        return $this->belongsTo(ShowcaseItem::class, 'showcase_id');
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'copyright' => 'Copyright infringement',
            'inappropriate' => 'Inappropriate content',
            'misleading' => 'Misleading content',
            'spam' => 'Spam or scam',
            'privacy' => 'Privacy violation',
            'other' => 'Other',
            default => ucfirst($this->reason),
        };
    }
}
