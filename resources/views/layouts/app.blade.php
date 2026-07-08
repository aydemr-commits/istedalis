<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'istedalis') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --iste-navy:#10284a; --iste-orange:#f28c28; --iste-gray:#f4f6f8; }
        body { background: var(--iste-gray); color: #1f2937; }
        .topbar { background: var(--iste-navy); color: #fff; border-bottom: 4px solid var(--iste-orange); }
        .brand-title { line-height: 1.25; }
        .btn-iste { background: var(--iste-orange); border-color: var(--iste-orange); color: #111827; font-weight: 600; }
        .btn-iste:hover { background: #d97706; border-color: #d97706; color: #111827; }
        .card { border-radius: 8px; border: 1px solid #d9dee7; }
        .table thead th { background: #e8edf3; color: #10284a; }
        .metric { border-left: 4px solid var(--iste-orange); }
        .nav-link, a { color: #10284a; }
    </style>
</head>
<body>
    <header class="topbar py-3">
        <div class="container d-flex flex-wrap justify-content-between gap-3 align-items-center">
            <div class="brand-title">
                <div class="fw-bold fs-5">Iskenderun Teknik Universitesi</div>
                <div>Denizcilik Teknolojileri MYO</div>
                <div class="small text-white-50">istedalis</div>
            </div>
            @if(session()->has('student_id') || session()->has('staff_id'))
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm">Cikis</button>
                </form>
            @endif
        </div>
    </header>

    <main class="container py-4">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
