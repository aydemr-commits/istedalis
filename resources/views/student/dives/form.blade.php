@extends('layouts.app', ['title' => 'Dalis Kaydi'])

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4 mb-3">{{ $dive->exists ? 'Dalis Kaydini Duzenle' : 'Yeni Dalis Kaydi' }}</h1>
        <form method="POST" action="{{ $action }}" class="row g-3">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif
            <div class="col-md-3"><label class="form-label">Ogrenci no</label><input class="form-control" value="{{ $student->student_no }}" disabled></div>
            <div class="col-md-5"><label class="form-label">Ad soyad</label><input class="form-control" value="{{ $student->full_name }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Dalis tarihi</label><input type="date" name="dive_date" class="form-control" value="{{ old('dive_date', optional($dive->dive_date)->format('Y-m-d')) }}" required></div>
            <div class="col-md-4"><label class="form-label">Dalis yeri</label><input name="location" class="form-control" value="{{ old('location', $dive->location) }}" required></div>
            <div class="col-md-4"><label class="form-label">Dalis tipi</label><input name="dive_type" class="form-control" value="{{ old('dive_type', $dive->dive_type) }}" required></div>
            <div class="col-md-4"><label class="form-label">Amac / gorev</label><input name="purpose" class="form-control" value="{{ old('purpose', $dive->purpose) }}"></div>
            <div class="col-md-3"><label class="form-label">Baslama saati</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time', $dive->start_time) }}"></div>
            <div class="col-md-3"><label class="form-label">Bitis saati</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time', $dive->end_time) }}"></div>
            <div class="col-md-3"><label class="form-label">Sure, dakika</label><input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $dive->duration_minutes) }}" required min="1"></div>
            <div class="col-md-3"><label class="form-label">Maks. derinlik, metre</label><input type="number" step="0.01" name="max_depth" class="form-control" value="{{ old('max_depth', $dive->max_depth) }}"></div>
            <div class="col-md-3"><label class="form-label">Su sicakligi</label><input type="number" step="0.1" name="water_temperature" class="form-control" value="{{ old('water_temperature', $dive->water_temperature) }}"></div>
            <div class="col-md-3"><label class="form-label">Gorus mesafesi</label><input type="number" step="0.1" name="visibility" class="form-control" value="{{ old('visibility', $dive->visibility) }}"></div>
            <div class="col-md-6"><label class="form-label">Hava / deniz durumu</label><input name="weather" class="form-control" value="{{ old('weather', $dive->weather) }}"></div>
            <div class="col-md-6"><label class="form-label">Ekipman</label><input name="equipment" class="form-control" value="{{ old('equipment', $dive->equipment) }}"></div>
            <div class="col-md-3"><label class="form-label">Baslangic tup basinci</label><input type="number" name="start_pressure" class="form-control" value="{{ old('start_pressure', $dive->start_pressure) }}"></div>
            <div class="col-md-3"><label class="form-label">Bitis tup basinci</label><input type="number" name="end_pressure" class="form-control" value="{{ old('end_pressure', $dive->end_pressure) }}"></div>
            <div class="col-md-6"><label class="form-label">Dalis amiri</label><input name="supervisor_name" class="form-control" value="{{ old('supervisor_name', $dive->supervisor_name) }}"></div>
            <div class="col-12"><label class="form-label">Aciklama / bulgular</label><textarea name="notes" class="form-control" rows="4">{{ old('notes', $dive->notes) }}</textarea></div>
            <div class="col-12 d-flex justify-content-between">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">Geri</a>
                <button class="btn btn-iste">Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection
