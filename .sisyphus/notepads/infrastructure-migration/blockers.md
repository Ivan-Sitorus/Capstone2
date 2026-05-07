# Blockers

## Docker Installation (BLOCKED — requires sudo)

**Date**: 2026-05-07
**Issue**: Docker daemon cannot start in rootless mode without the `newuidmap` setuid binary. The `uidmap` package (which provides this) requires `sudo apt-get install`.

**Root Cause**: 
- `newuidmap` needs `chmod u+s` (setuid bit) to configure user namespace UID/GID mappings
- Without setuid: `newuidmap: write to uid_map failed: Operation not permitted`
- This is a kernel-level restriction that cannot be bypassed without root

**Attempted Solutions**:
1. ✅ Rootless Docker script — requires uidmap package with setuid
2. ✅ Static Docker binary (v29.4.3) — installed but daemon won't start
3. ✅ rootlesskit + slirp4netns — both installed but blocked by newuidmap
4. ✅ docker-rootless-extras — all binaries extracted
5. ✅ User namespace support confirmed working (`unshare -U` works)
6. ❌ No Docker Desktop on Windows host
7. ❌ No sudo access to install uidmap package

**Workaround**: User needs to run one of:
```bash
# Option A: Install uidmap (simplest)
sudo apt-get update && sudo apt-get install -y uidmap

# Option B: Docker Desktop for Windows
# Download from https://www.docker.com/products/docker-desktop/
# Enable WSL2 integration in settings

# Option C: Full Docker Engine
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER
```

Once Docker is running, these steps remain:
1. `php artisan sail:install --with=pgsql,redis` → customize for PHP 8.5 + PG 18
2. Add Reverb, FastAPI (Python 3.14), queue worker to compose
3. `docker compose up -d` → smoke test
4. Playwright QA: 12 halaman kasir/customer
5. Playwright QA: 24 halaman Filament admin panel
6. Final review: 4 agents (Oracle audit + code quality + QA + scope check)
