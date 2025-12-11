<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        .btn {
            display: inline-block;
            padding: 12px 18px;
            background: #4f46e5;
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
        .btn-green {
            background: #22c55e;
        }
        .card {
            padding: 20px;
            background: #f9fafb;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>

    <h2>Terima kasih telah mendaftar: {{ $event->title }}</h2>

    <p>Berikut link penting untuk event ini:</p>

    <div class="card">
        <h3>🎥 Link Event / Zoom</h3>
        <a class="btn" href="{{ $event->extra_link }}" target="_blank">Masuk Event</a>
    </div>

    @if($event->whatsapp_group)
    <div class="card">
        <h3>💬 Grup WhatsApp</h3>
        <a class="btn btn-green" href="{{ $event->whatsapp_group }}" target="_blank">
            Masuk Grup WhatsApp
        </a>
    </div>
    @endif

    <div class="card">
        <h3>📘 Deskripsi Event</h3>
        <p style="white-space: pre-wrap;">{{ $event->description }}</p>

        <p><strong>Judul:</strong> {{ $event->title }}</p>
        <p><strong>Tanggal:</strong> {{ $event->date }}</p>
        <p><strong>Jam:</strong> {{ $event->time }}</p>
        <p><strong>Platform:</strong> {{ $event->platform }}</p>
    </div>

    <p>
        Jika ada kendala, cukup balas email ini.<br>
        Terima kasih,<br>
        {{ config('app.name') }}
    </p>

</body>
</html>
