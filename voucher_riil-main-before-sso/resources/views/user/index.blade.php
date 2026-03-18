<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-black">
            {{ __('Admin') }}
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
                <button onclick="openModal('add-p2-modal')"
                    class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5">
                    Tambah Admin
                </button>

                <div class="flex items-center gap-2">
                    <input type="text" id="search-customer" placeholder="Cari nama admin..."
                        class="px-4 py-2 border rounded" />

                    {{-- <form action="{{ route('user.import') }}" method="POST" enctype="multipart/form-data"
                        class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required
                            class="px-2 py-1 text-sm border rounded" />
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-700 rounded hover:bg-green-700">
                            Import Penerima Voucher by Excel
                        </button>
                    </form> --}}
                    {{-- <a href="{{ route('admin.export.all', request()->only(['q','status','from','to'])) }}"
                        class="inline-flex items-center px-3 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">
                        Export Excel
                    </a> --}}
                    <a href="{{ route('admin.export.padel') }}"
                        class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                        Export Padel Malang
                    </a>

                    <a href="{{ route('admin.export.others') }}"
                        class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Export Mooncake
                    </a>
                </div>
            </div>


            <div id="customer-table">
                @include('user.partials.table', ['customers' => $customers])
            </div>

        </div>
    </div>

    <!-- Modal Tambah -->
    <div id="add-p2-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
        <div class="relative p-6 bg-white rounded-lg shadow w-[600px]">
            <!-- Header Modal -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Tambah Admin</h3>
                <button type="button" class="text-xl font-bold text-gray-500 hover:text-gray-700"
                    onclick="closeModal('add-p2-modal')">×</button>
            </div>
            <form action="{{ route('user.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        Outlet
                        <select name="outlet_id" class="w-full p-2 border rounded-lg" required>
                            @foreach ($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        Nama Admin
                        <input type="text" name="name" placeholder="Nama Admin"
                            class="w-full p-2 border rounded-lg" required />
                    </div>

                    <div>
                        Email
                        <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded-lg"
                            required />
                    </div>

                    <div>
                        Password
                        <input type="password" name="password" placeholder="Password"
                            class="w-full p-2 border rounded-lg" required />
                    </div>
                </div>

                <button type="submit"
                    class="mt-4 w-full text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5">
                    Tambah Admin
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="edit-customer-modal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
        <div class="relative p-6 bg-white rounded-lg shadow w-[600px]">
            <!-- Header Modal -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Edit Admin</h3>
                <button type="button" class="text-xl font-bold text-gray-500 hover:text-gray-700"
                    onclick="document.getElementById('edit-customer-modal').classList.add('hidden')">×</button>
            </div>
            <form id="edit-customer-form" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        Outlet
                        <select name="outlet_id" id="edit-outlet_id" class="w-full p-2 border rounded-lg" required>
                            @foreach ($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        Nama Admin
                        <input type="text" name="name" id="edit-name" placeholder="Nama Admin"
                            class="w-full p-2 border rounded-lg" required />
                    </div>
                    <div>
                        Email
                        <input type="email" name="email" id="edit-email" placeholder="Email"
                            class="w-full p-2 border rounded-lg" required />
                    </div>
                    <div>
                        Password
                        <input type="password" name="password" placeholder="(Kosongkan jika tidak diubah)"
                            class="w-full p-2 border rounded-lg" />
                    </div>
                </div>

                <button type="submit"
                    class="mt-4 w-full text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div id="delete-confirmation-modal"
        class="fixed inset-0 z-50 items-center justify-center hidden bg-gray-900 bg-opacity-50">
        <div class="relative p-6 bg-white rounded-lg shadow w-[400px] text-center">
            <h3 class="mb-4 text-lg font-semibold">Hapus Admin?</h3>
            <p class="mb-6 text-sm text-gray-600">Apakah Anda yakin ingin menghapus admin ini?</p>
            <form id="delete-customer-form" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-center gap-4">
                    <button type="submit" class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">
                        Ya, Hapus
                    </button>
                    <button type="button" onclick="closeModal('delete-confirmation-modal')"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#search-customer').on('input', function() {
            let query = $(this).val();
            $.ajax({
                url: "{{ route('user') }}",
                type: 'GET',
                data: {
                    search: query
                },
                success: function(data) {
                    $('#customer-table').html(data);
                },
                error: function() {
                    $('#customer-table').html('<p class="p-4 text-red-500">Gagal memuat data</p>');
                }
            });
        });
    </script>

    <script>
        function editCustomer(id) {
            $.get(`/user/api/${id}`, function(data) {
                $('#edit-name').val(data.name);
                $('#edit-email').val(data.email);
                $('#edit-outlet_id').val(data.outlet_id); // otomatis select option sesuai value
                $('#edit-customer-form').attr('action', `/user/${id}`);
                $('#edit-customer-modal').removeClass('hidden').addClass('flex');
            });
        }

        function deleteCustomer(id) {
            $('#delete-customer-form').attr('action', `/user/${id}`);
            $('#delete-confirmation-modal').removeClass('hidden').addClass('flex');
        }

        function closeModal(modalId) {
            $(`#${modalId}`).addClass('hidden').removeClass('flex');
        }
    </script>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.getElementById(id).classList.add('flex');
        }
    </script>
</x-app-layout>
