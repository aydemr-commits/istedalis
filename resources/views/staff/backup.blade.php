@extends('layouts.app', ['title' => 'Yedekleme'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Yedekleme</h1>
    <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary">Panele Don</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5">Anlik Yedek Indir</h2>
        <div class="row g-3">
            <div class="col-md-6"><a class="btn btn-dark w-100" href="{{ route('staff.backup.json') }}">Tum verileri JSON olarak indir</a></div>
            <div class="col-md-6"><a class="btn btn-dark w-100" href="{{ route('staff.backup.all_csv') }}">Tum verileri CSV olarak indir</a></div>
            <div class="col-md-6"><a class="btn btn-iste w-100" href="{{ route('staff.backup.students_csv') }}">Ogrenci listesi CSV indir</a></div>
            <div class="col-md-6"><a class="btn btn-iste w-100" href="{{ route('staff.backup.dives_csv') }}">Dalis kayitlari CSV indir</a></div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Saklanan JSON Yedekleri</h2>
                <div class="text-muted small">Otomatik yedekler ve manuel olusturulan dosyalar {{ $retentionDays }} gun saklanir.</div>
            </div>
            <form method="POST" action="{{ route('staff.backup.create') }}">
                @csrf
                <button class="btn btn-outline-dark">Manuel yedek olustur</button>
            </form>
        </div>

        @if(empty($backups))
            <div class="alert alert-info mb-0">Henuz saklanan yedek dosyasi yok.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Dosya</th>
                            <th>Olusturma zamani</th>
                            <th>Boyut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                            <tr>
                                <td>{{ $backup['filename'] }}</td>
                                <td>{{ $backup['created_at']->timezone(config('app.timezone'))->format('d.m.Y H:i') }}</td>
                                <td>{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('staff.backup.files', $backup['filename']) }}">Indir</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
