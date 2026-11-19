<?php

namespace App\Helpers;

use Google\Client as Google_Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\{Admin,Subscription};

class Helper
{
    public static function redirectFunction($route, $notification)
    {
        return Redirect::route($route)->with($notification);
    }

    public static function responseFunction($status, $code, $msg, $data, $responseCode)
    {
        $response = ['status' => $status, 'status_code' => $code, 'message' => $msg];
        if (! is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $responseCode);
    }

    public static function validationResponse($validateUser, $code)
    {
        return response()->json([
            'status' => false,
            'status_code' => $code,
            'message' => 'Validation Error',
            'errors' => $validateUser->errors()->first(),
        ], 400);

    }

    public static function getCommonData($title, $headingTitle = null, $button = null)
    {
        return [
            'title' => $title,
            'heading_title' => $headingTitle ?? $title,
            'button' => $button,
        ];
    }
    
    public static function handleFileUpload($request, $fileKey, $storagePath, $existingPath = null)
    {
        if ($request->hasFile($fileKey)) {
            return self::handleUploadedFile($request->file($fileKey), $storagePath, $existingPath);
        }

        return $existingPath;
    }

    // public static function handleUploadedFile($file, $storagePath, $existingPath = null)
    // {
    //     if (empty($file)) {
    //         return $existingPath;
    //     }

    //     $mimeType = strtolower((string) $file->getMimeType());
    //     $supportedImageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];

    //     if (! in_array($mimeType, $supportedImageMimes, true)) {
    //         return self::storeOriginalFile($file, $storagePath);
    //     }

    //     $source = self::createImageResource($file->getRealPath(), $mimeType);
    //     if (! $source) {
    //         return self::storeOriginalFile($file, $storagePath);
    //     }

    //     $sourceWidth = imagesx($source);
    //     $sourceHeight = imagesy($source);

    //     if ($sourceWidth <= 0 || $sourceHeight <= 0) {
    //         return self::storeOriginalFile($file, $storagePath);
    //     }

    //     $fileBaseName = time().'_'.Str::random(8);
    //     Storage::disk('public')->makeDirectory($storagePath);

    //     $targetExtension = function_exists('imagewebp') ? 'webp' : 'jpg';
    //     $variants = [
    //         'full' => ['max_width' => 1280, 'max_height' => 1280, 'quality' => 82],
    //         'medium' => ['max_width' => 720, 'max_height' => 720, 'quality' => 78],
    //         'thumb' => ['max_width' => 360, 'max_height' => 360, 'quality' => 74],
    //     ];

    //     foreach ($variants as $variant => $config) {
    //         $relativeStoragePath = $storagePath.'/'.$fileBaseName.'_'.$variant.'.'.$targetExtension;
    //         $absolutePath = storage_path('app/public/'.$relativeStoragePath);

    //         self::saveOptimizedVariant(
    //             $source,
    //             $sourceWidth,
    //             $sourceHeight,
    //             $absolutePath,
    //             $config['max_width'],
    //             $config['max_height'],
    //             $config['quality'],
    //             $targetExtension,
    //             $mimeType
    //         );
    //     }

    //     return 'public/storage/'.$storagePath.'/'.$fileBaseName.'_full.'.$targetExtension;
    // }


    /**
     * Store an uploaded photo without EXIF/GPS/metadata (GD re-encode only).
     * JPEG orientation is applied before re-encode, then tags are discarded.
     */
    public static function handleUploadedFile($file, $storagePath, $existingPath = null)
    {
        if (empty($file)) {
            return $existingPath;
        }
    
        $mimeType = strtolower((string) $file->getMimeType());
        $supportedImageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    
        if (! in_array($mimeType, $supportedImageMimes, true)) {
            throw new \InvalidArgumentException('Unsupported image type. Allowed: jpeg, png, webp, gif.');
        }

        $realPath = $file->getRealPath();
        $orientation = self::readExifOrientation($realPath, $mimeType);
    
        $source = self::createImageResource($realPath, $mimeType);
        if (! $source) {
            throw new \InvalidArgumentException('Invalid image content.');
        }

        $source = self::applyExifOrientation($source, $orientation);
    
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
    
        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($source);
            throw new \InvalidArgumentException('Invalid image dimensions.');
        }

