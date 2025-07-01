<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Represents a single file within a rachma's files collection
 * This is a virtual model that works with the JSON files column
 */
class RachmaFile extends Model
{
    // This model doesn't have its own table - it's stored as JSON in rachmat.files
    protected $table = null;

    protected $fillable = [
        'id',
        'path',
        'original_name',
        'format',
        'size',
        'is_primary',
        'uploaded_at',
        'description'
    ];

    protected $casts = [
        'size' => 'integer',
        'is_primary' => 'boolean',
        'uploaded_at' => 'datetime'
    ];

    // Supported file formats for rachma files
    public const SUPPORTED_FORMATS = [
        'DST' => 'Tajima DST',
        'PES' => 'Brother PES',
        'JEF' => 'Janome JEF',
        'EXP' => 'Melco EXP',
        'VP3' => 'Husqvarna VP3',
        'XXX' => 'Singer XXX',
        'HUS' => 'Husqvarna HUS',
        'VIP' => 'Pfaff VIP',
        'SEW' => 'Janome SEW',
        'CSD' => 'Singer CSD',
        'PDF' => 'PDF Pattern',
        'ZIP' => 'Archive File',
        'RAR' => 'Archive File'
    ];

    /**
     * Get the file size in bytes
     */
    public function getFileSize(): ?int
    {
        if ($this->size) {
            return $this->size;
        }

        if ($this->path && Storage::disk('private')->exists($this->path)) {
            $filePath = Storage::disk('private')->path($this->path);
            return filesize($filePath);
        }

        return null;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->getFileSize();

        if (!$bytes) {
            return 'Unknown';
        }

        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Check if file exists on disk
     */
    public function exists(): bool
    {
        return $this->path && Storage::disk('private')->exists($this->path);
    }

    /**
     * Get the full file path
     */
    public function getFullPath(): ?string
    {
        if (!$this->path) {
            return null;
        }

        return Storage::disk('private')->path($this->path);
    }

    /**
     * Get file format description
     */
    public function getFormatDescription(): string
    {
        return self::SUPPORTED_FORMATS[$this->format] ?? $this->format;
    }

    /**
     * Check if this is an embroidery format
     */
    public function isEmbroideryFormat(): bool
    {
        $embroideryFormats = ['DST', 'PES', 'JEF', 'EXP', 'VP3', 'XXX', 'HUS', 'VIP', 'SEW', 'CSD'];
        return in_array($this->format, $embroideryFormats);
    }

    /**
     * Check if this is an archive format
     */
    public function isArchiveFormat(): bool
    {
        return in_array($this->format, ['ZIP', 'RAR']);
    }

    /**
     * Get download URL for admin
     */
    public function getDownloadUrl(int $rachmaId): string
    {
        return route('admin.rachmat.download-file-by-id', ['rachma' => $rachmaId, 'fileId' => $this->id]);
    }

    /**
     * Create a new RachmaFile instance from array data
     */
    public static function fromArray(array $data): self
    {
        $file = new self();
        $file->fill($data);
        return $file;
    }

    /**
     * Convert to array for JSON storage
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'original_name' => $this->original_name,
            'format' => $this->format,
            'size' => $this->getFileSize(),
            'is_primary' => $this->is_primary,
            'uploaded_at' => $this->uploaded_at?->toISOString(),
            'description' => $this->description
        ];
    }
}
