<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scanner – Inloggen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --vk-green: #2e7d32; --vk-green-dark: #1b5e20; }
        .btn-vierdaagse { background: var(--vk-green); color: #fff; border: none; }
        .btn-vierdaagse:hover { background: var(--vk-green-dark); color: #fff; }
        .qr-promo { background: linear-gradient(135deg, var(--vk-green) 0%, var(--vk-green-dark) 100%); color: #fff; border-radius: 12px; padding: 1.5rem; text-align: center; }
        #login-qr-overlay { position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.7); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; }
        #login-qr-overlay .overlay-content { background: #fff; border-radius: 12px; max-width: 100%; width: 360px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
        #login-qr-overlay .overlay-header { background: linear-gradient(135deg, var(--vk-green) 0%, var(--vk-green-dark) 100%); color: #fff; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h1 class="h4 text-center mb-4">Vierdaagse Scanner</h1>

            <div class="qr-promo mb-4">
                <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
                <h2 class="h5 mb-2">Inloggen met QR-code</h2>
                <p class="mb-3 small opacity-90">Scan de QR-code die de organisator toont. Geen wachtwoord nodig.</p>
                <button type="button" class="btn btn-light btn-lg" id="btn-scan-login-qr">
                    <i class="bi bi-camera-fill me-2"></i>Scan QR-code
                </button>
            </div>


            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="d-flex align-items-center gap-2 my-3">
                <hr class="flex-grow-1">
                <span class="text-muted small">of met account</span>
                <hr class="flex-grow-1">
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post" action="{{ route('scanner.login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Wachtwoord</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input" value="1">
                            <label class="form-check-label" for="remember">Onthoud mij</label>
                        </div>
                        <button type="submit" class="btn btn-vierdaagse w-100">Inloggen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="login-qr-overlay" style="display: none;">
    <div class="overlay-content">
        <div class="overlay-header d-flex justify-content-between align-items-center p-3">
            <span class="fw-bold">Scan inlog-QR-code</span>
            <button type="button" class="btn btn-light btn-sm" id="btn-close-scanner" aria-label="Sluiten">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-2">
            <div id="login-qr-reader"></div>
        </div>
        <div class="p-3">
            <div id="login-qr-error" class="alert alert-danger py-2 mb-0" style="display: none;"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function() {
    const scannerDomain = @json(config('app.scanner_domain'));
    function isValidLoginUrl(url) {
        try {
            const u = new URL(url);
            const ok = u.pathname === '/login' && u.searchParams.has('token');
            const token = u.searchParams.get('token') || '';
            return ok && /^[a-zA-Z0-9]+$/.test(token) && (u.hostname === scannerDomain || u.hostname.endsWith('.' + scannerDomain));
        } catch (e) {
            return false;
        }
    }

    const overlay = document.getElementById('login-qr-overlay');
    const readerDiv = document.getElementById('login-qr-reader');
    const errorEl = document.getElementById('login-qr-error');
    const btnScan = document.getElementById('btn-scan-login-qr');
    const btnClose = document.getElementById('btn-close-scanner');

    let html5QrCode = null;

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
    }
    function hideError() {
        errorEl.style.display = 'none';
    }

    function startScanner() {
        if (html5QrCode && html5QrCode.isScanning) return;
        if (html5QrCode) {
            try { html5QrCode.clear(); } catch (e) {}
            html5QrCode = null;
        }
        overlay.style.display = 'flex';
        hideError();
        readerDiv.innerHTML = '';
        html5QrCode = new Html5Qrcode('login-qr-reader');
        html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 5, qrbox: { width: 250, height: 250 } },
            function(decodedText) {
                if (!isValidLoginUrl(decodedText)) {
                    showError('Dit is geen geldige inlog-QR-code. Scan de code die de organisator toont.');
                    return;
                }
                html5QrCode.stop().then(function() {
                    window.location.href = decodedText;
                }).catch(function() {});
            },
            function() {}
        ).catch(function(err) {
            showError('Camera niet beschikbaar: ' + (err.message || err));
        });
    }

    function stopScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(function() {
                try { html5QrCode.clear(); } catch (e) {}
                html5QrCode = null;
                overlay.style.display = 'none';
            }).catch(function() {
                html5QrCode = null;
                overlay.style.display = 'none';
            });
        } else {
            if (html5QrCode) {
                try { html5QrCode.clear(); } catch (e) {}
                html5QrCode = null;
            }
            overlay.style.display = 'none';
        }
    }

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) stopScanner();
    });

    btnScan.addEventListener('click', startScanner);
    btnClose.addEventListener('click', stopScanner);
})();
</script>
</body>
</html>
