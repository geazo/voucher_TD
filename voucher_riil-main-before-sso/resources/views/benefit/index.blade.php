<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-black">
            {{ __('Benefit') }}
        </h2>
    </x-slot>

    <div class="px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-2 mb-4 text-sm text-white bg-green-500 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex items-center justify-between mb-4">
                <button data-modal-target="add-p2-modal" data-modal-toggle="add-p2-modal"
                    class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5">
                    Tambah Voucher
                </button>
            </div>

            <table class="min-w-full text-sm text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr class="text-center">
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Foto</th> <!-- Kolom foto -->
                        <th class="px-6 py-3">Benefit</th>
                        <th class="px-6 py-3">Outlet</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($benefits as $index => $benefit)
                        <tr>
                            <td class="px-6 py-3">{{ $benefits->firstItem() + $index }}</td>
                            <td class="px-6 py-3">
                                @if ($benefit->photo)
                                    <img src="{{ asset('storage/' . $benefit->photo) }}"
                                        class="object-cover w-12 h-12 mx-auto rounded">
                                @else
                                    <span class="text-gray-400">Tidak ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">{{ $benefit->name }}</td>
                            <td class="px-6 py-3">{{ $benefit->outlet->name }}</td>
                            <td class="px-6 py-3">
                                {{-- <button
                                    onclick="editP2(
                                    '{{ $benefit->id }}',
                                    '{{ $benefit->name }}',
                                    '{{ $benefit->outlet_id }}',
                                    '{{ $benefit->photo ? asset('storage/' . $benefit->photo) : '' }}',
                                )">
                                    Edit
                                </button> --}}
                                <button
                                    onclick='editP2(
        {{ $benefit->id }},
        @json($benefit->name),
        {{ $benefit->outlet_id }},
        @json($benefit->photo ? asset('storage/' . $benefit->photo) : ''),
        @json($benefit->redemption),
        @json($benefit->kode)
    )'>
                                    Edit
                                </button>
                                <button onclick="deleteP2({{ $benefit->id }})"
                                    class="text-red-600 hover:text-red-800">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $benefits->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div id="add-p2-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex items-center justify-center hidden overflow-x-hidden overflow-y-auto bg-black/50">
        <div class="relative w-full max-w-md p-4 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold">Tambah Benefit</h3>
                <button type="button" data-modal-hide="add-p2-modal" class="text-gray-400 hover:text-gray-900">
                    ×
                </button>
            </div>
            <form action="{{ route('benefit.store') }}" method="POST" enctype="multipart/form-data" class="pt-4">
                @csrf
                {{-- Nama --}}
                <div class="mb-4">
                    <label for="name" class="block mb-1 text-sm font-medium">Nama Benefit</label>
                    <input type="text" name="name" id="name"
                        class="w-full px-3 py-2 border border-gray-300 rounded" required>
                </div>

                {{-- Kode --}}
                <div class="mb-4">
                    <label for="kode" class="block mb-1 text-sm font-medium">Kode Benefit</label>
                    <textarea name="kode" id="kode" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded"></textarea>
                </div>

                {{-- Outlet --}}
                <div class="mb-4">
                    <label for="outlet_id" class="block mb-1 text-sm font-medium">Pilih Outlet</label>
                    <select name="outlet_id" id="outlet_id" class="w-full border border-gray-300 rounded" required>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }} ({{ $outlet->kode }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Foto utama --}}
                <div class="mb-4">
                    <label for="photo" class="block mb-1 text-sm font-medium">Foto Benefit</label>
                    <input type="file" name="photo" id="photo" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>

                {{-- Redemption --}}
                <div class="mb-4">
                    <label for="redemption" class="block mb-1 text-sm font-medium">Redemption</label>
                    <textarea name="redemption" id="redemption" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded"></textarea>
                </div>

                {{-- Tombol Simpan --}}
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="edit-p2-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex items-center justify-center hidden overflow-x-hidden overflow-y-auto bg-black/50">
        {{-- <div class="relative w-full max-w-md p-4 bg-white rounded-lg shadow"> --}}
        <div class="relative w-full max-w-md max-h-screen p-4 overflow-y-auto bg-white rounded-lg shadow">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold">Edit Benefit</h3>
                <button type="button" onclick="closeModal('edit-p2-modal')"
                    class="text-gray-400 hover:text-gray-900">×</button>
            </div>

            <form id="edit-form" method="POST" enctype="multipart/form-data" class="pt-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-id" name="id">

                {{-- Nama Benefit --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">Nama Benefit</label>
                    <input type="text" name="name" id="edit-name"
                        class="w-full px-3 py-2 border border-gray-300 rounded" required>
                </div>

                {{-- Kode --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">Kode Benefit</label>
                    <textarea name="kode" id="edit-kode" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded"></textarea>
                </div>

                {{-- Outlet --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">Pilih Outlet</label>
                    <select name="outlet_id" id="edit-outlet_id" class="w-full border border-gray-300 rounded"
                        required>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }} ({{ $outlet->kode }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Foto Utama --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">Foto Benefit</label>
                    <img id="edit-photo-preview" src="" alt="Foto Benefit"
                        class="hidden object-cover w-20 h-20 mb-2 border">
                    <input type="file" name="photo" id="edit-photo" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>

                {{-- Kode --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">Redemption</label>
                    <textarea name="redemption" id="edit-redemption" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 text-white bg-green-600 rounded">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div id="delete-p2-modal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
        <div class="relative p-6 bg-white rounded-lg shadow">
            <button onclick="closeModal('delete-p2-modal')"
                class="absolute text-gray-600 top-2 right-2 hover:text-gray-900">
                ✖
            </button>
            <h3 class="mb-4 text-xl font-semibold">Hapus Benefit</h3>
            <p>Apakah Anda yakin ingin menghapus benefit ini?</p>
            <form id="delete-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="mt-3 text-white bg-red-700 hover:bg-red-800 font-medium rounded-lg text-sm px-5 py-2.5">
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- TailwindCSS (jika belum ada) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Flowbite JS -->
    <script src="https://unpkg.com/flowbite@2.2.1/dist/flowbite.min.js"></script>

    <!-- JavaScript -->
    <script>
        function editP2(id, name, outlet_id, photoUrl, redemption, kode) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-outlet_id').value = outlet_id;
            document.getElementById('edit-redemption').value = redemption || '';
            document.getElementById('edit-kode').value = kode || '';
            document.getElementById('edit-form').action = "/benefit/" + id;

            let preview = document.getElementById('edit-photo-preview');
            if (photoUrl) {
                preview.src = photoUrl;
                preview.classList.remove('hidden');
            } else {
                preview.src = '';
                preview.classList.add('hidden');
            }

            document.getElementById('edit-p2-modal').classList.remove('hidden');
        }

        function deleteP2(id) {
            document.getElementById('delete-form').action = "/benefit/" + id;
            document.getElementById('delete-p2-modal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>
</x-app-layout>
