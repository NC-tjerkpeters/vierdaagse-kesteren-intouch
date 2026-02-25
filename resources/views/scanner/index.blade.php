@extends('scanner.layout')

@section('title', 'Scannen')
@section('header_title', $currentDay ? $currentDay->name : 'QR Scanner')

@section('content')
<div id="qr-reader"></div>
<p class="small text-muted mt-2 px-2">Richt de QR-code van het ticket in het kader. Min. {{ config('scanner.min_minutes_between_scans', 5) }} min tussen twee scans.</p>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function() {
    const POST_URL = @json(route('scanner.scan.api'));
    const DEBOUNCE_MS = 2000;
    const TIMEOUT_MS = 8000;
    const STORAGE_KEY = 'qr_preferred_camera_id';

    let html5QrCode = null;
    let lastVal = '', lastAt = 0;
    let torchOn = false;

    function getCsrfToken() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function debounceSame(v) {
        const now = Date.now();
        if (v === lastVal && (now - lastAt) < DEBOUNCE_MS) return true;
        lastVal = v;
        lastAt = now;
        return false;
    }

    async function postJson(url, data, timeoutMs) {
        const controller = new AbortController();
        const t = setTimeout(() => controller.abort(), timeoutMs);
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data),
                signal: controller.signal
            });
            let payload = null;
            try { payload = await res.json(); } catch (_) {}
            return { res, payload };
        } finally { clearTimeout(t); }
    }

    function sanitizeMessage(m) {
        const t = document.createElement('template');
        t.innerHTML = String(m ?? '');
        t.content.querySelectorAll('script,iframe,object,embed,link,meta,style').forEach(n => n.remove());
        const allowed = new Set(['BR','B','STRONG','I','EM','U','P','SMALL','SPAN','UL','OL','LI']);
        [...(t.content.querySelectorAll('*') || [])].forEach(el => {
            if (!allowed.has(el.tagName)) {
                const frag = document.createDocumentFragment();
                while (el.firstChild) frag.appendChild(el.firstChild);
                el.replaceWith(frag);
            } else {
                [...(el.attributes || [])].forEach(a => {
                    const n = a.name.toLowerCase();
                    if (n.startsWith('on') || n === 'style' || n === 'href' || n === 'src') el.removeAttribute(a.name);
                });
            }
        });
        return t.innerHTML;
    }

    const successBox = (msg) => Swal.fire({
        icon: 'success',
        title: 'Gelukt!',
        html: sanitizeMessage(msg || 'Actie voltooid.'),
        confirmButtonColor: '#43a047',
        allowOutsideClick: false
    });

    const errorBox = (msg) => Swal.fire({
        icon: 'error',
        title: 'Oops…',
        html: sanitizeMessage(msg || 'Er ging iets mis'),
        confirmButtonColor: '#e53935',
        allowOutsideClick: false
    });

    const infoBox = (msg) => Swal.fire({
        icon: 'info',
        title: 'Info',
        html: sanitizeMessage(msg || ''),
        confirmButtonColor: '#2196f3',
        allowOutsideClick: false
    });

    async function stopIfRunning() {
        if (!html5QrCode) return;
        try { if (html5QrCode.isScanning) await html5QrCode.stop(); } catch (_) {}
        try { await html5QrCode.clear(); } catch (_) {}
    }

    function pickBestIndex(list) {
        if (!list || !list.length) return -1;
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const i = list.findIndex(c => c.id === saved);
            if (i >= 0) return i;
        }
        const re = /(back|rear|environment)/i;
        const j = list.findIndex(c => re.test(c.label || ''));
        if (j >= 0) return j;
        if (list.length > 1) return list.length - 1;
        return 0;
    }

    async function startWithDeviceId(deviceId) {
        await stopIfRunning();
        html5QrCode = new Html5Qrcode('qr-reader', { verbose: false });
        const cfg = {
            fps: 10,
            qrbox: (w, h) => { const s = Math.max(220, Math.min(340, Math.floor(Math.min(w, h) * 0.8))); return { width: s, height: s }; },
            aspectRatio: 1.0,
            showTorchButtonIfSupported: true,
            showZoomSliderIfSupported: true
        };
        await html5QrCode.start(deviceId, cfg, onScanSuccess, onScanFailure);
        try { localStorage.setItem(STORAGE_KEY, deviceId); } catch (_) {}
    }

    async function startAuto() {
        try {
            await stopIfRunning();
            html5QrCode = new Html5Qrcode('qr-reader', { verbose: false });
            const cfg = {
                fps: 10,
                qrbox: (w, h) => { const s = Math.max(220, Math.min(340, Math.floor(Math.min(w, h) * 0.8))); return { width: s, height: s }; },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                showZoomSliderIfSupported: true
            };
            await html5QrCode.start({ facingMode: 'environment' }, cfg, onScanSuccess, onScanFailure);
        } catch (_) {
            try {
                const cams = await Html5Qrcode.getCameras();
                if (!cams || !cams.length) return errorBox('Geen camera gevonden of toestemming geweigerd.');
                const idx = pickBestIndex(cams);
                await startWithDeviceId(cams[Math.max(0, idx)].id);
            } catch (e) {
                const msg = (e && e.message) ? e.message : String(e || 'Onbekende fout');
                if (/NotAllowedError|Permission/.test(msg)) return errorBox('Camera-toegang geweigerd. Sta toegang toe in de browserinstellingen.');
                return errorBox(msg);
            }
        }
    }

    document.getElementById('btn-torch').addEventListener('click', async function() {
        try {
            const video = document.querySelector('video');
            const track = video && video.srcObject && video.srcObject.getVideoTracks && video.srcObject.getVideoTracks()[0];
            const caps = track && track.getCapabilities && track.getCapabilities();
            if (caps && caps.torch) {
                torchOn = !torchOn;
                await track.applyConstraints({ advanced: [{ torch: torchOn }] });
                this.textContent = torchOn ? 'Lampje uit' : 'Lampje aan';
            } else {
                errorBox('Deze camera ondersteunt geen lampje.');
            }
        } catch (_) {
            errorBox('Kon lampje niet schakelen.');
        }
    });

    async function onScanSuccess(text) {
        if (debounceSame(text)) return;
        try {
            if (html5QrCode && html5QrCode.pause) html5QrCode.pause(true);
            const { res, payload } = await postJson(POST_URL, { qrcode_values: text }, TIMEOUT_MS);
            if (res.ok && payload) {
                if (payload.status === 'ok') {
                    await successBox(payload.message || 'Actie voltooid.');
                } else if (payload.status === 'info') {
                    await infoBox(payload.message || '');
                } else {
                    await errorBox((payload.message) || 'Er ging iets mis.');
                }
            } else {
                await errorBox((payload && payload.message) || 'Serverfout (' + (res.status || 'onbekend') + ')');
            }
        } catch (e) {
            await errorBox((e && e.name === 'AbortError') ? 'Timeout: server reageerde niet.' : 'Netwerkfout. Probeer opnieuw.');
        } finally {
            lastVal = '';
            if (html5QrCode && html5QrCode.resume) { try { html5QrCode.resume(); } catch (_) {} }
        }
    }

    function onScanFailure(_err) {}

    startAuto();
})();
</script>
@endpush
