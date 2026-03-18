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
                        @include('head.transaksi.partials.table')
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $penerimas->links() }}
            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search');

        searchInput.addEventListener('keyup', function() {
            const query = this.value;
            fetch(`/transaksiHead/search?search=${query}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('transaksi-table-body').innerHTML = html;
                })
                .catch(error => {
                    console.error('❌ Error:', error);
                });
        });
    </script>
</x-app-layout>
