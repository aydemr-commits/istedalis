@extends('layouts.app', ['title' => 'Staff Girisi'])

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Ogretim Elemani / Dalis Amiri Girisi</h1>
                <form method="POST" action="{{ route('staff.login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Kurum no</label>
                        <input name="staff_no" value="{{ old('staff_no') }}" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sifre</label>
                        <input name="password" type="password" class="form-control" required>
                    </div>
                    <button class="btn btn-dark w-100">Giris Yap</button>
                </form>
                <div class="text-center mt-3">
                    <a href="{{ route('staff.register') }}">Dalis amiri kaydi olustur</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
