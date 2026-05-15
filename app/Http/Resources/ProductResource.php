<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //  return parent::toArray($request);
        $imagePath = $this->image?->path;
        $imageName = $this->image?->file_name;

        $tempUrl = null;

        if ($imagePath) {
            if (method_exists(Storage::disk('minio'), 'temporaryUrl')) {
                $tempUrl = Storage::disk('minio')->temporaryUrl($imagePath, now()->addMinutes(config('filesystems.temp_url_lifetime')));
            }
        }

        return array_filter(
            [
                'id' => $this->id,
                'sku' => $this->sku,
                'name' => $this->name,
                'description' => $this->description,
                'price' => (float) $this->price,
                'discount' => $this->discount ? (float) $this->discount : null,
                'price_after_discount' => $this->price_after_discount,
                'quantity' => $this->quantity,
                'is_active' => (bool) $this->is_active,
                'image_name' => $imageName,
                'image' => $tempUrl,
                'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('d/m/Y H:i') : null,
                'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('d/m/Y H:i') : null,
            ],
            static fn($value) => !is_null($value),
        );
    }
}
