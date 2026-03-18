    @forelse ($vouchers as $index => $voucher)
        <tr>
            <td class="px-6 py-3">{{ $vouchers->firstItem() + $index }}</td>
            <td class="px-6 py-3">{{ $voucher->kode_voucher }}</td>
            <td class="px-6 py-3">{{ $voucher->outlet->name }}</td>
            <td class="px-6 py-3">{{ $voucher->tgl_terbit_voucher }}</td>
            <td class="px-6 py-3">{{ $voucher->tgl_exp_voucher }}</td>
            {{-- <td class="px-6 py-3">
                @if ($voucher->discan)
                    {{ \Carbon\Carbon::parse($voucher->discan)->format('d/m/Y H:i') }}
                    <br>
                    <span class="text-xs text-gray-700">
                        Pemilik Voucher: {{ $voucher->penerima->name ?? 'Tidak diketahui' }}
                    </span>
                @else
                    -
                @endif
            </td> --}}
            <td class="px-6 py-3">
                @if ($voucher->status == 0)
                    <span class="font-semibold text-red-600">Sudah Di-Scan</span>
                @elseif ($voucher->status == 1)
                    <span class="font-semibold text-green-600">Voucher Tersedia</span>
                @elseif ($voucher->status == 2)
                    <span class="font-semibold text-blue-600">Voucher Sudah Dikirim</span>
                @else
                    <span class="text-gray-500">Status Tidak Dikenal</span>
                @endif
            </td>
            <td class="px-6 py-3 text-center">
                <button onclick="showDetailBenefit({{ $voucher->id }})" class="text-blue-600 hover:text-blue-800"
                    title="Lihat Detail">
                    👁
                </button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="py-3 text-gray-500">Tidak ada data</td>
        </tr>
    @endforelse
