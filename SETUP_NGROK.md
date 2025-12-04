# 🌐 Setup Ngrok untuk MyBBA System

## Problem yang Diselesaikan
- ❌ OCR Server tidak bisa diakses dari device lain via ngrok
- ❌ CORS policy blocking
- ❌ Hardcoded localhost URL

## ✅ Solusi yang Diterapkan

### 1. Dynamic API URL
- PHP config menggunakan environment variable `OCR_API_URL`
- Fallback ke `http://localhost:8000` jika tidak ada env variable
- JavaScript menggunakan `<?php echo OCR_API_URL; ?>` untuk dynamic URL

### 2. CORS Configuration Updated
- OCR server allow all origins (`allow_origins=["*"]`)
- Support untuk ngrok dan domain development

---

## 📋 SETUP INSTRUCTIONS

### Option A: Single Ngrok Tunnel (RECOMMENDED untuk Testing)

#### Step 1: Expose OCR Server via Ngrok
```powershell
# Terminal 1: Start OCR Server
cd F:\laragon\www\mybba\ocr_system
python main.py

# Terminal 2: Expose OCR Server
ngrok http 8000
```

**Copy ngrok URL**, contoh: `https://abc123.ngrok-free.app`

#### Step 2: Set Environment Variable
```powershell
# Set untuk session sekarang
$env:OCR_API_URL = "https://abc123.ngrok-free.app"

# Atau permanent (User level)
[System.Environment]::SetEnvironmentVariable("OCR_API_URL", "https://abc123.ngrok-free.app", "User")

# Restart PowerShell setelah set permanent
```

#### Step 3: Start Apache/Laragon
```powershell
# Restart Apache untuk apply env variable
# Atau restart Laragon
```

#### Step 4: Expose Web App via Ngrok
```powershell
# Terminal 3: Expose Laravel/PHP App
ngrok http 80
```

**Copy ngrok URL untuk web**, contoh: `https://xyz789.ngrok-free.app`

#### Step 5: Access System
- Buka browser: `https://xyz789.ngrok-free.app/mybba`
- Login dan test upload bukti transfer
- OCR akan hit `https://abc123.ngrok-free.app/api/v1/validate-transfer`

---

### Option B: Using ngrok config file (Multiple Tunnels)

#### Step 1: Create ngrok.yml
```yaml
version: "2"
authtoken: YOUR_NGROK_AUTH_TOKEN

tunnels:
  mybba-web:
    proto: http
    addr: 80
    hostname: mybba-web.ngrok-free.app  # Optional custom domain (requires paid plan)
    
  mybba-ocr:
    proto: http
    addr: 8000
    hostname: mybba-ocr.ngrok-free.app  # Optional custom domain (requires paid plan)
```

#### Step 2: Start Multiple Tunnels
```powershell
# Start both tunnels
ngrok start --all --config ngrok.yml
```

#### Step 3: Set Environment Variable
```powershell
$env:OCR_API_URL = "https://mybba-ocr.ngrok-free.app"
[System.Environment]::SetEnvironmentVariable("OCR_API_URL", "https://mybba-ocr.ngrok-free.app", "User")
```

---

### Option C: Using Reverse Proxy (Production-like)

Jika Anda punya VPS/Server:

#### Setup Nginx Reverse Proxy
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    
    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
    
    location /ocr/ {
        proxy_pass http://localhost:8000/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

Set env:
```bash
export OCR_API_URL="http://yourdomain.com/ocr"
```

---

## 🔍 Troubleshooting

### Error: "Failed to fetch localhost:8000"
**Cause**: Environment variable tidak ke-set atau Apache belum restart

**Solution**:
```powershell
# Check env variable
echo $env:OCR_API_URL

# Set ulang
$env:OCR_API_URL = "https://your-ngrok-url.ngrok-free.app"

# Restart Apache
```

### Error: "CORS policy blocking"
**Cause**: OCR server belum di-restart setelah config update

**Solution**:
```powershell
# Restart OCR server
cd F:\laragon\www\mybba\ocr_system
python main.py
```

### Error: "net::ERR_FAILED" 
**Cause**: Ngrok tunnel mati atau OCR server tidak running

**Solution**:
```powershell
# Check OCR server
curl http://localhost:8000/health

# Check ngrok status
curl https://your-ngrok-url.ngrok-free.app/health
```

### Error: Ngrok "Visit Site" Button
**Cause**: Ngrok free plan menampilkan warning page

**Solution**:
- Click "Visit Site" button
- Atau upgrade ke ngrok paid plan
- Atau gunakan alternatif: localtunnel, serveo

---

## 🚀 Quick Start Command

```powershell
# All-in-one setup
cd F:\laragon\www\mybba

# Terminal 1
cd ocr_system; python main.py

# Terminal 2
ngrok http 8000
# Copy URL, then:
$env:OCR_API_URL = "https://ABC123.ngrok-free.app"

# Terminal 3 (after setting env)
ngrok http 80
# Access: https://XYZ789.ngrok-free.app/mybba
```

---

## 📊 Architecture Diagram

```
┌─────────────────┐
│  Client Device  │
│  (Any Network)  │
└────────┬────────┘
         │ HTTPS
         ▼
┌─────────────────┐
│   Ngrok Web     │
│ xyz789.ngrok... │
└────────┬────────┘
         │
         ▼
┌─────────────────┐       ┌─────────────────┐
│   PHP App       │──────▶│   Ngrok OCR     │
│  (Laragon:80)   │       │ abc123.ngrok... │
└─────────────────┘       └────────┬────────┘
                                   │
                                   ▼
                          ┌─────────────────┐
                          │  OCR Server     │
                          │  (FastAPI:8000) │
                          └─────────────────┘
```

---

## 🔐 Security Notes

⚠️ **WARNING**: `allow_origins=["*"]` tidak aman untuk production!

**Untuk Production**, update `main.py`:
```python
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://yourdomain.com",
        "https://www.yourdomain.com"
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

---

## 📝 Alternative Solutions

### 1. **Cloudflare Tunnel** (Free, No Account Limit)
```bash
cloudflared tunnel --url http://localhost:8000
```

### 2. **LocalTunnel** (Free, Simple)
```bash
npm install -g localtunnel
lt --port 8000 --subdomain mybba-ocr
```

### 3. **Serveo** (Free, SSH-based)
```bash
ssh -R 80:localhost:8000 serveo.net
```

### 4. **Deploy OCR ke Cloud**
- Google Cloud Run (Free tier: 2M requests/month)
- AWS Lambda + API Gateway
- Heroku (Free dyno)
- Railway.app
- Render.com

---

## ✅ Verification Checklist

- [ ] OCR server running (`http://localhost:8000/health`)
- [ ] Ngrok tunnel active untuk OCR (port 8000)
- [ ] Environment variable `OCR_API_URL` set
- [ ] Apache/Laragon restarted
- [ ] Ngrok tunnel active untuk web (port 80)
- [ ] CORS allow origins updated
- [ ] Browser console shows correct OCR URL
- [ ] Upload test berhasil tanpa CORS error

---

**Created**: December 4, 2025
**Last Updated**: December 4, 2025
