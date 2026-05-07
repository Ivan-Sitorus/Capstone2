# Install CLI Tools — Learnings

## TiDB CLI (ticloud)
- Installed via official script: `curl -fsSL https://raw.githubusercontent.com/tidbcloud/tidbcloud-cli/main/install.sh | sh`
- Binary at: `~/.ticloud/bin/ticloud`
- Version: 1.0.0-beta.11
- PATH added to `~/.bashrc` automatically by installer
- Does NOT require Docker

## Cloudinary CLI (cld)
- Python 3.12 on Ubuntu 24.04 has PEP 668 (externally-managed-environment) protection
- `ensurepip` is NOT available on this system
- `python3 -m venv` also requires `python3-venv` (needs sudo)
- Workaround: `python3 /tmp/get-pip.py --break-system-packages` to install pip
- Then: `python3 -m pip install cloudinary-cli --break-system-packages`
- CLI installed to: `~/.local/bin/cld`
- Version: 1.14.1
- Does NOT require Docker

## Important Notes
- `source ~/.bashrc` doesn't reliably work in non-interactive bash subshells
- Always use full path or explicitly export PATH when testing
