# Railway Deployment Configuration

## Variables de Entorno en Railway

Configura estas variables en Railway Dashboard → Variables:

### Base de Datos (Railway PostgreSQL/MySQL)
```
DB_HOST=<railway-mysql-host>
DB_NAME=railway
DB_USER=<railway-user>
DB_PASS=<railway-password>
DB_PORT=<railway-port>
```

### Aplicación
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<tu-app>.up.railway.app
```

### SMTP (Gmail)
```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu-email@gmail.com
SMTP_PASS=tu-app-password
SMTP_FROM_EMAIL=tu-email@gmail.com
SMTP_FROM_NAME="Astillero La Roca"
```

### Sesión
```
SESSION_DURATION=1800
SESSION_COOKIE_NAME=ASTILLERO_SESSION
SESSION_COOKIE_SECURE=true
SESSION_COOKIE_HTTPONLY=true
```

### Rate Limiting
```
RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=900
RATE_LIMIT_BLOCK_DURATION=3600
```

### Logs
```
LOG_ENABLED=true
LOG_PATH=/tmp/logs
LOG_LEVEL=error
```

## Pasos para Deploy

### 1. Conectar Railway con GitHub
1. Ve a Railway.app
2. Conecta tu repositorio GitHub
3. Selecciona el repositorio "AI4Devs-finalproject"
4. Railway detectará automáticamente el proyecto PHP

### 2. Agregar Base de Datos MySQL
```bash
# En Railway Dashboard:
1. Click en "+ New" → Database → MySQL
2. Copia las credenciales (host, user, password, port)
3. Agrégalas a las Variables de Entorno
```

### 3. Configurar Variables de Entorno
```bash
# En Railway Dashboard → Tu Servicio → Variables:
1. Pega todas las variables de arriba
2. Reemplaza los placeholders con valores reales
3. Guarda los cambios
```

### 4. Desplegar
```bash
# Railway desplegará automáticamente cuando hagas push:
git add .
git commit -m "Configuración para Railway"
git push origin develop
```

## Estructura de Archivos Importante

```
/
├── Procfile              # Comando de inicio para Railway
├── package.json          # Metadata y scripts
├── composer.json         # Dependencias PHP
├── public/              # Document root público
│   ├── index.html       # Entrada principal
│   └── ...
└── src/                 # Código PHP
```

## Troubleshooting

### Error: Puerto no encontrado
```bash
# Railway proporciona $PORT automáticamente
# Ya está configurado en Procfile
```

### Error: Base de datos no conecta
```bash
# Verifica que las variables DB_* estén correctas
# Prueba la conexión desde Railway Logs
```

### Error: Archivos no se cargan
```bash
# Asegúrate de que public/ sea el document root
# Railway debe servir desde public/index.html
```

### Error: Sesiones no persisten
```bash
# Verifica SESSION_COOKIE_SECURE=true (requiere HTTPS)
# Railway proporciona HTTPS automáticamente
```

## Comandos Útiles

### Ver logs en tiempo real
```bash
# En Railway Dashboard → Deployments → View Logs
```

### Redeploy manual
```bash
# Railway Dashboard → Deployments → Redeploy
```

### Acceder a la base de datos
```bash
# Railway Dashboard → MySQL → Connect
# Usa las credenciales proporcionadas
```

## URLs Importantes

- **Dashboard**: https://railway.app/dashboard
- **Tu App**: https://<tu-proyecto>.up.railway.app
- **Docs**: https://docs.railway.app

## Checklist de Deploy

- [ ] Variables de entorno configuradas
- [ ] Base de datos MySQL creada en Railway
- [ ] Schema de BD importado (schema.sql)
- [ ] Datos iniciales importados (data.sql)
- [ ] APP_URL apunta a la URL de Railway
- [ ] SESSION_COOKIE_SECURE=true
- [ ] APP_DEBUG=false
- [ ] Credenciales SMTP configuradas
- [ ] Push a GitHub realizado
- [ ] Deploy exitoso en Railway
- [ ] Prueba de login funcionando
- [ ] Emails enviándose correctamente

## Próximos Pasos

1. Configura las variables de entorno en Railway
2. Importa la base de datos
3. Haz push del código
4. Prueba la aplicación en la URL de Railway
