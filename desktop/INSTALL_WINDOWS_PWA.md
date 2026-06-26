# GUÍA — INSTALAR NIGHTPOS CAJA EN WINDOWS (PWA)

**Para cajeras y admin en PC de sucursal.**  
**Versión:** V1 PWA  
**Compatible:** Windows 10/11, Edge o Chrome

---

## Objetivo

Instalar NightPOS Caja como app en la PC de sucursal para que:

- Abra desde un ícono en el escritorio (sin escribir URL).
- Tenga ventana propia (sin barras del navegador).
- Recuerde la empresa, sucursal y usuario.

---

## Requisitos previos

- PC con Windows 10 u 11.
- Microsoft Edge (incluido en Windows) o Google Chrome.
- Acceso a internet al hosting de NightPOS (HTTPS).
- URL del sistema (ejemplo: `https://pos.cliente.com`).
- Si hay impresión térmica: `NightPOSPrintAgent.exe` ya instalado como servicio (ver `agent/README_WINDOWS.md`).

---

## Instalación con Microsoft Edge (recomendado para Windows)

### Paso 1 — Abre Edge y entra al sistema

1. Abre **Microsoft Edge** (no Internet Explorer).
2. Ve a la URL de NightPOS: `https://pos.cliente.com`
3. Haz login con tu empresa, sucursal y usuario cajera/admin.

### Paso 2 — Instala la app

**Opción A — Aviso automático:**

NightPOS muestra un aviso azul:

> *Instala **NightPOS Caja** para acceso directo desde tu pantalla.*

Haz clic en **Instalar** → confirma → el ícono aparece en el escritorio.

**Opción B — Desde el menú de Edge:**

1. Haz clic en `⋯` (menú) arriba a la derecha.
2. Ve a **"Aplicaciones"**.
3. Haz clic en **"Instalar este sitio como una aplicación"**.
4. Ponle nombre: `NightPOS Caja` → clic **Instalar**.

### Paso 3 — Anclala a la barra de tareas (opcional pero recomendado)

1. Busca "NightPOS Caja" en el menú Inicio.
2. Clic derecho → **"Anclar a la barra de tareas"**.
3. Ahora aparece en la barra siempre visible.

### Paso 4 — Autoarranque (opcional)

Para que NightPOS Caja arranque al iniciar Windows:

1. Busca la app en el menú Inicio.
2. Clic derecho → **"Más"** → **"Abrir ubicación del archivo"**.
3. Copia el acceso directo (`Ctrl+C`).
4. Abre: `Win + R` → escribe `shell:startup` → Enter.
5. Pega el acceso directo en esa carpeta.

Ahora NightPOS abrirá automáticamente cuando enciendas la PC.

---

## Instalación con Google Chrome

1. Abre Chrome y ve a la URL de NightPOS.
2. Haz login.
3. Haz clic en `⋮` (tres puntos) arriba a la derecha.
4. Haz clic en **"Instalar NightPOS Caja…"** (o "Instalar esta aplicación").
5. El ícono aparece en el escritorio.

---

## Uso diario

### Primera vez (empresa/sucursal)

Si es la primera vez:

1. Selecciona **empresa** de la lista.
2. Selecciona **sucursal**.
3. Ingresa usuario y contraseña (modo password) o PIN.

### Después de la primera vez

La pantalla muestra directamente:

```
Empresa: Casa22
Sucursal: Centro
Usuario: cajero.demo

PIN: ____
```

Solo ingresa el PIN.

### Cambiar empresa / sucursal

En la pantalla de login:
- Botón **Cambiar empresa** o **Cambiar sucursal**.

---

## Agente de impresión

NightPOS Caja **no controla la impresora directamente** desde la app.

La impresión funciona así:

```
NightPOS Caja (PWA) → Backend hosting → Print jobs → NightPOS Print Agent → Impresora USB
```

El agente es un **servicio separado** (`NightPOSPrintAgent.exe`) que debe estar:
- ✅ Instalado como servicio Windows
- ✅ Configurado con `backend_url` y `device_key`
- ✅ Con estado **"connected"** en la bandeja del sistema

**Para ver estado del agente:** revisa el ícono en la bandeja del sistema (esquina inferior derecha).

**Si no imprime:** ve a NightPOS Admin → Configuración → Impresoras → revisa el dispositivo.

Instrucciones completas de instalación del agente: `agent/README_WINDOWS.md`

---

## Conexión

NightPOS necesita internet para operar.

- Si aparece **"Sin conexión"** en rojo: verifica la red o el HTTPS al hosting.
- El agente de impresión también necesita acceso al backend.

---

## Actualizaciones

Cuando hay una nueva versión de NightPOS:

- La app muestra: **"Nueva versión disponible → Actualizar"**.
- Haz clic en **Actualizar**.
- El agente de impresión se actualiza **por separado** (reemplazar EXE + `restart-service.bat`).

---

## Cambio de dominio del hosting

Si el dominio del sistema cambia:

1. Desinstala la app:
   - Menú Inicio → clic derecho → "Desinstalar".
   - O: Edge → `⋯` → Aplicaciones → "Administrar aplicaciones" → Eliminar.
2. Abre el **nuevo dominio** en el navegador.
3. Instala de nuevo.
4. **Actualiza también** `backend_url` en el `config.json` del agente de impresión.

---

## Problemas frecuentes

### "No veo opción de instalar"

- Asegúrate de estar en Edge o Chrome (no Firefox, no IE).
- El sitio debe estar en **HTTPS** — no funciona con HTTP.
- Verifica que el aviso no haya sido descartado antes.

### "La app abre pero no carga nada"

- Verifica internet y acceso al hosting.
- Cierra y abre de nuevo.

### "Cajera / admin no ven sus módulos"

- Verifica que el usuario tiene los permisos correctos.
- Si se cambió el rol: hacer logout y login de nuevo.

### "No imprime pero la app funciona"

- Verifica el agente de impresión (bandeja del sistema).
- Revisa logs: `C:\ProgramData\NightPOS\PrintAgent\logs\agent.log`.

---

## Resumen rápido (checklist instalación)

- [ ] PC Windows con Edge/Chrome
- [ ] URL HTTPS del sistema funcionando
- [ ] Login exitoso
- [ ] PWA instalada desde Edge/Chrome
- [ ] Ícono en escritorio y/o barra de tareas
- [ ] Autoarranque configurado (opcional)
- [ ] Print Agent instalado y estado `connected`
- [ ] Impresora registrada y `device_key` en `config.json`

---

## Soporte

Para problemas técnicos contacta al administrador de NightPOS.

Para el agente de impresión ver: `agent/README_WINDOWS.md` y `agent/TROUBLESHOOTING_GUIDE.md`.
