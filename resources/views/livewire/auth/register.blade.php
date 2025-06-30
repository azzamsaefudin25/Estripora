<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex h-full items-center">
        <main class="w-full max-w-5xl mx-auto p-10">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">Registrasi</h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Sudah punya akun?
                            <a class="text-blue-600 decoration-2 hover:underline font-medium dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600"
                                href="/login">
                                Masuk di sini
                            </a>
                        </p>
                    </div>
                    <hr class="my-5 border-slate-300">

                    <!-- Form -->
                    <form wire:submit.prevent='register'>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Form Group -->
                            <div>
                                <label for="nik" class="block text-sm mb-2 dark:text-white">NIK</label>
                                <div class="relative">
                                    <input type="text" id="nik" wire:model="nik" placeholder="NIK"
                                        class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600"
                                        aria-describedby="nik-error">
                                </div>
                                @error('nik')
                                    <p class="text-xs text-red-600 mt-2" id="nik-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Name Field -->
                            <div>
                                <label for="name" class="block text-sm mb-2 dark:text-white">Nama Lengkap</label>
                                <div class="relative">
                                    <input type="text" id="name" wire:model="name" placeholder="Nama Lengkap"
                                        class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600"
                                        aria-describedby="name-error">
                                </div>
                                @error('name')
                                    <p class="text-xs text-red-600 mt-2" id="name-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone Field -->
                            <div>
                                <label for="phone" class="block text-sm mb-2 dark:text-white">No HP</label>
                                <div class="relative">
                                    <input type="text" id="phone" wire:model="phone"
                                        placeholder="No HP/Telephone"
                                        class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600"
                                        aria-describedby="phone-error">
                                </div>
                                @error('phone')
                                    <p class="text-xs text-red-600 mt-2" id="phone-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email Field -->
                            <div>
                                <label for="email" class="block text-sm mb-2 dark:text-white">Email</label>
                                <div class="relative">
                                    <input type="email" id="email" wire:model="email" placeholder="Email"
                                        class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600"
                                        aria-describedby="email-error">
                                </div>
                                @error('email')
                                    <p class="text-xs text-red-600 mt-2" id="email-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Username Field -->
                            <div>
                                <label for="username" class="block text-sm mb-2 dark:text-white">Username</label>
                                <div class="relative">
                                    <input type="text" id="username" wire:model="username" placeholder="Username"
                                        class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600"
                                        aria-describedby="username-error">
                                </div>
                                @error('username')
                                    <p class="text-xs text-red-600 mt-2" id="username-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password Field -->
                            <div>
                                <label for="password" class="block text-sm mb-2 dark:text-white">Password</label>
                                <div class="relative">
                                    <input type="password" id="password" wire:model="password" placeholder="Password"
                                        class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600"
                                        aria-describedby="password-error">
                                    <button type="button" id="togglePassword"
                                        class="absolute inset-y-0 end-0 flex items-center pe-3">
                                        <svg id="eyeIcon" class="h-5 w-5 text-gray-500" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-xs text-red-600 mt-2" id="password-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Captcha Form Group -->
                            <div>
                                <label for="captcha" class="block text-sm mb-2 dark:text-white">Captcha</label>
                                <div class="flex gap-3 items-center mb-3">
                                    <!-- Captcha Display -->
                                    <div class="flex-1">
                                        <div
                                            class="bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-center">
                                            <span
                                                class="text-lg font-mono font-bold text-gray-800 dark:text-white letter-spacing-wide select-none"
                                                style="letter-spacing: 4px; font-family: 'Courier New', monospace;">
                                                {{ $captcha_code }}
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Refresh Button -->
                                    <button type="button" wire:click="refreshCaptcha"
                                        class="p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                            </path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Captcha Input -->
                                <input type="text" id="captcha" wire:model="captcha"
                                    placeholder="Masukkan captcha"
                                    class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600"
                                    aria-describedby="captcha-error">

                                @error('captcha')
                                    <p class="text-xs text-red-600 mt-2" id="captcha-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- End Captcha Form Group -->
                            <!-- Submit Button -->
                            <div class="mt-4 col-span-2">
                                <button type="submit"
                                    class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition-colors duration-200">
                                    Daftar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