        $maxDimension = 8000;
        if ($sourceWidth > $maxDimension || $sourceHeight > $maxDimension) {
            imagedestroy($source);
            throw new \InvalidArgumentException('Image dimensions exceed the maximum allowed size.');
        }
    
        $fileBaseName = time().'_'.Str::random(8);
    
        // ✅ create folder in public/storage
        $publicPath = public_path('storage/'.$storagePath);
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0755, true);
        }
    
        $targetExtension = function_exists('imagewebp') ? 'webp' : 'jpg';
    
        $variants = [
            'full' => ['max_width' => 1280, 'max_height' => 1280, 'quality' => 82],
            'medium' => ['max_width' => 720, 'max_height' => 720, 'quality' => 78],
            'thumb' => ['max_width' => 360, 'max_height' => 360, 'quality' => 74],
        ];
    
        foreach ($variants as $variant => $config) {
            $relativeStoragePath = $storagePath.'/'.$fileBaseName.'_'.$variant.'.'.$targetExtension;
    
            // ✅ SAVE DIRECTLY IN public/storage
            $absolutePath = public_path('storage/'.$relativeStoragePath);
    
            self::saveOptimizedVariant(
                $source,
                $sourceWidth,
                $sourceHeight,
                $absolutePath,
                $config['max_width'],
                $config['max_height'],
                $config['quality'],
                $targetExtension,
                $mimeType
            );
        }

        imagedestroy($source);
    
        // Relative to Laravel public/ so asset() works when APP_URL ends with /public
        return 'storage/'.$storagePath.'/'.$fileBaseName.'_full.'.$targetExtension;
    }

    private static function readExifOrientation(string $filePath, string $mimeType): int
    {
        if (! in_array($mimeType, ['image/jpeg', 'image/jpg'], true)) {
            return 1;
        }
        if (! function_exists('exif_read_data')) {
            return 1;
        }

        $exif = @exif_read_data($filePath);
        if (! is_array($exif) || empty($exif['Orientation'])) {
            return 1;
        }

        return (int) $exif['Orientation'];
    }

    /**
     * @param  \GdImage|resource  $image
     * @return \GdImage|resource
     */
    private static function applyExifOrientation($image, int $orientation)
    {
        if ($orientation <= 1) {
            return $image;
        }

        switch ($orientation) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);

                return $image;
            case 3:
                return imagerotate($image, 180, 0) ?: $image;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);

                return $image;
            case 5:
                $rotated = imagerotate($image, -90, 0);
                if ($rotated !== false) {
                    imageflip($rotated, IMG_FLIP_HORIZONTAL);

                    return $rotated;
                }

                return $image;
            case 6:
                return imagerotate($image, -90, 0) ?: $image;
            case 7:
                $rotated = imagerotate($image, 90, 0);
                if ($rotated !== false) {
                    imageflip($rotated, IMG_FLIP_HORIZONTAL);

                    return $rotated;
                }

                return $image;
            case 8:
                return imagerotate($image, 90, 0) ?: $image;
            default:
                return $image;
        }
    }

    public static function getImageVariants($path)
    {
        $normalizedPath = self::normalizeImagePath($path);
        
        if (empty($normalizedPath)) {
            return [
                'full' => '',
                'medium' => '',
                'thumb' => '',
            ];
        }

        if (! preg_match('/_full\.(webp|jpg|jpeg|png)$/i', $normalizedPath, $matches)) {
            return [
                'full' => $normalizedPath,
                'medium' => $normalizedPath,
                'thumb' => $normalizedPath,
            ];
        }

        $extension = $matches[1];
        $basePath = preg_replace('/_full\.(webp|jpg|jpeg|png)$/i', '', $normalizedPath);

        return [
            'full' => $basePath.'_full.'.$extension,
            'medium' => $basePath.'_medium.'.$extension,
            'thumb' => $basePath.'_thumb.'.$extension,
        ];
    }

    /**
     * Build a public URL for a stored media path.
     * Strips a leading "public/" so asset() does not produce /public/public/... when
     * APP_URL already includes /public (shared hosting).
     */
    public static function mediaAsset($path)
    {
        if (empty($path)) {
            return '';
        }

        $path = trim((string) $path);
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(self::normalizeImagePath($path));
    }
    
    public static function normalizeImagePath($path)
    {
        if (empty($path)) {
            return '';
        }
        
        $path = trim((string) $path);
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        
        $path = str_replace('\\', '/', ltrim($path, '/'));

        // Legacy disk path → public-relative storage path
        if (str_starts_with($path, 'storage/app/public/')) {
            $path = 'storage/'.substr($path, strlen('storage/app/public/'));
        }

        // DB historically stored "public/storage/..."; asset() base is already public web root
        while (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }
        
        $path = preg_replace('#/+#', '/', $path);

        return $path;
    }

    /**
     * Delete a publicly stored upload by DB path (storage/... or public/storage/...).
     */
    public static function deletePublicFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $relative = self::normalizeImagePath($path);
        if ($relative === '' || str_starts_with($relative, 'http://') || str_starts_with($relative, 'https://')) {
            return;
        }

        $publicFile = public_path($relative);
        if (is_file($publicFile)) {
            @unlink($publicFile);
        }

        if (str_starts_with($relative, 'storage/')) {
            $diskPath = substr($relative, strlen('storage/'));
            if ($diskPath !== '' && Storage::disk('public')->exists($diskPath)) {
                Storage::disk('public')->delete($diskPath);
            }
        }
    }

    private static function createImageResource($filePath, $mimeType)
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($filePath),
            'image/png' => @imagecreatefrompng($filePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : null,
            'image/gif' => @imagecreatefromgif($filePath),
            default => null,
        };
    }

    private static function saveOptimizedVariant($source, $sourceWidth, $sourceHeight, $absolutePath, $maxWidth, $maxHeight, $quality, $targetExtension, $sourceMimeType)
    {
        $scale = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight, 1);
        $newWidth = max(1, (int) round($sourceWidth * $scale));
        $newHeight = max(1, (int) round($sourceHeight * $scale));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array($sourceMimeType, ['image/png', 'image/webp', 'image/gif'], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        } else {
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $white);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        if ($targetExtension === 'webp' && function_exists('imagewebp')) {
            imagewebp($resized, $absolutePath, $quality);
        } else {
            imagejpeg($resized, $absolutePath, $quality);
        }

    }

    // private static function storeOriginalFile($file, $storagePath)
    // {
    //     $extension = strtolower((string) $file->getClientOriginalExtension());
    //     $extension = $extension ?: 'jpg';

    //     $fileName = time().'_'.Str::random(8).'.'.$extension;
    //     $file->storeAs($storagePath, $fileName, 'public');

    //     return 'public/storage/'.$storagePath.'/'.$fileName;
    // }
    
    
    private static function storeOriginalFile($file, $storagePath)
{
    $extension = strtolower((string) $file->getClientOriginalExtension());
    $extension = $extension ?: 'jpg';

    $fileName = time().'_'.Str::random(8).'.'.$extension;

    // ✅ save in public/storage
    $destinationPath = public_path('storage/'.$storagePath);

    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0755, true);
    }

    $file->move($destinationPath, $fileName);

    return 'storage/'.$storagePath.'/'.$fileName;
}
    

    /* --- generate access token for firebase --- */
    private static $firebaseCredentialsPath;

    public static function init()
    {
        $relative = config('services.firebase.credentials', 'app/service-account-file.json');
        self::$firebaseCredentialsPath = storage_path($relative);
    }

    // public function __construct()
    // {
    //     $this->firebaseCredentialsPath = storage_path('app/service-account-file.json');
    // }

    private static function getAccessToken()
    {
        $client = new Google_Client;
        $client->setAuthConfig(self::$firebaseCredentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();

        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

        return $accessToken;
    }

    // push notification
    public static function PushNotification($tokens, $msg)
    {
        try {
            self::init();
            $accessToken = self::getAccessToken();
            // echo "<pre>"; print_r($accessToken); die;

            // $dataPayload = $msg;
            foreach($tokens as $token) {
                $json_data = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $msg['title'],
                            'body' => $msg['body'],
                        ],
                        'data' => $msg,
                    ],
                ];
    
                $data = json_encode($json_data);
                $url  = 'https://fcm.googleapis.com/v1/projects/cooking-champs-79749/messages:send';
                $headers = [
                    'Content-Type:application/json',
                    'Authorization:Bearer '.$accessToken,
                ];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $result = curl_exec($ch);

                if ($result === false) {
                    Log::error('FCM Send Error: '.curl_error($ch));
                    curl_close($ch);

                    return false;
                }
                curl_close($ch);
    
                return $result;
            }

        } catch (\Exception $e) {
            return false;
        }

    }

    // story detail share notification
    public static function StoryDetailPushNotification($tokens, $msg)
    {
        try {
            // Ensure the credentials path is set
            self::init();
            $accessToken = self::getAccessToken();

            $url = 'https://fcm.googleapis.com/v1/projects/cooking-champs-79749/messages:send';
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer '.$accessToken,
            ];

            foreach ($tokens as $token) {
                $json_data = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $msg['title'],
                            'body' => $msg['body'],
                        ],
                        'data' => $msg,
                    ],
                ];

                $data = json_encode($json_data);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $result = curl_exec($ch);

                if ($result === false) {
                    Log::error('FCM Send Error: '.curl_error($ch));
                }

                curl_close($ch);

            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error sending FCM notification: '.$e->getMessage());

            return false;
        }
    }
    
    // get platform charges
    public static function getPlatformCharges(): array
    {
        $admin = Admin::first();

        return [
            'tax_amount'       => $admin?->tax_amount ?? 0,
            'tax_amount_per'   => $admin?->tax_amount_per ?? 0,
            'platform_fee'     => $admin?->platform_fee ?? 0,
            'shipping_charges' => $admin?->shipping_charges ?? 0,
        ];
    }

    public static function getOpenAiApiKey(): ?string
    {
        $envKey = config('services.openai.api_key');
        if (! empty($envKey)) {
            return $envKey;
        }

        return Admin::first()?->openai_api_key ?: null;
    }

    public static function getStripeKey(): ?string
    {
        $envKey = config('services.stripe.STRIPE_KEY');
        if (! empty($envKey)) {
            return $envKey;
        }

        return Admin::first()?->stripe_key ?: null;
    }

    public static function getStripeSecret(): ?string
    {
        $envKey = config('services.stripe.STRIPE_SECRET');
        if (! empty($envKey)) {
            return $envKey;
        }

        return Admin::first()?->stripe_secret ?: null;
    }
    
    // get subscription price for kids learning.
    // Admin used to save type as Str::slug(name), so "Kids Learning" would not
    // match the hardcoded first-kid / additional-kid types. Fall back to any plan.
    public static function getSubscriptionPrice()
    {
        $planColumns = ['id', 'amount', 'stripe_price_id', 'type'];

        $firstKidAmount = Subscription::whereIn('type', ['first-kid', 'first_kid', 'firstkid'])
            ->orderBy('id')
            ->first($planColumns);

        $additionalKidAmount = Subscription::whereIn('type', ['additional-kid', 'additional_kid', 'additionalkid'])
            ->orderBy('id')
            ->first($planColumns);

        if (! $firstKidAmount) {
            $firstKidAmount = Subscription::orderBy('id')->first($planColumns);
        }

        if (! $additionalKidAmount) {
            $additionalKidAmount = $firstKidAmount
                ? (Subscription::where('id', '!=', $firstKidAmount->id)->orderBy('id')->first($planColumns) ?? $firstKidAmount)
                : null;
        }

        return [
            "first_kid_amount" => $firstKidAmount,
            "additional_kid_amount" => $additionalKidAmount,
        ];
    }
}
