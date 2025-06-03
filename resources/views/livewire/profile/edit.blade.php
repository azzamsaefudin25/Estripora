<div class="max-w-2xl mx-auto mt-20 p-6 bg-white shadow-md rounded-xl">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Profil</h2>

    <form wire:submit.prevent="updateProfile">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-gray-500">NIK</label>
                <input type="text" wire:model="nik" class="mt-1 p-3 w-full border rounded" disabled />
                @error('nik')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="text-sm text-gray-500">Username</label>
                <input type="text" wire:model="username" class="mt-1 p-3 w-full border rounded" />
                @error('username')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="text-sm text-gray-500">Nama Lengkap</label>
                <input type="text" wire:model="name" class="mt-1 p-3 w-full border rounded" />
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="text-sm text-gray-500">Email</label>
                <input type="email" wire:model="email" class="mt-1 p-3 w-full border rounded" />
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="text-sm text-gray-500">No HP</label>
                <input type="text" wire:model="phone" class="mt-1 p-3 w-full border rounded" />
                @error('phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-4 mt-6">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Simpan
            </button>
            <button onclick="window.location.href='{{ route('indexProfile') }}'"
                class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                Batal
            </button>
        </div>

    </form>
</div>
