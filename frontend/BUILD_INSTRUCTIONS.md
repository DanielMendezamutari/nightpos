# Frontend - Compilación para Desarrollo y Hosting

## 📋 Configuración

El frontend está configurado para conectarse a diferentes APIs según el entorno:

### Desarrollo Local
- **Archivo**: `.env`
- **API**: `http://nightpos.test/api`
- **Comando**: `npm run dev`

### Producción / Hosting
- **Archivo**: `.env.production`
- **API**: `https://nightpos.ribersoft.com/api`
- **Comando**: `npm run build`

---

## 🚀 Cómo Usar

### 1. **Para Desarrollo Local**
```bash
cd frontend
npm install
npm run dev
```
Esto iniciará el servidor de desarrollo en `http://localhost:5173` y se conectará a `http://nightpos.test/api`.

### 2. **Para Compilar para Hosting**
```bash
cd frontend
npm run build
```
Esto generará los archivos compilados en la carpeta `dist/` listos para subir a tu hosting. Estos archivos se conectarán a `https://nightpos.ribersoft.com/api`.

### 3. **Previsualizar Build de Producción**
```bash
npm run preview
```
Esto te permite previsualizar cómo se verá en producción localmente.

---

## 📁 Estructura de Compilación

Después de ejecutar `npm run build`, encontrarás:

```
dist/
├── index.html          (archivo principal)
├── assets/             (JS, CSS compilados)
└── ...
```

Estos archivos son los que debes subir a tu hosting (generalmente a la carpeta `public_html` o similar).

---

## 🔧 Diferencias entre Entornos

| Aspecto | Desarrollo | Hosting |
|--------|-----------|---------|
| **URL API** | `http://nightpos.test/api` | `https://nightpos.ribersoft.com/api` |
| **Archivo Config** | `.env` | `.env.production` |
| **Comando** | `npm run dev` | `npm run build` |
| **Donde se ejecuta** | Tu máquina local | Servidor web (nginx, Apache, etc) |

---

## ⚠️ Importante

- **No edites `.env` para hosting**: Usa `.env.production` en su lugar
- **Tu `.env` local no se verá afectada** - seguirá conectando a `nightpos.test`
- **El `.env.production` solo se usa al compilar** - no afecta el desarrollo local

---

## 📝 Variables de Entorno

Si necesitas agregar más variables en el futuro:

1. Agrégalas a `.env` con el prefijo `VITE_`
2. Agrégalas también a `.env.production` si cambian en producción
3. En el código, accede con: `import.meta.env.VITE_TU_VARIABLE`

Ejemplo en el código:
```javascript
const API_URL = import.meta.env.VITE_API_BASE_URL
```
