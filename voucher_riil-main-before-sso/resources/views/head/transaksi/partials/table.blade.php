@foreach ($penerimas as $index => $penerima)
    <tr>
        <td class="px-6 py-3">{{ $index + 1 }}</td>
        <td class="px-6 py-3">{{ $penerima->name }}</td>
        <td class="px-6 py-3">{{ $penerima->phone }}</td>
        <td class="px-6 py-3">{{ $penerima->email }}</td>
        <td class="px-6 py-3">
            {{ $penerima->outlet->name ?? '-' }} {{-- menampilkan nama outlet, fallback '-' jika null --}}
        </td>
        <td class="px-6 py-3">
            {{ $penerima->voucher->kode_voucher ?? 'Belum dikirim voucher' }} {{-- menampilkan nama outlet, fallback '-' jika null --}}
        </td>
        <td class="px-6 py-3">{{ $penerima->bill }}</td>
        <td class="flex justify-center px-6 py-3 space-x-2">
            @php $disabled = $penerima->voucher_id !== null; @endphp

            {{-- Kirim via Email --}}
            <form method="POST" action="{{ route('voucher.send', $penerima->id) }}">
                @csrf
                <button type="submit" title="Kirim Voucher ke Email"
                    class="{{ $disabled ? 'text-gray-700' : 'text-green-600 hover:text-green-800' }} disabled:opacity-50"
                    @if ($disabled) disabled @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M20 4H4a2 2 0 00-2 2v1.2l10 6.25L22 7.2V6a2 2 0 00-2-2zm0 4.25l-10 6.25L4 8.25V18a2 2 0 002 2h12a2 2 0 002-2V8.25z" />
                    </svg>
                </button>
            </form>

            {{-- Kirim via WhatsApp --}}
            <form method="POST" action="{{ route('voucher.send.whatsapp', $penerima->id) }}" target="_blank">
                @csrf
                <button type="submit" title="Kirim ke WhatsApp"
                    class="{{ $disabled ? 'text-gray-700' : 'text-blue-500 hover:text-blue-700' }} disabled:opacity-50"
                    @if ($disabled) disabled @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M20.52 3.48A11.78 11.78 0 0012.01 0 11.87 11.87 0 000 11.87a11.57 11.57 0 001.6 5.93L0 24l6.33-1.65a11.91 11.91 0 005.67 1.45h.01a11.78 11.78 0 0011.77-11.77c0-3.15-1.23-6.11-3.26-8.22zM12.01 21.71h-.01a9.65 9.65 0 01-4.92-1.34l-.35-.2-3.75.98 1-3.65-.23-.38a9.64 9.64 0 01-1.51-5.2A9.77 9.77 0 0112 2.23c5.38 0 9.75 4.37 9.75 9.75 0 5.38-4.37 9.73-9.74 9.73zm5.39-7.35c-.3-.15-1.78-.88-2.05-.98s-.48-.15-.69.15-.79.98-.97 1.18-.36.23-.66.08a7.97 7.97 0 01-2.33-1.43 8.8 8.8 0 01-1.64-2.06c-.17-.3 0-.46.13-.61.13-.13.3-.36.45-.53s.2-.3.3-.5a.57.57 0 000-.53c-.08-.15-.69-1.67-.94-2.28s-.5-.5-.69-.51h-.59a1.13 1.13 0 00-.82.38c-.28.3-1.07 1.04-1.07 2.55 0 1.5 1.09 2.95 1.24 3.15.15.2 2.15 3.3 5.2 4.63.73.31 1.29.5 1.73.64.73.23 1.4.2 1.93.12.59-.09 1.78-.73 2.03-1.44.25-.71.25-1.32.18-1.44-.07-.12-.27-.2-.56-.35z" />
                    </svg>
                </button>
            </form>

            {{-- Reset Voucher --}}
            <form method="POST" action="{{ route('penerima.resetVoucher', $penerima->id) }}">
                @csrf
                @method('PATCH')
                <button type="submit" title="Reset Voucher" class="text-orange-500 hover:text-orange-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v6h6M20 20v-6h-6M5 19A9 9 0 1119 5" />
                    </svg>
                </button>
            </form>

            {{-- Edit penerima --}}
            <button type="button" title="Lihat / Edit" class="text-purple-500 hover:text-purple-700"
                onclick="openEditModal({{ $penerima->id }})">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </td>
    </tr>
@endforeach
