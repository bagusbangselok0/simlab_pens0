@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    Tambah Laboratorium
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('lab.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nama_lab" class="form-label">Nama Laboratorium</label>
                        <input type="text" class="form-control" id="nama_lab" name="nama_lab" required>
                    </div>
                    <div class="mb-3">
                        <label for="kode_lab" class="form-label">Kode Laboratorium</label>
                        <input type="text" class="form-control" id="kode_lab" name="kode_lab" required>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        // Tambahkan skrip JavaScript jika diperlukan
    </script>
@endsection