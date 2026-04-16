<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Image Watermark Tool</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg: #0d0f14;
      --card: #161a22;
      --card2: #1e2330;
      --border: #2a3040;
      --accent: #4f8ef7;
      --accent2: #7c5cfc;
      --green: #22c55e;
      --red: #ef4444;
      --text: #e8eaf0;
      --muted: #8892a4;
      --radius: 14px;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 32px 16px 60px;
    }

    /* ── Header ── */
    .header {
      text-align: center;
      margin-bottom: 36px;
    }

    .header .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, rgba(79, 142, 247, .18), rgba(124, 92, 252, .18));
      border: 1px solid rgba(79, 142, 247, .35);
      border-radius: 999px;
      padding: 5px 14px;
      font-size: 12px;
      font-weight: 600;
      color: var(--accent);
      letter-spacing: .5px;
      margin-bottom: 14px;
    }

    .header h1 {
      font-size: clamp(26px, 5vw, 40px);
      font-weight: 700;
      background: linear-gradient(135deg, #e8eaf0 30%, #4f8ef7 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1.2;
    }

    .header p {
      color: var(--muted);
      margin-top: 8px;
      font-size: 15px;
    }

    /* ── Layout ── */
    .container {
      width: 100%;
      max-width: 960px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }

    @media (max-width: 700px) {
      .container {
        grid-template-columns: 1fr;
      }
    }

    /* ── Card ── */
    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px;
    }

    .card-title {
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--muted);
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-title span {
      font-size: 16px;
    }

    /* ── Upload Zone ── */
    .upload-zone {
      border: 2px dashed var(--border);
      border-radius: 10px;
      padding: 36px 20px;
      text-align: center;
      cursor: pointer;
      transition: all .25s;
      position: relative;
      overflow: hidden;
    }

    .upload-zone:hover,
    .upload-zone.dragging {
      border-color: var(--accent);
      background: rgba(79, 142, 247, .06);
    }

    .upload-zone input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      z-index: 2;
    }

    .upload-zone .icon {
      font-size: 40px;
      margin-bottom: 10px;
      display: block;
      filter: drop-shadow(0 0 12px rgba(79, 142, 247, .4));
    }

    .upload-zone p {
      color: var(--muted);
      font-size: 14px;
    }

    .upload-zone strong {
      color: var(--accent);
    }

    /* ── Image Preview ── */
    #previewBox {
      display: none;
      margin-top: 16px;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid var(--border);
    }

    #previewBox img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      display: block;
    }

    .preview-name {
      background: var(--card2);
      padding: 8px 12px;
      font-size: 13px;
      color: var(--muted);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .preview-name button {
      background: none;
      border: none;
      color: var(--red);
      cursor: pointer;
      font-size: 16px;
    }

    /* ── Form Fields ── */
    .field {
      margin-bottom: 16px;
    }

    label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: var(--muted);
      margin-bottom: 6px;
    }

    input[type="text"],
    input[type="datetime-local"] {
      width: 100%;
      background: var(--card2);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 10px 14px;
      color: var(--text);
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      outline: none;
      transition: border-color .2s;
    }

    input:focus {
      border-color: var(--accent);
    }

    input::placeholder {
      color: #4a5568;
    }

    /* ── Location Block ── */
    .loc-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    #btn-location {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 11px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: var(--card2);
      color: var(--text);
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all .22s;
      font-family: 'Inter', sans-serif;
      margin-bottom: 12px;
    }

    #btn-location:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    #btn-location.loading {
      opacity: .7;
      pointer-events: none;
    }

    .loc-status {
      font-size: 12px;
      padding: 6px 10px;
      border-radius: 6px;
      margin-bottom: 12px;
      display: none;
    }

    .loc-status.success {
      background: rgba(34, 197, 94, .12);
      color: var(--green);
      display: block;
      border: 1px solid rgba(34, 197, 94, .2);
    }

    .loc-status.error {
      background: rgba(239, 68, 68, .12);
      color: var(--red);
      display: block;
      border: 1px solid rgba(239, 68, 68, .2);
    }

    /* ── Submit Button ── */
    #btn-submit {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      transition: opacity .2s, transform .15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 8px;
    }

    #btn-submit:hover {
      opacity: .9;
      transform: translateY(-1px);
    }

    #btn-submit:active {
      transform: translateY(0);
    }

    #btn-submit:disabled {
      opacity: .5;
      pointer-events: none;
    }

    /* ── Spinner ── */
    .spinner {
      width: 18px;
      height: 18px;
      border: 2px solid rgba(255, 255, 255, .3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .7s linear infinite;
      display: none;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    /* ── Result ── */
    #result-section {
      display: none;
      grid-column: 1 / -1;
    }

    .result-img-wrap {
      position: relative;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid var(--border);
    }

    .result-img-wrap img {
      width: 100%;
      display: block;
      max-height: 500px;
      object-fit: contain;
      background: #000;
    }

    .result-actions {
      display: flex;
      gap: 10px;
      margin-top: 14px;
      flex-wrap: wrap;
    }

    .btn-dl {
      flex: 1;
      min-width: 140px;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid var(--accent);
      background: rgba(79, 142, 247, .1);
      color: var(--accent);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      text-align: center;
      text-decoration: none;
      transition: background .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .btn-dl:hover {
      background: rgba(79, 142, 247, .2);
    }

    .btn-reset {
      flex: 1;
      min-width: 140px;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: var(--card2);
      color: var(--muted);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      transition: border-color .2s;
    }

    .btn-reset:hover {
      border-color: var(--muted);
    }

    /* ── Toast ── */
    #toast {
      position: fixed;
      bottom: 28px;
      right: 28px;
      background: var(--card2);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 12px 18px;
      font-size: 14px;
      color: var(--text);
      display: none;
      align-items: center;
      gap: 8px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, .5);
      z-index: 999;
      animation: slideUp .3s ease;
    }

    @keyframes slideUp {
      from {
        transform: translateY(20px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
  </style>
</head>

<body>

  <div class="header">
    <div class="badge">📍 Location-Based Watermarking</div>
    <h1>Image Watermark Tool</h1>
    <p>Upload your image, detect location — watermark will be auto-applied</p>
  </div>

  <div class="container">

    <!-- LEFT: Upload + Form -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

      <!-- Upload Card -->
      <div class="card">
        <div class="card-title"><span>📸</span> Image Upload</div>

        <div class="upload-zone" id="uploadZone">
          <input type="file" id="imageInput" accept="image/jpeg,image/png,image/gif" />
          <span class="icon">🖼️</span>
          <p><strong>Click to choose</strong> or drag & drop</p>
          <p style="margin-top:4px; font-size:12px;">JPG, PNG, GIF supported</p>
        </div>

        <div id="previewBox">
          <img id="previewImg" src="" alt="Preview" />
          <div class="preview-name">
            <span id="previewName">image.jpg</span>
            <button onclick="clearImage()" title="Remove">✕</button>
          </div>
        </div>
      </div>

      <!-- Details Card -->
      <div class="card">
        <div class="card-title"><span>✏️</span> Watermark Details</div>

        <div class="field">
          <label>Name / Label</label>
          <input type="text" id="inp-text" placeholder="e.g. Site Name" />
        </div>

        <div class="field">
          <label>Date & Time <span style="font-size:11px;color:var(--accent);font-weight:400;">● LIVE</span></label>
          <div id="live-clock" style="
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            font-family: 'Inter', monospace;
            color: var(--text);
            letter-spacing: 0.5px;
          ">--</div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Location + Action -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

      <!-- Location Card -->
      <div class="card">
        <div class="card-title"><span>📍</span> Location</div>

        <button id="btn-location" onclick="getLocation()">
          <span>📡</span> Detect My Location
        </button>

        <div class="loc-status" id="locStatus"></div>

        <div class="field">
          <label>Address</label>
          <input type="text" id="inp-address" placeholder="Auto-filled or type manually" />
        </div>

        <div class="loc-row">
          <div class="field" style="margin-bottom:0">
            <label>Latitude</label>
            <input type="text" id="inp-lat" placeholder="e.g. 28.6139 N" />
          </div>
          <div class="field" style="margin-bottom:0">
            <label>Longitude</label>
            <input type="text" id="inp-lon" placeholder="e.g. 77.2090 E" />
          </div>
        </div>
      </div>

      <!-- Action Card -->
      <div class="card">
        <div class="card-title"><span>🚀</span> Apply Watermark</div>
        <p style="font-size:13px; color:var(--muted); margin-bottom:16px; line-height:1.6;">
          Fill in the details and click <strong style="color:var(--text)">Apply Watermark</strong>.
          The watermarked image will appear below and you can download it.
        </p>

        <button id="btn-submit" onclick="applyWatermark()">
          <div class="spinner" id="spinner"></div>
          <span id="btn-label">⚡ Apply Watermark</span>
        </button>
      </div>
    </div>

    <!-- RESULT: Full Width -->
    <div class="card" id="result-section">
      <div class="card-title"><span>✅</span> Watermarked Image</div>
      <div class="result-img-wrap">
        <img id="resultImg" src="" alt="Watermarked Result" />
      </div>
      <div class="result-actions">
        <a id="btn-download" class="btn-dl" href="#" download="watermarked.jpg">⬇️ Download Image</a>
        <button class="btn-reset" onclick="resetAll()">🔄 Process Another</button>
      </div>
    </div>

  </div>

  <div id="toast"></div>

  <script>
    // ── Live Real-Time Clock (browser system time) ──
    const clockEl = document.getElementById('live-clock');
    function updateClock() {
      const now = new Date();
      const date = now.toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' });
      const time = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
      clockEl.textContent = date + '  |  ' + time;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── Drag & Drop ──
    const zone = document.getElementById('uploadZone');
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragging'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragging'));
    zone.addEventListener('drop', e => {
      e.preventDefault();
      zone.classList.remove('dragging');
      const file = e.dataTransfer.files[0];
      if (file) loadFile(file);
    });

    document.getElementById('imageInput').addEventListener('change', function () {
      if (this.files[0]) loadFile(this.files[0]);
    });

    function loadFile(file) {
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('previewName').textContent = file.name;
        document.getElementById('previewBox').style.display = 'block';
      };
      reader.readAsDataURL(file);
      // store file on input
      const dt = new DataTransfer();
      dt.items.add(file);
      document.getElementById('imageInput').files = dt.files;
    }

    function clearImage() {
      document.getElementById('imageInput').value = '';
      document.getElementById('previewBox').style.display = 'none';
      document.getElementById('previewImg').src = '';
    }

    // ── Geolocation — 3-step fallback ──
    // Step 1: Browser GPS/WiFi  →  Step 2: IP-based (ipapi.co)  →  Step 3: ip-api.com

    function getLocation() {
      const btn = document.getElementById('btn-location');
      btn.classList.add('loading');
      btn.innerHTML = '<span>⏳</span> Detecting location...';
      setLocStatus('', '');

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          pos => onGotCoords(pos.coords.latitude, pos.coords.longitude, 'browser'),
          () => {
            btn.innerHTML = '<span>⏳</span> Trying IP-based location...';
            getIPLocation(btn);
          },
          { timeout: 8000, enableHighAccuracy: false, maximumAge: 30000 }
        );
      } else {
        getIPLocation(btn);
      }
    }

    function getIPLocation(btn) {
      fetch('https://ipapi.co/json/')
        .then(r => r.json())
        .then(data => {
          if (data && data.latitude) {
            onGotCoords(data.latitude, data.longitude, 'ip', data);
          } else { throw new Error(); }
        })
        .catch(() => {
          fetch('https://ip-api.com/json/')
            .then(r => r.json())
            .then(data => {
              if (data && data.lat) {
                onGotCoords(data.lat, data.lon, 'ip', { city: data.city, region: data.regionName, country_name: data.country });
              } else { throw new Error(); }
            })
            .catch(() => {
              btn.classList.remove('loading');
              btn.innerHTML = '<span>📡</span> Detect My Location';
              setLocStatus('error', 'Could not detect location automatically. Please enter manually.');
            });
        });
    }

    function onGotCoords(lat, lon, source, ipData) {
      const btn = document.getElementById('btn-location');
      const latDir = lat >= 0 ? 'N' : 'S';
      const lonDir = lon >= 0 ? 'E' : 'W';
      document.getElementById('inp-lat').value = Math.abs(lat).toFixed(6) + '° ' + latDir;
      document.getElementById('inp-lon').value = Math.abs(lon).toFixed(6) + '° ' + lonDir;

      if (ipData && ipData.city) {
        const parts = [ipData.city, ipData.region, ipData.country_name].filter(Boolean);
        document.getElementById('inp-address').value = parts.join(', ');
        btn.classList.remove('loading');
        btn.innerHTML = '<span>✅</span> Location Detected';
        const note = source === 'ip' ? ' (approximate — via IP)' : '';
        setLocStatus('success', '📍 Location detected successfully!' + note);
        return;
      }

      fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)
        .then(r => r.json())
        .then(data => {
          document.getElementById('inp-address').value = data.display_name || '';
          const note = source === 'ip' ? ' (approximate — via IP)' : '';
          setLocStatus('success', '📍 Location detected successfully!' + note);
        })
        .catch(() => {
          setLocStatus('success', '📍 Coordinates set. Address could not be fetched.');
        })
        .finally(() => {
          btn.classList.remove('loading');
          btn.innerHTML = '<span>✅</span> Location Detected';
        });
    }

    function setLocStatus(type, msg) {
      const el = document.getElementById('locStatus');
      el.className = 'loc-status ' + type;
      el.textContent = msg;
    }

    // ── Apply Watermark ──
    // async function applyWatermark() {
    //   const fileInput = document.getElementById('imageInput');
    //   if (!fileInput.files || !fileInput.files[0]) {
    //     toast('⚠️ Please select an image first!'); return;
    //   }

    //   const text = document.getElementById('inp-text').value.trim();
    //   const address = document.getElementById('inp-address').value.trim();
    //   const lat = document.getElementById('inp-lat').value.trim();
    //   const lon = document.getElementById('inp-lon').value.trim();

    //   // Capture current real-time datetime at the moment of submit
    //   const now = new Date();
    //   const datetimeStr = now.toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' })
    //     + ' ' + now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

    //   // Build FormData
    //   const fd = new FormData();
    //   fd.append('image', fileInput.files[0]);
    //   if (text) fd.append('text', text);
    //   if (address) fd.append('address', address);
    //   if (lat) fd.append('lat', lat);
    //   if (lon) fd.append('lon', lon);
    //   if (datetimeStr) fd.append('datetime', datetimeStr);
      
    //   const API_URL = 'https://karvy.sarsspl.com/api/image_watermarker.php';

    //   // Loading state
    //   setLoading(true);

    //   try {
    //     const res = await fetch(API_URL, { method: 'POST', body: fd });
    //     const data = await res.json();

    //     if (data.status === 'success') {
            
    //         console.log("Response",data);
    //       // Build full URL to saved image
    //       const imgUrl = data.saved_at + '?t=' + Date.now();
    //       const resultImg = document.getElementById('resultImg');
    //       resultImg.src = imgUrl;
    //       document.getElementById('btn-download').href = imgUrl;
    //       document.getElementById('btn-download').download = 'watermarked_' + Date.now() + '.jpg';

    //       // Show result
    //       document.getElementById('result-section').style.display = 'block';
    //       document.getElementById('result-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    //       toast('✅ Watermark successfully applied!');
    //     } else {
    //       toast('❌ ' + (data.message || 'Something went wrong.'));
    //     }
    //   } catch (err) {
    //     toast('❌ Could not connect to the server. Please check if XAMPP is running.');
    //   } finally {
    //     setLoading(false);
    //   }
    // }

    async function applyWatermark() {
      const fileInput = document.getElementById('imageInput');
      if (!fileInput.files || !fileInput.files[0]) {
        toast('⚠️ Please select an image first!');
        return;
      }
    
      setLoading(true);
    
      let text = document.getElementById('inp-text').value.trim();
      let address = '';
      let lat = '';
      let lon = '';
    
      try {
        // ── AUTO LOCATION FETCH ──
        const position = await new Promise((resolve, reject) => {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
              timeout: 8000
            });
          } else {
            reject();
          }
        });
    
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
    
        lat = latitude.toFixed(6);
        lon = longitude.toFixed(6);
    
        // Reverse geocoding (address)
        try {
          const resGeo = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`);
          const geoData = await resGeo.json();
          address = geoData.display_name || '';
        } catch (e) {
          address = '';
        }
    
      } catch (err) {
        console.log("GPS failed, fallback to IP");
    
        // ── FALLBACK IP LOCATION ──
        try {
          const res = await fetch('https://ipapi.co/json/');
          const data = await res.json();
    
          lat = data.latitude || '';
          lon = data.longitude || '';
          address = `${data.city || ''}, ${data.region || ''}, ${data.country_name || ''}`;
        } catch (e) {
          toast('⚠️ Location not available');
        }
      }
    
      // ── CURRENT DATETIME ──
      const now = new Date();
      const datetimeStr =
        now.toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
        ' ' +
        now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    
      // ── FORM DATA ──
      const fd = new FormData();
      fd.append('image', fileInput.files[0]);
      if (text) fd.append('text', text);
      if (address) fd.append('address', address);
      if (lat) fd.append('lat', lat);
      if (lon) fd.append('lon', lon);
      fd.append('datetime', datetimeStr);
    
      const API_URL = 'https://karvy.sarsspl.com/api/image_watermarker.php';
    
      try {
        const res = await fetch(API_URL, { method: 'POST', body: fd });
        const data = await res.json();
    
        if (data.status === 'success') {
    
          console.log("aasdasda",data);
          const imgUrl = data.saved_at + '?t=' + Date.now();
          
          document.getElementById('resultImg').src = imgUrl;
          document.getElementById('btn-download').href = imgUrl;
    
          document.getElementById('result-section').style.display = 'block';
    
          toast('✅ Watermark with location applied!');
        } else {
          toast('❌ ' + data.message);
        }
    
      } catch (err) {
        toast('❌ Server error');
      } finally {
        setLoading(false);
      }
    }

    function setLoading(on) {
      const btn = document.getElementById('btn-submit');
      const lbl = document.getElementById('btn-label');
      const spinner = document.getElementById('spinner');
      btn.disabled = on;
      spinner.style.display = on ? 'block' : 'none';
      lbl.textContent = on ? 'Processing...' : '⚡ Apply Watermark';
    }

    function resetAll() {
      clearImage();
      document.getElementById('result-section').style.display = 'none';
      document.getElementById('resultImg').src = '';
      document.getElementById('inp-text').value = '';
      document.getElementById('inp-address').value = '';
      document.getElementById('inp-lat').value = '';
      document.getElementById('inp-lon').value = '';
      document.getElementById('btn-location').innerHTML = '<span>📡</span> Detect My Location';
      setLocStatus('', '');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Toast ──
    let toastTimer;
    function toast(msg) {
      const el = document.getElementById('toast');
      el.textContent = msg;
      el.style.display = 'flex';
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => { el.style.display = 'none'; }, 3500);
    }
  </script>
</body>

</html>