<?php

namespace App\Http\Controllers\Admin;

use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SignatureImageController
{
    public function __invoke(Request $request, Signature $signature): StreamedResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($signature->image), 404);

        return $disk->response($signature->image, headers: [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
