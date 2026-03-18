<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modify') }}
        </h2>
    </x-slot>

    {{-- Table --}}
    <div class="px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <form method="POST" action="{{ url('/dashboard/' . $tamu->id) }}" enctype="multipart/form-data"
                class="max-w-md mx-auto mt-10">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    {{-- Left Box --}}
                    <div>
                        {{-- Kategori --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="kategori" id="kategori"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('kategori', $tamu->kategori) }}" required />
                            <label for="kategori"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Kategori</label>
                        </div>

                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="nik" id="nik"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('nik', $tamu->nik) }}" required />
                            <label for="nik"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">NIK</label>
                        </div>

                        {{-- Nama Depan --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="nama_depan" id="nama_depan"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('nama_depan', $tamu->nama_depan) }}" required />
                            <label for="nama_depan"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Nama
                                Depan</label>
                        </div>

                        {{-- Nama Belakang --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="nama_belakang" id="nama_belakang"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('nama_belakang', $tamu->nama_belakang) }}" required />
                            <label for="nama_belakang"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Nama
                                Belakang</label>
                        </div>

                        {{-- Perusahaan --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="perusahaan" id="perusahaan"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('perusahaan', $tamu->perusahaan) }}" required />
                            <label for="perusahaan"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Perusahaan</label>
                        </div>

                        {{-- No HP --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="no_hp" id="no_hp"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('no_hp', $tamu->no_hp) }}" required />
                            <label for="no_hp"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">No
                                HP</label>
                        </div>

                        {{-- Jam Masuk --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="jam_masuk" id="jam_masuk"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('jam_masuk', $tamu->jam_masuk) }}" readonly />
                            <label for="jam_masuk"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Jam
                                Masuk</label>
                        </div>

                        {{-- Jam Keluar --}}
                        {{-- <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="jam_keluar" id="jam_keluar"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('jam_keluar', $tamu->jam_keluar) }}" readonly />
                            <label for="jam_keluar"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Jam
                                Keluar</label>
                        </div> --}}

                         {{-- Tujuan --}}
                         <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="kepentingan" id="kepentingan"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('kepentingan', $tamu->kepentingan) }}" readonly />
                            <label for="kepentingan"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Kepentingan</label>
                        </div>

                        {{-- Tujuan --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="lokasi_tujuan" id="lokasi_tujuan"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('lokasi_tujuan', $tamu->lokasi_tujuan) }}" readonly />
                            <label for="lokasi_tujuan"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Tujuan</label>
                        </div>

                        {{-- Visit Location --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="visit_location" id="visit_location"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('visit_location', $tamu->visit_location) }}" />
                            <label for="visit_location"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Visit
                                Location</label>
                        </div>

                        {{-- Access Card --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="access_card" id="access_card"
                                class="block py-2.5 px-0 w-full text-sm text-black bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder=" " value="{{ old('access_card', $tamu->access_card) }}" />
                            <label for="access_card"
                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600">Access
                                Card</label>
                        </div>
                    </div>

                    {{-- Right Box --}}
                    <div>
                        {{-- Foto Wajah --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Foto Wajah</label>

                            @if (!empty($tamu->foto_wajah))
                                <img src="{{ asset($tamu->foto_wajah) }}" alt="Foto Wajah"
                                    class="w-32 h-32 object-cover rounded-md">
                            @else
                                <div
                                    class="w-32 h-32 flex items-center justify-center border border-gray-300 rounded-md bg-gray-100 text-gray-700 text-sm">
                                    Data tamu ini dibuat oleh admin
                                </div>
                            @endif
                        </div>


                        {{-- Foto KTP --}}
                        <div class="relative z-0 w-full mb-5 group">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Foto KTP</label>
                            <img src="{{ asset($tamu->foto_ktp) }}" alt="Foto KTP"
                                class="w-32 h-32 object-cover rounded-md">
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Update
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
