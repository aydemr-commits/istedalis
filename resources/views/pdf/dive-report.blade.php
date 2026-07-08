<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1, h2 { color: #10284a; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #e8edf3; }
        .summary { display: table; width: 100%; margin-top: 16px; }
        .summary div { display: table-row; }
        .summary strong, .summary span { display: table-cell; border-bottom: 1px solid #e5e7eb; padding: 5px; }
        .page-break { page-break-before: always; }
        .approval { margin-top: 18mm; border: 1px solid #10284a; padding: 10px; }
        .approval-grid { display: table; width: 100%; }
        .approval-grid div { display: table-cell; width: 25%; height: 42px; vertical-align: top; border-right: 1px solid #cbd5e1; padding: 4px; }
        .approval-grid div:last-child { border-right: 0; }
    </style>
</head>
<body>
    <section>
        <h1>ISTE DENIZCILIK TEKNOLOJILERI MYO</h1>
        <h2>DALIS KAYIT RAPORU</h2>
        <div class="summary">
            <div><strong>Ogrenci no</strong><span>{{ $student->student_no }}</span></div>
            <div><strong>Ad soyad</strong><span>{{ $student->full_name }}</span></div>
            <div><strong>Program</strong><span>{{ $student->program }}</span></div>
            <div><strong>Sinif</strong><span>{{ $student->class_name }}</span></div>
            <div><strong>Rapor tarih araligi</strong><span>{{ $dateRange }}</span></div>
            <div><strong>Secilen dalis sayisi</strong><span>{{ $dives->count() }}</span></div>
            <div><strong>Secilen dalislarin toplam suresi</strong><span>{{ $dives->sum('duration_minutes') }} dk</span></div>
            <div><strong>Maksimum derinlik</strong><span>{{ $dives->max('max_depth') }} m</span></div>
            <div><strong>Rapor olusturma tarihi</strong><span>{{ $createdAt->format('d.m.Y H:i') }}</span></div>
            <div><strong>Raporu olusturan</strong><span>{{ $staff?->full_name ?? 'Ogretim Elemani / Dalis Amiri' }}</span></div>
        </div>
        <table>
            <thead><tr><th>Tarih</th><th>Dalis yeri</th><th>Dalis tipi</th><th>Sure</th><th>Maks. derinlik</th><th>Dalis amiri</th></tr></thead>
            <tbody>
            @foreach($dives as $dive)
                <tr><td>{{ $dive->dive_date?->format('d.m.Y') }}</td><td>{{ $dive->location }}</td><td>{{ $dive->dive_type }}</td><td>{{ $dive->duration_minutes }} dk</td><td>{{ $dive->max_depth }} m</td><td>{{ $dive->supervisor_name }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </section>

    @foreach($dives as $dive)
        <section class="page-break">
            <h2>Dalis Kaydi</h2>
            <table>
                <tr><th>Ogrenci no</th><td>{{ $student->student_no }}</td><th>Ad soyad</th><td>{{ $student->full_name }}</td></tr>
                <tr><th>Dalis tarihi</th><td>{{ $dive->dive_date?->format('d.m.Y') }}</td><th>Dalis yeri</th><td>{{ $dive->location }}</td></tr>
                <tr><th>Dalis tipi</th><td>{{ $dive->dive_type }}</td><th>Amac / gorev</th><td>{{ $dive->purpose }}</td></tr>
                <tr><th>Baslama saati</th><td>{{ $dive->start_time }}</td><th>Bitis saati</th><td>{{ $dive->end_time }}</td></tr>
                <tr><th>Dalis suresi</th><td>{{ $dive->duration_minutes }} dk</td><th>Maksimum derinlik</th><td>{{ $dive->max_depth }} m</td></tr>
                <tr><th>Su sicakligi</th><td>{{ $dive->water_temperature }}</td><th>Gorus mesafesi</th><td>{{ $dive->visibility }}</td></tr>
                <tr><th>Hava / deniz durumu</th><td>{{ $dive->weather }}</td><th>Ekipman</th><td>{{ $dive->equipment }}</td></tr>
                <tr><th>Baslangic tup basinci</th><td>{{ $dive->start_pressure }}</td><th>Bitis tup basinci</th><td>{{ $dive->end_pressure }}</td></tr>
                <tr><th>Aciklama / bulgular</th><td colspan="3">{{ $dive->notes }}</td></tr>
            </table>
            <div class="approval">
                <strong>DALIS AMIRI ONAYI</strong>
                <div class="approval-grid">
                    <div>Dalis amiri adi soyadi<br>{{ $dive->supervisor_name }}</div>
                    <div>Imza</div>
                    <div>Tarih</div>
                    <div>Kase / onay alani</div>
                </div>
            </div>
        </section>
    @endforeach
</body>
</html>
