<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\FaceRecognitionService;
class FaceAuthController extends Controller
{
    /**
     * Registra el rostro del usuario autenticado actualmente.
     */
    public function enroll(Request $request, FaceRecognitionService $faceService)
    {
        $request->validate(['image' => 'required']);

        $user = Auth::user();
        // La imagen viene del navegador como una data URL (base64)
        $imageBase64 = $request->input('image');
        
        // Extraemos solo los datos de la imagen, quitando el prefijo "data:image/jpeg;base64,"
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));

        try {
            // Hacemos la llamada a la API de Python como 'multipart/form-data'
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
                return response()->json(['success' => true, 'message' => 'Rostro registrado exitosamente.']);
            }

            // Si algo falla, lo registramos en el log para depuración
            Log::error('Face API Error (Enroll): ' . $response->body());
            return response()->json(['success' => false, 'message' => 'No se pudo registrar el rostro. Intenta con mejor iluminación.'], 500);

        } catch (\Exception $e) {
            Log::error('Face API Connection Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error de conexión con el servicio de reconocimiento.'], 500);
        }
    }

    /**
     * Inicia sesión de un usuario a través del reconocimiento facial.
     */
    public function login(Request $request)
    {
        $request->validate(['image' => 'required']);
        $imageBase64 = $request->input('image');
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));

        try {
            $response = Http::asMultipart()
                ->post(config('services.face_api.url') . '/recognize', [
                    [
                        'name'     => 'image',
                        'contents' => $imageData,
                        'filename' => 'face.jpg'
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Verificamos si hubo una coincidencia y no es 'unknown'
                if (!empty($data['matches']) && $data['matches'][0] !== 'unknown') {
                    $userEmail = $data['matches'][0];
                    $user = User::where('email', $userEmail)->first();

                    if ($user) {
                        Auth::login($user);
                        $request->session()->regenerate();
                        // Devolvemos una respuesta exitosa con la URL a la que redirigir
                        return response()->json(['success' => true, 'redirect' => route('dashboard')]);
                    }
                }
                // Si no hay usuario o el rostro es desconocido
                return response()->json(['success' => false, 'message' => 'Rostro no reconocido.'], 401);
            }

            Log::error('Face API Error (Login): ' . $response->body());
            return response()->json(['success' => false, 'message' => 'Error en el servicio de reconocimiento.'], 500);
        } catch (\Exception $e) {
            Log::error('Face API Connection Error (Login): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error de conexión con el servicio.'], 500);
        }
    }
}