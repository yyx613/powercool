<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class QuillController extends Controller
{
    const QUILL_IMAGE_PATH = 'public/quill-images';

    /**
     * Upload image for Quill editor
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                // Store the file
                $path = Storage::putFile(self::QUILL_IMAGE_PATH, $file);

                // Generate URL
                $url = config('app.url') . str_replace('public', '/storage', $path);

                return response()->json([
                    'url' => $url,
                    'path' => $path
                ]);
            }

            return response()->json([
                'message' => 'No image file provided'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }
}
