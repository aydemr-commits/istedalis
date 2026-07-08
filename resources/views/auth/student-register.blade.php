@extends('layouts.app', ['title' => 'Ogrenci Kaydi'])

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Ogrenci Kaydi</h1>
                <form method="POST" action="{{ route('student.register.post') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Ogrenci no</label>
                        <input name="student_no" value="{{ old('student_no') }}" class="form-control" required autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">iste.edu.tr e-posta</label>
                        <input name="email" type="email" value="{{ old('email') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ad</label>
                        <input name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Soyad</label>
                        <input name="surname" value="{{ old('surname') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Program</label>
                        <select name="program" class="form-select" required>
                            <option value="">Program secin</option>
                            <option @selected(old('program') === 'Su Altı Teknolojisi')>Su Altı Teknolojisi</option>
                            <option @selected(old('program') === 'Sualtı Kaynak Teknolojisi')>Sualtı Kaynak Teknolojisi</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sinif</label>
                        <select name="class_name" class="form-select" required>
                            <option value="">Sinif secin</option>
                            <option @selected(old('class_name') === '1. Sinif')>1. Sinif</option>
                            <option @selected(old('class_name') === '2. Sinif')>2. Sinif</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefon</label>
                        <input name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sifre</label>
                        <input name="password" type="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sifre tekrar</label>
                        <input name="password_confirmation" type="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-iste">Kayit Olustur</button>
                        <a class="btn btn-outline-secondary" href="{{ route('student.login') }}">Giris ekranina don</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
