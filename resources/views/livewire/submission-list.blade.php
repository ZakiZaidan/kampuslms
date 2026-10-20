<div>
    <table>
        <thead>
            <tr>
                <th>Mahasiswa</th>
                <th>Berkas</th>
                <th>Terlambat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($submissions as $submission)
            <tr>
                <td>{{ $submission->user->name }}</td>
                <td>{{ $submission->original_name }}</td>
                <td>
                    @if($submission->is_late)
                        <span style="color:red">Terlambat</span>
                    @else
                        <span style="color:green">Tepat waktu</span>
                    @endif
                </td>
                <td>
                    {{-- BUG 7 (w09): Hanya menyembunyikan tombol di tampilan.
                         Method download() di SubmissionList.php tidak ada Gate::authorize().
                         Penyerang bisa memanggil wire:click="download(99)" untuk submission
                         milik orang lain langsung lewat HTTP POST ke endpoint Livewire.
                         Solusi: tambahkan Gate::authorize('view', $submission) di method PHP. --}}
                    @if($submission->user_id === auth()->id())
                        <button wire:click="download({{ $submission->id }})">
                            Unduh
                        </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

