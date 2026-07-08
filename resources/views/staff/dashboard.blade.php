@extends('layouts.app', ['title' => 'Staff Paneli'])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h1 class="h3 mb-1">Ogretim Elemani / Dalis Amiri Paneli</h1>
        <div class="text-muted">{{ $staff->full_name }} @if($staff->isAdmin()) <span class="badge text-bg-dark">Admin</span> @endif</div>
    </div>
    @if($staff->isAdmin())
        <a class="btn btn-outline-dark" href="{{ route('staff.backup') }}">Yedekleme</a>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card metric"><div class="card-body"><div class="text-muted">Toplam ogrenci</div><div class="fs-3 fw-bold">{{ $summary['student_count'] }}</div></div></div></div>
    <div class="col-md-4"><div class="card metric"><div class="card-body"><div class="text-muted">Toplam dalis kaydi</div><div class="fs-3 fw-bold">{{ $summary['dive_count'] }}</div></div></div></div>
    <div class="col-md-4"><div class="card metric"><div class="card-body"><div class="text-muted">Toplam sure</div><div class="fs-3 fw-bold">{{ $summary['duration'] }} dk</div></div></div></div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5">Son Eklenen Dalislar</h2>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Ogrenci</th><th>Tarih</th><th>Yer</th><th>Tip</th><th>Sure</th></tr></thead>
                <tbody>
                @foreach($summary['recent'] as $dive)
                    <tr><td>{{ $dive->student?->full_name }}</td><td>{{ $dive->dive_date?->format('d.m.Y') }}</td><td>{{ $dive->location }}</td><td>{{ $dive->dive_type }}</td><td>{{ $dive->duration_minutes }} dk</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5">Ogrenciler</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Ogrenci no</th><th>Ad soyad</th><th>Program</th><th>Sinif</th><th>Dalis sayisi</th><th>Toplam sure</th><th></th></tr></thead>
                <tbody>
                @foreach($students as $student)
                    <tr>
                        <td>{{ $student->student_no }}</td>
                        <td>{{ $student->full_name }}</td>
                        <td>{{ $student->program }}</td>
                        <td>{{ $student->class_name }}</td>
                        <td>{{ $student->dives_count }}</td>
                        <td>{{ (int) $student->dives_sum_duration_minutes }} dk</td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('staff.students.report', $student) }}">Rapor al</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5">Tum Dalis Kayitlari</h2>
        <form class="row g-2 mb-3" method="GET" action="{{ route('staff.dashboard') }}">
            <div class="col-md-2"><input name="student_no" class="form-control" placeholder="Ogrenci no" value="{{ request('student_no') }}"></div>
            <div class="col-md-2"><input name="name" class="form-control" placeholder="Ad soyad" value="{{ request('name') }}"></div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div>
            <div class="col-md-2"><input name="location" class="form-control" placeholder="Dalis yeri" value="{{ request('location') }}"></div>
            <div class="col-md-2"><input name="dive_type" class="form-control" placeholder="Dalis tipi" value="{{ request('dive_type') }}"></div>
            <div class="col-12"><button class="btn btn-dark btn-sm">Filtrele</button> <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary btn-sm">Temizle</a></div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Ogrenci</th><th>Tarih</th><th>Yer</th><th>Tip</th><th>Sure</th><th>Derinlik</th><th>Dalis amiri</th></tr></thead>
                <tbody>
                @foreach($dives as $dive)
                    <tr>
                        <td>{{ $dive->student?->student_no }} - {{ $dive->student?->full_name }}</td>
                        <td>{{ $dive->dive_date?->format('d.m.Y') }}</td>
                        <td>{{ $dive->location }}</td>
                        <td>{{ $dive->dive_type }}</td>
                        <td>{{ $dive->duration_minutes }} dk</td>
                        <td>{{ $dive->max_depth }} m</td>
                        <td>{{ $dive->supervisor_name }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $dives->links() }}
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5">Ogrenci Bazli Raporlama</h2>
        <form class="row g-2 mb-3" method="GET" action="{{ route('staff.dashboard') }}">
            <div class="col-md-8">
                <select name="report_student_id" class="form-select" required>
                    <option value="">Ogrenci secin</option>
                    @foreach($allStudents as $student)
                        <option value="{{ $student->id }}" @selected(request('report_student_id') == $student->id)>{{ $student->student_no }} - {{ $student->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><button class="btn btn-dark w-100">Dalislari Listele</button></div>
        </form>

        @if($selectedStudent)
            <form method="POST" action="{{ route('staff.reports.student') }}">
                @csrf
                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th></th><th>Tarih</th><th>Yer</th><th>Tip</th><th>Sure</th><th>Derinlik</th></tr></thead>
                        <tbody>
                        @foreach($selectedStudent->dives()->orderBy('dive_date')->get() as $dive)
                            <tr>
                                <td><input type="checkbox" name="dive_ids[]" value="{{ $dive->id }}" checked></td>
                                <td>{{ $dive->dive_date?->format('d.m.Y') }}</td>
                                <td>{{ $dive->location }}</td>
                                <td>{{ $dive->dive_type }}</td>
                                <td>{{ $dive->duration_minutes }} dk</td>
                                <td>{{ $dive->max_depth }} m</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-iste">PDF Rapor Olustur</button>
            </form>
        @endif
    </div>
</div>
@endsection
