<div class="w-full">
    <h2 class="text-2xl font-bold mb-6">Ulasan Pengunjung</h2>

    <!-- Filter buttons -->
    <div class="flex space-x-4 mb-6">
        <button wire:click="setFilter('all')"
            class="px-4 py-2 rounded-lg transition-colors {{ $ratingFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-800' }}">
            Semua
        </button>
        <button wire:click="setFilter('highest')"
            class="px-4 py-2 rounded-lg transition-colors {{ $ratingFilter === 'highest' ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-800' }}">
            Rating Tertinggi
        </button>
        <button wire:click="setFilter('lowest')"
            class="px-4 py-2 rounded-lg transition-colors {{ $ratingFilter === 'lowest' ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-800' }}">
            Rating Terendah
        </button>
    </div>

    @if (count($ulasans) > 0)
        <div class="max-h-96 overflow-y-auto pr-2">
            <div class="space-y-6">
                @foreach ($ulasans as $ulasan)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                {{-- <h3 class="text-lg font-semibold">{{ $ulasan->penyewaan->lokasi->tempat->nama }}
                                </h3> --}}
                                <h4 class="text-lg font-semibold text-gray-600">{{ $ulasan->penyewaan->user->name }}
                                </h4>
                                <!-- Menambahkan nama lokasi -->
                                <p class="text-xs text-blue-600">Lokasi: {{ $ulasan->penyewaan->lokasi->nama_lokasi }}
                                </p>
                            </div>
                            <div class="flex items-center">
                                <div class="flex">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $ulasan->rating)
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                </path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                </path>
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                                <span
                                    class="ml-1 text-sm text-gray-600">{{ $ulasan->created_at->format('d M Y') }}</span>
                            </div>
                        </div>

                        <div class="text-gray-800 mb-4">
                            <div class="">
                                {{ $ulasan->ulasan }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4 mt-2">
                            <!-- Like button with active state -->
                            <button wire:click="likeUlasan({{ $ulasan->id_ulasan }})"
                                class="flex items-center space-x-1 transition-colors {{ isset($userReactions[$ulasan->id_ulasan]) && $userReactions[$ulasan->id_ulasan] === 'like' ? 'text-blue-600' : 'text-gray-600 hover:text-blue-600' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="{{ isset($userReactions[$ulasan->id_ulasan]) && $userReactions[$ulasan->id_ulasan] === 'like' ? '2.5' : '2' }}">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                </svg>
                                <span>{{ $ulasan->like }}</span>
                            </button>

                            <!-- Dislike button with active state -->
                            <button wire:click="dislikeUlasan({{ $ulasan->id_ulasan }})"
                                class="flex items-center space-x-1 transition-colors {{ isset($userReactions[$ulasan->id_ulasan]) && $userReactions[$ulasan->id_ulasan] === 'dislike' ? 'text-red-600' : 'text-gray-600 hover:text-red-600' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="{{ isset($userReactions[$ulasan->id_ulasan]) && $userReactions[$ulasan->id_ulasan] === 'dislike' ? '2.5' : '2' }}">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2" />
                                </svg>
                                <span>{{ $ulasan->dislike }}</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $ulasans->links() }}
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500">Belum ada ulasan</p>
        </div>
    @endif

    <!-- Login Modal -->
    @if ($showLoginModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-transition>
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-xl font-semibold mb-4">Login Diperlukan</h3>
                <p class="mb-4">Anda harus login terlebih dahulu untuk memberikan like atau dislike pada ulasan.</p>
                <div class="flex justify-end space-x-3">
                    <button wire:click="closeLoginModal"
                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button wire:click="redirectToLogin"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Login
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
