<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    {{-- FORMULARIO DE LOGIN TRADICIONAL (SIN CAMBIOS) --}}
    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Log in') }}
            </flux:button>
        </div>
    </form>

    {{-- ========================================================== --}}
    {{-- INICIO DEL BLOQUE DE LOGIN FACIAL --}}
    {{-- ========================================================== --}}
    <div class="relative flex items-center">
        <span class="flex-grow border-t border-zinc-300 dark:border-zinc-700"></span>
        <span class="mx-4 flex-shrink text-xs text-zinc-500 uppercase">O</span>
        <span class="flex-grow border-t border-zinc-300 dark:border-zinc-700"></span>
    </div>

    <div x-data="faceLogin()" class="flex flex-col items-center">
        {{-- CAMBIO CLAVE AQUÍ: :disabled="loading" se convierte en x-bind:disabled="loading" --}}
        <flux:button @click="startCamera()" type="button" variant="outline" class="w-full" x-bind:disabled="loading">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span x-show="!loading">{{ __('Iniciar Sesión con Rostro') }}</span>
            <span x-show="loading">{{ __('Verificando...') }}</span>
        </flux:button>
        <p x-text="message" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>

        {{-- Modal para mostrar la cámara --}}
        <div x-show="cameraOpen" style="display: none;" x-transition class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click.self="stopCamera()">
            <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow-xl text-center">
                <h3 class="font-semibold mb-2 text-zinc-800 dark:text-zinc-200">Posiciona tu rostro en el centro</h3>
                <video x-ref="video" class="w-96 h-72 rounded bg-zinc-200 dark:bg-zinc-700" autoplay playsinline></video>
                <canvas x-ref="canvas" class="hidden"></canvas>
                <p class="mt-2 text-sm text-zinc-500">Verificando automáticamente...</p>
            </div>
        </div>
    </div>

    <script>
    function faceLogin() {
        return {
            cameraOpen: false, loading: false, message: '', stream: null,
            startCamera() {
                this.message = ''; this.cameraOpen = true;
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        this.stream = stream;
                        this.$refs.video.srcObject = stream;
                        this.$refs.video.oncanplay = () => {
                            setTimeout(() => this.captureAndLogin(), 1500);
                        };
                    })
                    .catch(err => {
                        this.message = 'No se pudo acceder a la cámara. Revisa los permisos.';
                        this.stopCamera();
                    });
            },
            stopCamera() {
                this.cameraOpen = false; this.loading = false;
                if (this.stream) { this.stream.getTracks().forEach(track => track.stop()); }
            },
            captureAndLogin() {
                if (!this.cameraOpen) return;
                this.loading = true;
                const video = this.$refs.video; const canvas = this.$refs.canvas;
                canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageDataUrl = canvas.toDataURL('image/jpeg');
                this.stopCamera();

                fetch('{{ route("face.login") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ image: imageDataUrl })
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(({ status, body }) => {
                    if (status === 200 && body.success) {
                        window.location.href = body.redirect;
                    } else {
                        this.message = body.message || 'No se pudo iniciar sesión.';
                    }
                })
                .catch(() => this.message = 'Error de comunicación con el servidor.')
                .finally(() => this.loading = false);
            }
        }
    }
    </script>
    {{-- ========================================================== --}}
    {{-- FIN DEL BLOQUE DE LOGIN FACIAL --}}
    {{-- ========================================================== --}}

    @if (Route::has('register'))
        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __("Don't have an account?") }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>