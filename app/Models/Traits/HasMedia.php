<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasMedia
{
    /**
     * Default disk for storing media files.
     *
     * @var string
     */
    protected string $defaultMediaDisk = 'public';

    /**
     * Default path for storing media files.
     *
     * @var string
     */
    protected string $defaultMediaPath = 'media';

    /**
     * Default visibility for media files.
     *
     * @var string
     */
    protected string $defaultMediaVisibility = 'public';

    /**
     * Boot the trait to register model event listeners.
     *
     * @return void
     */
    public static function bootHasMedia()
    {
        static::deleted(function (Model $model) {
            foreach ($model->mediaFields as $mediaField => $options) {
                if ($mediaPath = $model->{$mediaField}) {
                    $model->deleteMediaFile($mediaPath, $options);
                }
            }
        });
    }

    /**
     * Set the value of a media attribute.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setAttribute(
        $key,
        $value
    )
    {
        if (
            !array_key_exists($key, $this->mediaFields) ||
            !($value instanceof UploadedFile)
        ) {
            parent::setAttribute($key, $value);
            return;
        }

        $this->handleMediaUpload($key, $value);
    }

    /**
     * Handle the upload of a media file.
     *
     * @param string $mediaField
     * @param UploadedFile $file
     * @return void
     */
    protected function handleMediaUpload(
        string       $mediaField,
        UploadedFile $file
    ): void
    {
        $options = $this->mediaFields[$mediaField] ?? [];
        $disk = $options['disk'] ?? $this->defaultMediaDisk;
        $path = $options['path'] ?? $this->defaultMediaPath;
        $visibility = $options['visibility'] ?? $this->defaultMediaVisibility;

        $oldMediaPath = $this->{$mediaField};

        $fileName = $this->generateUniqueFileName($file);

        $storedPath = $file->storeAs($path, $fileName, [
            'disk' => $disk,
            'visibility' => $visibility,
        ]);

        $this->attributes[$mediaField] = $storedPath;

        // Delete the old media file if it exists
        if ($oldMediaPath) {
            $this->deleteMediaFile($oldMediaPath, $options);
        }
    }

    /**
     * Generate a unique file name for the uploaded media file.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateUniqueFileName(
        UploadedFile $file
    ): string
    {
        return Str::uuid() . '.' . $file->getClientOriginalExtension();
    }

    /**
     * Delete the media file associated with the given attribute.
     *
     * @param string|null $mediaPath
     * @param array $options
     * @return void
     */
    public function deleteMediaFile(
        ?string $mediaPath = null,
        array  $options = []
    ): void
    {
        $disk = $options['disk'] ?? $this->defaultMediaDisk;
        $storage = Storage::disk($disk);
        if ($mediaPath && $storage->exists($mediaPath)) {
            $storage->delete($mediaPath);
        }
    }

    /**
     * Get the URL to the media file.
     *
     * @param string $mediaField
     * @param string|null $fallback
     * @return string|null
     */
    public function getMediaUrl(
        string $mediaField,
        string $fallback = null
    ): ?string
    {
        $options = $this->mediaFields[$mediaField] ?? [];
        $disk = $options['disk'] ?? $this->defaultMediaDisk;
        $storage = Storage::disk($disk);
        $mediaPath = $this->{$mediaField};

        return $mediaPath && $storage->exists($mediaPath)
            ? $storage->url($mediaPath)
            : $fallback;
    }

    public function deleteMedia(
        string $mediaField
    ): static
    {
        $options = $this->mediaFields[$mediaField] ?? [];
        $this->deleteMediaFile($this->{$mediaField}, $options);
        $this->{$mediaField} = null;
        return $this;
    }
}
