<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectTitle }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px 10px;
            color: #1e293b;
            line-height: 1.6;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
        }
        .email-header {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .logo-img {
            width: 52px;
            height: 52px;
            margin-bottom: 8px;
            vertical-align: middle;
        }
        .header-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #e2e8f0;
        }
        .email-body {
            padding: 24px;
        }
        .card-content {
            background-color: #f8fafc;
            border-left: 4px solid #1e3a8a;
            padding: 16px;
            border-radius: 4px;
            font-size: 14px;
            color: #334155;
            line-height: 1.6;
        }
        .card-content strong {
            color: #0f172a;
        }
        .action-area {
            text-align: center;
            margin-top: 24px;
        }
        .btn-primary {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff !important;
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 16px 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer-brand {
            font-weight: 700;
            color: #334155;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            @if(!empty($logoPath) && isset($message))
                <img src="{{ $message->embed($logoPath) }}" alt="SIKERJA Logo" class="logo-img" />
            @endif
            <h1 class="header-title">SIKERJA SAMARINDA</h1>
            <p class="header-subtitle">Sistem Kerja Sama Daerah Pemerintah Kota Samarinda</p>
        </div>
        
        <div class="email-body">
            <div class="card-content">
                {!! $formattedContent !!}
            </div>

            @if(!empty($actionUrl))
                <div class="action-area">
                    <a href="{{ $actionUrl }}" class="btn-primary" target="_blank">Buka Aplikasi SIKERJA</a>
                </div>
            @endif
        </div>

        <div class="email-footer">
            <div class="footer-brand">Tim Kerja Sama Daerah</div>
            <div>Pemerintah Kota Samarinda</div>
            <div style="margin-top: 8px; font-size: 11px; color: #94a3b8;">
                Email ini dikirim otomatis oleh Sistem SIKERJA Kota Samarinda. Harap tidak membalas email ini secara langsung.
            </div>
        </div>
    </div>
</body>
</html>
