<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        {{-- FORMULARIO EXISTENTE PARA ACTUALIZAR PERFIL --}}
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        {{-- COMPONENTE EXISTENTE PARA BORRAR USUARIO --}}
        <livewire:settings.delete-user-form />

        {{-- ========================================================== --}}
        {{-- INICIO DEL CÓDIGO AÑADIDO PARA REGISTRO FACIAL --}}
        {{-- ========================================================== --}}
        <div x-data="faceEnroll()" class="mt-10 pt-6 border-t border-zinc-200 dark:border-zinc-700">
            <flux:heading>{{ __('Inicio de Sesión Facial') }}</flux:heading>
            <flux:subheading>{{ __('Registra tu rostro para iniciar sesión de forma rápida y segura.') }}</flux:subheading>

            <div class="mt-4">
                {{-- Contenedor del video y canvas que solo se muestra si la cámara está activa --}}
                <div x-show="cameraOpen" style="display: none;" class="relative w-64 h-48 bg-zinc-200 dark:bg-zinc-700 rounded-lg overflow-hidden">
                    <video x-ref="video" class="w-full h-full object-cover" autoplay playsinline></video>
                    <canvas x-ref="canvas" class="hidden"></canvas>
                </div>

                {{-- Botones de control --}}
                <div class="mt-4 flex gap-4">
                    <flux:button x-show="!cameraOpen" @click="startCamera()" type="button">{{ __('Activar Cámara') }}</flux:button>
                    <flux:button x-show="cameraOpen" style="display: none;" @click="captureAndEnroll()" :disabled="loading" type="button">
                        <span x-show="!loading">{{ __('Registrar Mi Rostro') }}</span>
                        <span x-show="loading">{{ __('Procesando...') }}</span>
                    </flux:button>
                    <flux:button x-show="cameraOpen" style="display: none;" @click="stopCamera()" variant="secondary" type="button">{{ __('Cancelar') }}</flux:button>
                </div>

                {{-- Mensajes de estado para el usuario --}}
                <p x-text="message" class="mt-2 text-sm" :class="{ 'text-green-600 dark:text-green-400': success, 'text-red-600 dark:text-red-400': !success }"></p>
            </div>
        </div>

        <script>
            function faceEnroll() {
                return {
                    cameraOpen: false,
                    loading: false,
                    message: '',
                    success: false,
                    stream: null,

                    startCamera() {
                        this.message = '';
                        this.cameraOpen = true;
                        navigator.mediaDevices.getUserMedia({ video: true })
                            .then(stream => {
                                this.stream = stream;
                                this.$refs.video.srcObject = stream;
                            })
                            .catch(err => {
                                this.message = 'No se pudo acceder a la cámara. Revisa los permisos en tu navegador.';
                                this.success = false;
                                this.cameraOpen = false;
                            });
                    },

                    stopCamera() {
                        this.cameraOpen = false;
                        if (this.stream) {
                            this.stream.getTracks().forEach(track => track.stop());
                        }
                    },

                    captureAndEnroll() {
                        this.loading = true;
                        this.message = '';
                        const video = this.$refs.video;
                        const canvas = this.$refs.canvas;
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                        const imageDataUrl = canvas.toDataURL('image/jpeg');

                        fetch('{{ route("face.enroll") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ image: imageDataUrl })
                        })
                        .then(res => res.json().then(data => ({ status: res.status, body: data })))
                        .then(({ status, body }) => {
                            this.message = body.message;
                            this.success = (status === 200);
                            if (this.success) {
                                this.stopCamera();
                            }
                        })
                        .catch(() => {
                            this.message = 'Ocurrió un error inesperado al contactar al servidor.';
                            this.success = false;
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                    }
                }
            }
        </script>
        {{-- ========================================================== --}}
        {{-- FIN DEL CÓDIGO AÑADIDO --}}
        {{-- ========================================================== --}}

    </x-settings.layout>
</section>