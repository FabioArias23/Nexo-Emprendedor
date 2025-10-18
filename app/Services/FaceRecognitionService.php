<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    /**
     * Registra el rostro de un usuario en la API de reconocimiento facial.
     *
     * @param User $user El usuario al que se asociará el rostro.
     * @param string $imageBase64 La imagen en formato data URL (base64).
     * @return bool True si fue exitoso, false si falló.
     */
    public function enroll(User $user, string $imageBase64): bool
    {
        // Extraemos solo los datos de la imagen
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));

        try {
            $response = Http::asMultipart()
                ->post(config('services.face_api.url') . '/register', [
                    [
                        'name'     => 'identifier',
                        'contents' => $user->email,
                    ],
                    [
                        'name'     => 'image',
                        'contents' => $imageData,
                        'filename' => 'face.jpg'
                    ]
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Face API Error (Enroll Service): ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Face API Connection Error (Service): ' . $e->getMessage());
            return false;
        }
    }
}