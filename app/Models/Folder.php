<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    // protected $table = 'tb_folder';

    protected $fillable = [
        'seller_id',
        'parent_id',
        'license_id',
        'folder_name',
        'folder_description',
        'visibility',
        'is_bundle',
        'bundle_price',
        'allow_individual_sale',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'is_bundle' => 'boolean',
            'allow_individual_sale' => 'boolean',
            'bundle_price' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function license()
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'folder_id');
    }

    public function updateVisibilityRecursive(string $visibility): void
    {
        // Update Visibilitas Folder ini
        $this->visibility = $visibility;
        $this->save();

        // Update semua konten di folder ini
        $this->contents()->update(['visibility' => $visibility,]);

        // Rekursif ke seluruh subfolder
        foreach ($this->children as $child) {
            $child->updateVisibilityRecursive($visibility);
        }
    }

    public function updateStatusRecursive(string $status): void
    {
        // update folder saat ini
        $this->update([
            'status' => $status,
        ]);

        // update semua content di folder ini
        $this->contents()->update([
            'status' => $status,
        ]);

        // update semua subfolder dan isinya
        foreach ($this->children as $child) {
            $child->updateStatusRecursive($status);
        }
    }

    public function hasDirectSingleSaleContent(): bool
    {
        return $this->contents()
            ->where('sale_type', 'single_sale')
            ->exists();
    }

    public function hasPurchasableBundleContents(): bool
    {
        return $this->contents()
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->where('sale_type', 'multi_sale')
            ->where('sale_status', 'available')
            ->exists();
    }

    public function getBundleContents()
    {
        return $this->contents()
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->where('sale_type', 'multi_sale')
            ->where('sale_status', 'available')
            ->get();
    }

    public function syncDirectContentLicenses(): void
    {
        if (! $this->is_bundle || ! $this->license_id) {
            return;
        }

        $this->contents()->update([
            'license_id' => $this->license_id,
        ]);
    }
}
