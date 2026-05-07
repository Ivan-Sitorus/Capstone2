# Sudo Commands Reference — W9 Cafe POS Development

> Semua command yang butuh `sudo` untuk development environment.
> Update file ini setiap kali nemu command sudo baru.

---

## Version Check (May 2026)

| Tool | Versi Saat Ini | Versi Terbaru | Status |
|---|---|---|---|
| **PHP** | 8.5.5 | 8.5.5 | ✅ **Latest** |
| **Node.js** | 24.15.0 LTS | 24.15.0 LTS | ✅ **Latest LTS** (26.0.0 sudah rilis tapi masih Current) |
| **Composer** | 2.9.7 | 2.9.7 | ✅ **Latest stable** |
| **npm** | 11.12.1 | bawaan Node | ✅ Ikut Node |
| **Python** | 3.12.3 | 3.12.13 | ⚠️ **Bisa diupdate** (tapi ga wajib — datamining pake Docker Python 3.14) |

---

## Setup Urutan (WSL + Docker Desktop)

| Step | Lokasi | Yang dilakukan |
|---|---|---|
| **1** | **WSL terminal** | `sudo nano /etc/wsl.conf` — tambah `[automount]` |
| **2** | **PowerShell** | `notepad "$env:USERPROFILE\.wslconfig"` — isi memory=4GB, processors=2 |
| **3** | **PowerShell** | `wsl --shutdown` — restart WSL biar config terapply |
| **4** | **Windows browser** | Download & install [Docker Desktop](https://www.docker.com/products/docker-desktop/) |
| **5** | **Docker Desktop Settings** | Resources → WSL Integration → centang **Ubuntu-24.04** → Apply & Restart |
| **6** | **WSL terminal** | `docker ps` — verifikasi jalan tanpa sudo |
| **7** | **WSL terminal** | Ketik **"lanjut"** ke AI agent |

> **Tidak perlu** `sudo systemctl enable docker`, `sudo usermod -aG docker`, atau `curl get.docker.com`.
> Docker Desktop urus semuanya otomatis.

---

## WSL2 System Config

### Edit /etc/wsl.conf
```bash
sudo nano /etc/wsl.conf
```
Isi:
```ini
[boot]
systemd=true

[automount]
enabled = true
options = "metadata"
mountFsTab = false

[user]
default=nioha

[network]
generateResolvConf = true
```
**Tujuan:** WSL2 mount metadata permission + systemd.  
**Kapan:** Setup awal.

### Edit .wslconfig (dari PowerShell Windows)
```powershell
notepad "$env:USERPROFILE\.wslconfig"
```
Isi:
```ini
[wsl2]
memory=4GB
processors=2
localhostForwarding=true
```
**Tujuan:** Limit RAM/CPU WSL2 biar Windows tetap responsif.  
**Kapan:** Setup awal (skip dulu kalo ragu, bisa diisi nanti).  
⚠️ **RAM kamu 6GB, makanya diset 4GB** — sisanya buat Windows.

### Restart WSL setelah ubah config
```powershell
wsl --shutdown
```
**Tujuan:** Apply config.  
**Kapan:** Setelah ubah `/etc/wsl.conf` atau `.wslconfig`.

---

## Docker Desktop (Recommended)

### Install
Download dari https://www.docker.com/products/docker-desktop/
Install seperti aplikasi Windows biasa.

### Setup WSL Integration
1. Buka Docker Desktop → Settings (gear icon)
2. Resources → WSL Integration
3. Enable integration with **Ubuntu-24.04**
4. Apply & Restart

### Verifikasi (dari WSL terminal)
```bash
docker ps
docker compose version
```
**Tujuan:** Pastikan Docker jalan tanpa `sudo`.  
**Kapan:** Setelah install Docker Desktop.

---

## Update Tools

### Install Python 3.14 (via deadsnakes PPA)
```bash
sudo add-apt-repository ppa:deadsnakes/ppa
sudo apt update
sudo apt install python3.14 python3.14-venv python3.14-distutils
curl -sS https://bootstrap.pypa.io/get-pip.py | python3.14
```
**Tujuan:** Python 3.14 untuk development.  
**Kapan:** Optional — datamining juga pake Python 3.14 via Docker.

### Update Composer
```bash
sudo composer self-update
```
**Tujuan:** Update ke latest stable.  
**Kapan:** Sekali-sekali. Saat ini 2.9.7 sudah latest.

### Update npm packages project
```bash
npm update
```
**Tujuan:** Update dependencies project ke versi terbaru sesuai range di package.json.  
**Kapan:** Sesuai kebutuhan.  
⚠️ **Tidak perlu sudo.**

### Install system tools (optional)
```bash
sudo apt-get install -y curl wget unzip git
```
**Tujuan:** Tools dasar yang mungkin belum ada.  
**Kapan:** Setup awal (cek dulu pake `which curl wget unzip git`).

---

## Catatan

- **PHP 8.5, Node.js, Python 3.12, Composer** — semua sudah terinstall, tidak perlu sudo.
- **PostgreSQL, Redis, Reverb** — jalan lewat Docker (via Laravel Sail), tidak perlu install native.
- **FastAPI datamining** — jalan lewat Docker (Python 3.14 container).
- **Docker Desktop vs Engine Manual** — untuk WSL + AI agent, Docker Desktop lebih stabil. Zero sudo, auto-manage.
