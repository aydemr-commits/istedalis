@extends('layouts.app', ['title' => 'Giris'])

@section('content')
<div class="row justify-content-center g-4 mt-2">
    <div class="col-md-5">
        <div class="card h-100 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 text-primary-emphasis">Ogrenci Girisi</h1>
                <p class="text-muted">Ogrenciler kendi dalis kayitlarini gorur, yeni kayit ekler ve duzenler.</p>
                <a href="{{ route('student.login') }}" class="btn btn-iste w-100">Ogrenci Girisi</a>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card h-100 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 text-primary-emphasis">Ogretim Elemani / Dalis Amiri Girisi</h2>
                <p class="text-muted">Yetkili personel tum ogrencileri ve raporlari gorur; yedekleme yalnizca admin hesabi ile yapilir.</p>
                <a href="{{ route('staff.login') }}" class="btn btn-dark w-100">Staff Girisi</a>
            </div>
        </div>
    </div>
</div>
@endsection
