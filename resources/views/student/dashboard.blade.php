@extends('layouts.app', ['title' => 'Ogrenci Paneli'])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h1 class="h3 mb-1">{{ $student->full_name }}</h1>
        <div class="text-muted">{{ $student->student_no }} - {{ $student->program }} - {{ $student->class_name }}</div>
    </div>
    <a class="btn btn-iste" href="{{ route('student.dives.create') }}">Yeni Dalis Kaydi</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card metric"><div class="card-body"><div class="text-muted">Toplam dalis</div><div class="fs-3 fw-bold">{{ $stats['count'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card metric"><div class="card-body"><div class="text-muted">Toplam sure</div><div class="fs-3 fw-bold">{{ $stats['duration'] }} dk</div></div></div></div>
    <div class="col-md-3"><div class="card metric"><div class="card-body"><div class="text-muted">Maks. derinlik</div><div class="fs-3 fw-bold">{{ $stats['max_depth'] }} m</div></div></div></div>
    <div class="col-md-3"><div class="card metric"><div class="card-body"><div class="text-muted">Son dalis</div><div class="fs-5 fw-bold">{{ $stats['last_date']?->format('d.m.Y') ?? '-' }}</div></div></div></div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5">Dalis Kayitlarim</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Tarih</th><th>Yer</th><th>Tip</th><th>Sure</th><th>Maks. derinlik</th><th></th></tr></thead>
                <tbody>
                @forelse($dives as $dive)
                    <tr>
                        <td>{{ $dive->dive_date?->format('d.m.Y') }}</td>
                        <td>{{ $dive->location }}</td>
                        <td>{{ $dive->dive_type }}</td>
                        <td>{{ $dive->duration_minutes }} dk</td>
                        <td>{{ $dive->max_depth }} m</td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('student.dives.edit', $dive) }}">Duzenle</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Kayit bulunmuyor.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
