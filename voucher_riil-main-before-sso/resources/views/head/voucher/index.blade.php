<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-black">
            {{ __('Voucher') }}
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
                {{-- <button data-modal-target="add-p2-modal" data-modal-toggle="add-p2-modal"
                    class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5">
                    Tambah Voucher
                </button> --}}

                <input type="text" id="search-input" placeholder="Cari nama penerima atau kode voucher..."
                    class="w-1/2 px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                <div class="flex gap-4 my-4">
                    <label><input type="checkbox" class="status-filter" value="0"> Sudah Di-Scan</label>
                    <label><input type="checkbox" class="status-filter" value="1"> Voucher Tersedia</label>
                    <label><input type="checkbox" class="status-filter" value="2"> Voucher Sudah Dikirim</label>
                </div>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full text-sm text-gray-500 table-auto">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr class="text-center">
                            <th class="px-6 py-3">No</th>
                            <th class="px-6 py-3">Kode Voucher</th>
                            <th class="px-6 py-3">Outlet Voucher</th>
                            <th class="px-6 py-3">Tgl Berlaku</th>
                            <th class="px-6 py-3">Tgl Hangus</th>
                            {{-- <th class="px-6 py-3">Discan</th> --}}
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="text-center" id="voucher-table">
                        @include('head.voucher.partials.table', ['vouchers' => $vouchers])
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $vouchers->links() }}
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
                <form action="{{ route('voucherHead.store') }}" method="POST" class="pt-4">
                    @csrf
                    <div class="mb-4">
                        <label for="outlet_id" class="block mb-1 text-sm font-medium">Pilih Outlet</label>
                        <select name="outlet_id" id="outlet_id" class="w-full border border-gray-300 rounded" required>
                            @foreach ($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }} ({{ $outlet->kode }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="tgl_terbit_voucher" class="block mb-1 text-sm font-medium">Tanggal Terbit</label>
                        <input type="date" name="tgl_terbit_voucher" class="w-full border border-gray-300 rounded"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="tgl_exp_voucher" class="block mb-1 text-sm font-medium">Tanggal Hangus</label>
                        <input type="date" name="tgl_exp_voucher" class="w-full border border-gray-300 rounded"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block mb-1 text-sm font-medium">Deskripsi Voucher</label>
                        <textarea name="description" rows="3" class="w-full border border-gray-300 rounded" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="jumlah" class="block mb-1 text-sm font-medium">Jumlah Voucher</label>
                        <input type="number" name="jumlah" min="1"
                            class="w-full border border-gray-300 rounded" required>
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
                        <input type="text" id="edit-name" name="name" class="w-full p-2 border rounded-lg"
                            required />
                    </div>
                    <div class="mb-4">
                        <label for="edit-kode" class="block text-sm font-medium">Kode Outlet</label>
                        <input type="text" id="edit-kode" name="kode" class="w-full p-2 border rounded-lg"
                            required />
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

        <!-- Modal Detail Benefit -->
        <div id="detail-benefit-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50">
            <div class="relative w-full max-w-lg p-4 bg-white rounded-lg shadow-lg">
                <button onclick="closeModal('detail-benefit-modal')"
                    class="absolute text-gray-600 top-2 right-2 hover:text-gray-900">✖</button>
                <h3 class="mb-4 text-lg font-semibold">Detail Benefit</h3>
                <div id="detail-benefit-content" class="overflow-x-auto">
                    <p class="text-gray-500">Memuat data...</p>
                </div>
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

        <script>
            document.getElementById('search-input').addEventListener('input', function() {
                const query = this.value;

                fetch(`/voucherHead/search?query=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('voucher-table').innerHTML = data;
                    });
            });
        </script>

        <script>
            function debounce(func, delay) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), delay);
                };
            }

            const searchInput = document.getElementById('search-input');
            const statusCheckboxes = document.querySelectorAll('.status-filter');

            function fetchFilteredData() {
                const query = searchInput.value;
                const selectedStatuses = Array.from(statusCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => `status[]=${cb.value}`)
                    .join('&');

                const url = `/voucherHead/search?query=${encodeURIComponent(query)}&${selectedStatuses}`;

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('voucher-table').innerHTML = data;
                    });
            }

            const debouncedFetch = debounce(fetchFilteredData, 300);

            // Trigger search on typing
            searchInput.addEventListener('input', debouncedFetch);

            // Trigger search on checkbox change
            statusCheckboxes.forEach(cb => {
                cb.addEventListener('change', fetchFilteredData);
            });
        </script>

        <script>
            function showDetailBenefit(voucherId) {
                document.getElementById('detail-benefit-modal').classList.remove('hidden');
                const content = document.getElementById('detail-benefit-content');
                content.innerHTML = `<p class="text-gray-500">Memuat data...</p>`;

                fetch(`/voucherHead/${voucherId}/benefits`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) {
                            content.innerHTML = `<p class="text-gray-500">Tidak ada data benefit</p>`;
                            return;
                        }
                        let html = `<table class="w-full text-sm text-gray-600 border">
<thead class="bg-gray-100">
    <tr>
        <th class="px-3 py-2 border">Kode Benefit</th>
        <th class="px-3 py-2 border">Status</th>
        <th class="px-3 py-2 border">Discan</th>
        <th class="px-3 py-2 border">Pemilik Voucher</th>
    </tr>
</thead>
<tbody>`;
                        data.forEach(item => {
                            let statusText = '';
                            if (item.status == 0) statusText =
                                '<span class="font-semibold text-red-600">Sudah Di-Scan</span>';
                            else if (item.status == 1) statusText =
                                '<span class="font-semibold text-green-600">Voucher Tersedia</span>';
                            else if (item.status == 2) statusText =
                                '<span class="font-semibold text-blue-600">Voucher Sudah Dikirim</span>';
                            else statusText = '<span class="text-gray-500">Status Tidak Dikenal</span>';

                            let discan = item.discan ?
                                new Date(item.discan).toLocaleString('id-ID') :
                                '-';

                            let pemilik = (item.voucher && item.voucher.penerima) ?
                                item.voucher.penerima.name :
                                '-';

                            html += `<tr>
        <td class="px-3 py-2 border">${item.kode_benefit}</td>
        <td class="px-3 py-2 border">${statusText}</td>
        <td class="px-3 py-2 border">${discan}</td>
        <td class="px-3 py-2 border">${pemilik}</td>
    </tr>`;
                        });
                        html += `</tbody></table>`;
                        content.innerHTML = html;
                    })
                    .catch(err => {
                        console.error(err);
                        content.innerHTML = `<p class="text-red-500">Gagal memuat data</p>`;
                    });
            }

            function closeModal(id) {
                document.getElementById(id).classList.add('hidden');
            }
        </script>
</x-app-layout>
