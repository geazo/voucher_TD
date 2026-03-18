<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-black">
            {{ __('Transaksi') }}
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

            @if (session('error'))
                <div class="p-2 mb-4 text-sm text-white bg-red-500 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex items-center justify-between mb-4">

                <input type="text" id="search" placeholder="Cari nama penerima atau kode voucher..."
                    class="w-1/2 px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full text-sm text-gray-500 table-auto">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr class="text-center">
                            <th class="px-6 py-3">No</th>
                            <th class="px-6 py-3">Nama Penerima</th>
                            <th class="px-6 py-3">WA Penerima</th>
                            <th class="px-6 py-3">Email Penerima</th>
                            <th class="px-6 py-3">Outlet</th>
                            <th class="px-6 py-3">Voucher</th>
                            <th class="px-6 py-3">No. Bill</th>
                            <th class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="transaksi-table-body" class="text-center">
                        @include('transaksi.partials.table')
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $penerimas->links() }}
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-1/3 p-6 bg-white rounded-lg shadow-lg">
            <h2 class="mb-4 text-lg font-bold">Edit Penerima</h2>
            <form id="editForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="block mb-1">Outlet</label>
                    <select name="outlet_id" id="editOutlet" class="w-full p-2 border rounded"></select>
                </div>
                <div class="mb-3">
                    <label class="block mb-1">Nama</label>
                    <input type="text" name="name" id="editName" class="w-full p-2 border rounded">
                </div>
                <div class="mb-3">
                    <label class="block mb-1">Email</label>
                    <input type="email" name="email" id="editEmail" class="w-full p-2 border rounded">
                </div>
                <div class="mb-3">
                    <label class="block mb-1">Phone</label>
                    <input type="text" name="phone" id="editPhone" class="w-full p-2 border rounded">
                </div>
                <div class="mb-3">
                    <label class="block mb-1">Bill</label>
                    <input type="text" name="bill" id="editBill" class="w-full p-2 border rounded">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-300 rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search');

        searchInput.addEventListener('keyup', function() {
            const query = this.value;
            fetch(`/transaksi/search?search=${query}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('transaksi-table-body').innerHTML = html;
                })
                .catch(error => {
                    console.error('❌ Error:', error);
                });
        });
    </script>

    <script>
        function openEditModal(id) {
            fetch(`/penerima/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    const penerima = data.penerima;
                    const outlets = data.outlets;

                    // Isi dropdown outlet
                    const outletSelect = document.getElementById('editOutlet');
                    outletSelect.innerHTML = '';
                    outlets.forEach(o => {
                        outletSelect.innerHTML +=
                            `<option value="${o.id}" ${o.id === penerima.outlet_id ? 'selected' : ''}>${o.name}</option>`;
                    });

                    // Isi form
                    document.getElementById('editName').value = penerima.name;
                    document.getElementById('editEmail').value = penerima.email ?? '';
                    document.getElementById('editPhone').value = penerima.phone ?? '';
                    document.getElementById('editBill').value = penerima.bill ?? '';

                    // Set form action
                    document.getElementById('editForm').action = `/penerima/${id}`;

                    // Tampilkan modal
                    document.getElementById('editModal').classList.remove('hidden');
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
