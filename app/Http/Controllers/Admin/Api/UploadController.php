<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadController extends Controller
{
    use ApiResponse;

    /**
     * @return array<int, string>
     */
    protected function parseTags(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }
        $raw = trim($raw);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map(static fn ($v) => trim((string) $v), $decoded)));
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public function store(Request $request): JsonResponse
    {
        $sceneKey = (string) $request->input('scene', 'avatar');
        if (! preg_match('/^[a-z0-9_]{1,32}$/', $sceneKey)) {
            return $this->fail('无效的场景参数', 422, 422);
        }

        $scenes = config('upload.scenes', []);
        if (! isset($scenes[$sceneKey])) {
            return $this->fail('未定义的上传场景', 422, 422);
        }

        $scene = $scenes[$sceneKey];
        $diskName = config('upload.disk', 'public');
        $disk = Storage::disk($diskName);

        $dir = trim((string) ($scene['directory'] ?? 'uploads'), '/');
        $maxKb = max(1, (int) ($scene['max_kb'] ?? 512));
        $mimes = $scene['mimes'] ?? ['jpeg', 'jpg', 'png'];
        $mimesRule = is_array($mimes) ? implode(',', $mimes) : 'jpeg,jpg,png';

        $mimeLabels = is_array($mimes) ? implode('、', $mimes) : $mimesRule;

        try {
            $request->validate([
                'file' => ['required', 'file', 'max:'.$maxKb, 'mimes:'.$mimesRule],
                'scene' => ['sometimes', 'string', 'max:32'],
            ], [
                'file.required' => '请选择要上传的文件',
                'file.file' => '请上传有效的文件',
                'file.max' => "文件大小不能超过 {$maxKb} KB",
                'file.mimes' => "仅允许以下类型：{$mimeLabels}",
                'scene.max' => '场景参数过长',
            ]);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first(), 422, 422);
        }

        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            return $this->fail('无效的文件', 422, 422);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $basename = Str::uuid()->toString().($ext ? '.'.$ext : '');

        $path = $disk->putFileAs($dir, $file, $basename);

        if ($path === false) {
            return $this->fail('存储失败', 500, 500);
        }

        $tags = $this->parseTags($request->input('tags'));

        $resource = Resource::query()->create([
            'original_name' => $file->getClientOriginalName() ?: $basename,
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'extension' => $ext,
            'size_bytes' => (int) $file->getSize(),
            'scene' => $sceneKey,
            'user_id' => $request->user()?->id,
            'disk' => $diskName,
            'status' => 1,
            'tags' => $tags,
            'uploaded_at' => now(),
        ]);

        return $this->ok([
            'path' => $path,
            'url' => $disk->url($path),
            'scene' => $sceneKey,
            'resource_id' => $resource->id,
        ]);
    }
}
