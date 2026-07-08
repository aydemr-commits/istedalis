@extends('layouts.app', ['title' => 'Dalis Amiri Kaydi'])

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Ogretim Elemani / Dalis Amiri Kaydi</h1>
                <form method="POST" action="{{ route('staff.register.post') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Kurum no</label>
                        <input name="staff_no" value="{{ old('staff_no') }}" class="form-control" required autofocus>
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
                        <label class="form-label">Sifre</label>
                        <input name="password" type="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sifre tekrar</label>
                        <input name="password_confirmation" type="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-dark">Kayit Olustur</button>
                        <a class="btn btn-outline-secondary" href="{{ route('staff.login') }}">Giris ekranina don</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
