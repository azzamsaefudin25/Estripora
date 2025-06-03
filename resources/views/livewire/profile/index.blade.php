<div class="max-w-2xl mx-auto mt-20 p-6 bg-white shadow-md rounded-xl">
    @if (!$user)
        <div class="flex flex-col items-center text-center text-red-600">
            <!-- Heroicon: Exclamation Triangle -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 text-yellow-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 9v2m0 4h.01M10.29 3.86l-8.59 14.76A1 1 0 002.59 21h18.82a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z" />
            </svg>
            <p class="text-xl font-semibold">Silakan login dahulu</p>
        </div>
    @else
        <!-- Tampilan Profil -->
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Profil Pengguna</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <div>
                <label class="text-sm text-gray-500">NIK</label>
                <div class="mt-1 p-3 border rounded bg-gray-50">{{ $user->nik ?? '-' }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Username</label>
                <div class="mt-1 p-3 border rounded bg-gray-50">{{ $user->username }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Nama Lengkap</label>
                <div class="mt-1 p-3 border rounded bg-gray-50">{{ $user->name }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Email</label>
                <div class="mt-1 p-3 border rounded bg-gray-50">{{ $user->email }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">No HP</label>
                <div class="mt-1 p-3 border rounded bg-gray-50">{{ $user->phone ?? '-' }}</div>
            </div>
        </div>
        <div class="flex mt-5 ">
            <button type="submit" onclick="window.location.href='{{ route('editProfile') }}'"
                class="relative ml-auto bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition">Edit
                Profile</button>
        </div>
    @endif
</div>
