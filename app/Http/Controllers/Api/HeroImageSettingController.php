<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HeroImageSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $imagePaths = $this->resolveImagePaths();
        $mobileImagePaths = $this->resolveMobileImagePaths();
        $imageUrls = $this->resolveImageUrls($imagePaths);
        $mobileImageUrls = $this->resolveImageUrls($mobileImagePaths);

        return response()->json([
            'image_path' => $imagePaths[0] ?? null,
            'image_url' => $imageUrls[0] ?? null,
            'image_paths' => $imagePaths,
            'image_urls' => $imageUrls,
            'mobile_image_path' => $mobileImagePaths[0] ?? null,
            'mobile_image_url' => $mobileImageUrls[0] ?? null,
            'mobile_image_paths' => $mobileImagePaths,
            'mobile_image_urls' => $mobileImageUrls,
        ]);
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        if (blank($imagePath)) {
            return null;
        }

        return Storage::disk('public')->url($imagePath);
    }

    /**
     * @return array<int, string>
     */
    private function resolveImagePaths(): array
    {
        return $this->resolveImagePathsFor('hero.image_paths', 'hero.image_path');
    }

    /**
     * @return array<int, string>
     */
    private function resolveMobileImagePaths(): array
    {
        return $this->resolveImagePathsFor('hero.mobile_image_paths', 'hero.mobile_image_path');
    }

    /**
     * @return array<int, string>
     */
    private function resolveImagePathsFor(string $pathsKey, string $legacyKey): array
    {
        $imagePaths = $this->normalizeImagePaths(Setting::getValue($pathsKey));

        if ($imagePaths !== []) {
            return $imagePaths;
        }

        $legacyPath = Setting::getValue($legacyKey);

        if (blank($legacyPath)) {
            return [];
        }

        return [$legacyPath];
    }

    /**
     * @return array<int, string>
     */
    private function normalizeImagePaths(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($path): bool => filled($path)));
        }

        if (is_string($decoded)) {
            return [$decoded];
        }

        return [$value];
    }

    /**
     * @param  array<int, string>  $imagePaths
     * @return array<int, string>
     */
    private function resolveImageUrls(array $imagePaths): array
    {
        if ($imagePaths === []) {
            return [];
        }

        $imagePaths = array_values(array_filter($imagePaths, fn ($path): bool => filled($path)));

        return array_values(array_map(fn (string $path): string => Storage::disk('public')->url($path), $imagePaths));
    }
}
