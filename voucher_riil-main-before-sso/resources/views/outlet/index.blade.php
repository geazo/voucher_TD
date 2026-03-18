<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-black">
            {{ __('Outlet') }}
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
                    Tambah Outlet
                </button>
            </div>

            <table class="min-w-full text-sm text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr class="text-center">
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Outlet</th>
                        <th class="px-6 py-3">Kode Outlet</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($outlets as $index => $outlet)
                        <tr>
                            <td class="px-6 py-3">{{ $outlets->firstItem() + $index }}</td>
                            <td class="px-6 py-3">{{ $outlet->name }}</td>
                            <td class="px-6 py-3">{{ $outlet->kode }}</td>
                            <td class="px-6 py-3">
                                <button
                                    onclick="editP2({{ $outlet->id }}, '{{ $outlet->name }}', '{{ $outlet->kode }}')"
                                    class="text-blue-600 hover:text-blue-800">Edit</button>
                                <button onclick="deleteP2({{ $outlet->id }})"
                                    class="text-red-600 hover:text-red-800">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $outlets->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div id="add-p2-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex items-center justify-center hidden overflow-x-hidden overflow-y-auto bg-black/50">
        <div class="relative w-full max-w-md p-4 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold">Tambah Outlet</h3>
                <button type="button" data-modal-hide="add-p2-modal" class="text-gray-400 hover:text-gray-900">
                    ×
                </button>
            </div>
            <form action="{{ route('outlet.store') }}" method="POST" class="pt-4">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block mb-1 text-sm font-medium">Nama Outlet</label>
                    <input type="text" name="name" id="name"
                        class="w-full px-3 py-2 border border-gray-300 rounded" required>
                </div>
                <div class="mb-4">
                    <label for="kode" class="block mb-1 text-sm font-medium">Kode Outlet</label>
                    <input type="text" name="kode" id="kode"
                        class="w-full px-3 py-2 border border-gray-300 rounded" required>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="edit-p2-modal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
        <div class="relative p-6 bg-white rounded-lg shadow">
            <button onclick="closeModal('edit-p2-modal')"
                class="absolute text-gray-600 top-2 right-2 hover:text-gray-900">
                ✖
            </button>
            <h3 class="mb-4 text-xl font-semibold">Edit Outlet</h3>
            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-id" name="id">
                <div class="mb-4">
                    <label for="edit-name" class="block text-sm font-medium">Nama Outlet</label>
                    <input type="text" id="edit-name" name="name" class="w-full p-2 border rounded-lg" required />
                </div>
                <div class="mb-4">
                    <label for="edit-kode" class="block text-sm font-medium">Kode Outlet</label>
                    <input type="text" id="edit-kode" name="kode" class="w-full p-2 border rounded-lg" required />
                </div>
                <button type="submit"
                    class="mt-3 text-white bg-green-700 hover:bg-green-800 font-medium rounded-lg text-sm px-5 py-2.5">
                    Update Outlet
                </button>
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
            <h3 class="mb-4 text-xl font-semibold">Hapus Outlet</h3>
            <p>Apakah Anda yakin ingin menghapus outlet ini?</p>
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
        function editP2(id, name, kode) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-kode').value = kode;
            document.getElementById('edit-form').action = "/outlet/" + id;
            document.getElementById('edit-p2-modal').classList.remove('hidden');
        }

        function deleteP2(id) {
            document.getElementById('delete-form').action = "/outlet/" + id;
            document.getElementById('delete-p2-modal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>
</x-app-layout>
