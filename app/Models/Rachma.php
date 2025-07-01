<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Rachma extends Model
{
    use HasFactory;

    protected $table = 'rachmat';

    protected $fillable = [
        'designer_id',
      
        'title_ar',
        'title_fr',
       
        'description_ar',
        'description_fr',
        'file_path',
        'files',
        'preview_images',
    
        'width',
        'height',
        'gharazat',
        'color_numbers',
        'price',
        'original_price',
        
        'average_rating',
        'ratings_count',
    ];

    protected $casts = [
        'preview_images' => 'array',
        'files' => 'array',
        'color_numbers' => 'array',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    protected $appends = [
        'preview_image_urls',
        'title',
        'description',
    ];

    /**
     * Get the preview images URLs
     */
    public function getPreviewImageUrlsAttribute(): array
    {
        if (!$this->preview_images || !is_array($this->preview_images)) {
            return [];
        }

        return array_map(function ($imagePath) {
            // If it's already a full URL, return as is
            if (str_starts_with($imagePath, 'http')) {
                return $imagePath;
            }

            // If it starts with storage/, return as is (already has storage prefix)
            if (str_starts_with($imagePath, 'storage/')) {
                return asset($imagePath);
            }

            // The image path already includes the directory structure (e.g., "images/preview/filename.jpg")
            return asset("storage/{$imagePath}");
        }, $this->preview_images);
    }

    /**
     * Get all files as RachmaFile instances
     */
    public function getFilesAttribute(): array
    {
        $filesData = $this->attributes['files'] ?? null;

        if (!$filesData) {
            // Fallback to single file_path for backward compatibility
            if ($this->file_path) {
                return [$this->createFileFromPath($this->file_path)];
            }
            return [];
        }

        if (is_string($filesData)) {
            $filesData = json_decode($filesData, true);
        }

        if (!is_array($filesData)) {
            return [];
        }

        return array_map(function ($fileData) {
            return RachmaFile::fromArray($fileData);
        }, $filesData);
    }

    /**
     * Get the primary file
     */
    public function getPrimaryFile(): ?RachmaFile
    {
        $files = $this->files;

        foreach ($files as $file) {
            if ($file->is_primary) {
                return $file;
            }
        }

        // If no primary file is marked, return the first file
        return $files[0] ?? null;
    }

    /**
     * Get files by format
     */
    public function getFilesByFormat(string $format): array
    {
        return array_filter($this->files, function ($file) use ($format) {
            return strtoupper($file->format) === strtoupper($format);
        });
    }

    /**
     * Get all embroidery format files
     */
    public function getEmbroideryFiles(): array
    {
        return array_filter($this->files, function ($file) {
            return $file->isEmbroideryFormat();
        });
    }

    /**
     * Check if rachma has files
     */
    public function hasFiles(): bool
    {
        return count($this->files) > 0;
    }

    /**
     * Get total size of all files
     */
    public function getTotalFileSize(): int
    {
        $totalSize = 0;
        foreach ($this->files as $file) {
            $totalSize += $file->getFileSize() ?? 0;
        }
        return $totalSize;
    }

    /**
     * Get formatted total file size
     */
    public function getFormattedTotalSize(): string
    {
        $bytes = $this->getTotalFileSize();

        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get the designer that owns the rachma.
     */
    public function designer(): BelongsTo
    {
        return $this->belongsTo(Designer::class);
    }

    /**
     * Get the categories that belong to the rachma (many-to-many).
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'rachma_categories');
    }

    /**
     * Get the orders for the rachma.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the ratings for the rachma.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'target_id')->where('target_type', 'rachma');
    }

    /**
     * Get the ratings using polymorphic relationship.
     */
    public function ratingsPolymorphic()
    {
        return $this->morphMany(Rating::class, 'ratable', 'target_type', 'target_id');
    }

    /**
     * Get the comments for the rachma.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'target_id')->where('target_type', 'rachma');
    }

    /**
     * Get the parts for the rachma.
     */
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class)->orderBy('order');
    }

    /**
     * Get the title based on the current locale.
     */
    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"title_$locale"} ?? $this->title_ar ?? $this->title;
    }

    /**
     * Get the description based on the current locale.
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $this->{"description_$locale"} ?? $this->description_ar ?? $this->description;
    }

    /**
     * Get the title attribute (defaults to Arabic, then original title).
     */
    public function getTitleAttribute(): string
    {
        return $this->attributes['title_ar'] ?? $this->attributes['title'];
    }

    /**
     * Get the description attribute (defaults to Arabic, then original description).
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->attributes['description_ar'] ?? $this->attributes['description'];
    }

    /**
     * Get formatted size from width and height.
     */
    public function getFormattedSizeAttribute(): string
    {
        if ($this->width && $this->height) {
            return "{$this->width} x {$this->height} cm";
        }
        
        return $this->size ?? 'غير محدد';
    }

    /**
     * Get the dimensions as an array.
     */
    public function getDimensionsAttribute(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    /**
     * Scope to get only active rachmat
     */
    public function scopeActive($query)
    {
        // All rachmat are considered active since is_active field was removed
        return $query;
    }

    /**
     * Scope to filter by category (now uses many-to-many relationship)
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    /**
     * Scope to filter by multiple categories
     */
    public function scopeByCategories($query, array $categoryIds)
    {
        return $query->whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        });
    }

    /**
     * Scope to filter by size
     */
    public function scopeBySize($query, $size)
    {
        return $query->where('size', $size);
    }

    /**
     * Scope to filter by gharazat range
     */
    public function scopeByGharazatRange($query, $min, $max)
    {
        return $query->whereBetween('gharazat', [$min, $max]);
    }

    /**
     * Update average rating
     */
    public function updateAverageRating()
    {
        $ratings = $this->ratings();
        $this->average_rating = $ratings->avg('rating') ?? 0;
        $this->ratings_count = $ratings->count();
        $this->save();
    }

    

    /**
     * Add a new file to the rachma
     */
    public function addFile(string $path, string $originalName, string $format, ?string $description = null, bool $isPrimary = false): RachmaFile
    {
        $files = $this->files;

        // If this is set as primary, unmark other primary files
        if ($isPrimary) {
            foreach ($files as $file) {
                $file->is_primary = false;
            }
        }

        // Create new file
        $newFile = RachmaFile::fromArray([
            'id' => $this->getNextFileId(),
            'path' => $path,
            'original_name' => $originalName,
            'format' => strtoupper($format),
            'size' => null, // Will be calculated when accessed
            'is_primary' => $isPrimary || empty($files), // First file is automatically primary
            'uploaded_at' => now(),
            'description' => $description ?? "File in {$format} format"
        ]);

        // Add to files array
        $files[] = $newFile;

        // Update the model
        $this->updateFilesArray($files);

        return $newFile;
    }

    /**
     * Remove a file from the rachma
     */
    public function removeFile(int $fileId): bool
    {
        $files = $this->files;
        $fileIndex = null;

        foreach ($files as $index => $file) {
            if ($file->id == $fileId) {
                $fileIndex = $index;
                break;
            }
        }

        if ($fileIndex === null) {
            return false;
        }

        $removedFile = $files[$fileIndex];

        // Delete the physical file
        if ($removedFile->exists()) {
            Storage::disk('private')->delete($removedFile->path);
        }

        // Remove from array
        unset($files[$fileIndex]);
        $files = array_values($files); // Re-index array

        // If we removed the primary file, make the first remaining file primary
        if ($removedFile->is_primary && !empty($files)) {
            $files[0]->is_primary = true;
        }

        // Update the model
        $this->updateFilesArray($files);

        return true;
    }

    /**
     * Set a file as primary
     */
    public function setPrimaryFile(int $fileId): bool
    {
        $files = $this->files;
        $found = false;

        foreach ($files as $file) {
            if ($file->id == $fileId) {
                $file->is_primary = true;
                $found = true;
            } else {
                $file->is_primary = false;
            }
        }

        if ($found) {
            $this->updateFilesArray($files);
        }

        return $found;
    }

    /**
     * Get the next available file ID
     */
    private function getNextFileId(): int
    {
        $files = $this->files;
        $maxId = 0;

        foreach ($files as $file) {
            if ($file->id > $maxId) {
                $maxId = $file->id;
            }
        }

        return $maxId + 1;
    }

    /**
     * Update the files array in the database
     */
    private function updateFilesArray(array $files): void
    {
        $filesData = array_map(function ($file) {
            return $file->toArray();
        }, $files);

        $this->update(['files' => $filesData]);
    }

    /**
     * Create a RachmaFile from a file path (for backward compatibility)
     */
    private function createFileFromPath(string $filePath): RachmaFile
    {
        $pathInfo = pathinfo($filePath);
        $extension = strtoupper($pathInfo['extension'] ?? 'UNKNOWN');

        return RachmaFile::fromArray([
            'id' => 1,
            'path' => $filePath,
            'original_name' => $pathInfo['basename'] ?? 'unknown',
            'format' => $extension,
            'size' => null,
            'is_primary' => true,
            'uploaded_at' => $this->created_at ?? now(),
            'description' => "Legacy {$extension} file"
        ]);
    }

    /**
     * Get file storage directory for this rachma
     */
    public function getFileStorageDirectory(): string
    {
        return "private/rachmat/files/{$this->id}";
    }

    /**
     * Ensure file storage directory exists
     */
    public function ensureFileStorageDirectory(): void
    {
        $directory = $this->getFileStorageDirectory();
        if (!Storage::disk('private')->exists($directory)) {
            Storage::disk('private')->makeDirectory($directory);
        }
    }

    /**
     * Ensure file storage directory exists
     */
    public function ensureStorageDirectoryExists(): void
    {
        $directory = $this->getFileStorageDirectory();
        if (!Storage::disk('private')->exists($directory)) {
            Storage::disk('private')->makeDirectory($directory);
        }
    }

    /**
     * Get downloadable files for API usage
     */
    public function getDownloadableFiles(): array
    {
        $downloadableFiles = [];
        
        // Use new multiple files system
        if ($this->hasFiles()) {
            foreach ($this->files as $file) {
                if ($file->exists()) {
                    $downloadableFiles[] = [
                        'id' => $file->id,
                        'path' => $file->path,
                        'name' => $file->original_name,
                        'format' => $file->format,
                        'size' => $file->getFileSize(),
                        'is_primary' => $file->is_primary
                    ];
                }
            }
        }
        // Fallback to single file for backward compatibility
        elseif ($this->file_path && Storage::disk('private')->exists($this->file_path)) {
            $pathInfo = pathinfo($this->file_path);
            $downloadableFiles[] = [
                'id' => 1,
                'path' => $this->file_path,
                'name' => $pathInfo['basename'] ?? 'file',
                'format' => strtoupper($pathInfo['extension'] ?? 'unknown'),
                'size' => Storage::disk('private')->size($this->file_path),
                'is_primary' => true
            ];
        }
        
        return $downloadableFiles;
    }
}
