# 🛡️ SIGPOL

**Proyecto TFC — FP Superior ASIR**  
- Sistema integral para la gestión de denuncias de la Guardia Civil.  
- Implementación de un CPD con alta disponibilidad y tolerancia a fallos.
Registro, consulta y administración centralizada desde múltiples puestos distribuidos geográficamente.  
✅ Reproducible · 🐳 Dockerizado · 📚 Documentado · 🔐 Seguro · ⚡ Escalable

---

## ⚙️ Arquitectura

🧭 Balanceadores:
  🖥️ balanceador1:
    - 🔀 Traefik
    - 🛡️ Keepalived (activo)
  🖥️ balanceador2:
    - 🔀 Traefik
    - 🛡️ Keepalived (pasivo)

🌐 Servidores Web:
  🖥️ servidorWeb1:
    - 🐳 Docker:
        - 📦 web1: NGINX + PHP
        - 📦 web2: NGINX + PHP
  🖥️ servidorWeb2:
    - 🐳 Docker:
        - 📦 web3: NGINX + PHP
        - 📦 web4: NGINX + PHP

🗄️ Base de Datos:
  🖥️ servidorSQL:
    - 🪟 Windows Server
    - 🧠 SQL Server
    - 📄 estructura.sql

💾 Backup:
  🖥️ servidorBackup:
    - 📦 Copia de seguridad (.bak)
    - ☁️ Sincronización con Google Drive
    - 🔁 Automatización con RClone

---
