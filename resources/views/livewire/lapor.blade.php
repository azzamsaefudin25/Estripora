<div>
    @if (session()->has('message'))
        <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-4">
        <!-- Email -->
        <div>
            <label for="email" class="block font-semibold">Email:</label>
            <input type="email" wire:model="email" required class="border p-2 rounded w-full">
            @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <!-- Penyewaan -->
        <div>
            <label for="id_penyewaan" class="block font-semibold">ID Penyewaan:</label>
            <input type="text" wire:model="id_penyewaan" required class="border p-2 rounded w-full">
            @error('id_penyewaan') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <!-- Keluhan -->
        <div>
            <label for="keluhan" class="block font-semibold">Keluhan:</label>
            <textarea wire:model="keluhan" required class="border p-2 rounded w-full"></textarea>
            @error('keluhan') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <!-- Upload Foto -->
        <div>
            <label for="foto" class="block font-semibold">Upload Foto:</label>
            <input type="file" wire:model="foto" accept="image/*" class="border p-2 rounded w-full">
            @error('foto') <span class="text-red-500">{{ $message }}</span> @enderror

            @if ($foto)
                <p class="mt-2">Preview:</p>
                <img src="{{ $foto->temporaryUrl() }}" alt="Preview" width="800" class="rounded border">
            @endif
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Kirim Laporan</button>
    </form>
</div>
