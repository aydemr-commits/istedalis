@extends('layouts.app', ['title' => 'Ogrenci Girisi'])

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Ogrenci Girisi</h1>
                <form method="POST" action="{{ route('student.login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Ogrenci no</label>
                        <input name="student_no" value="{{ old('student_no') }}" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sifre</label>
                        <input name="password" type="password" class="form-control" required>
                    </div>
                    <button class="btn btn-iste w-100">Giris Yap</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
