# DonaTu — Plataforma de Gestión de Donaciones y Rendición de Cuentas

Plataforma web para la gestión transparente de donaciones, seguimiento de egresos y rendición de cuentas a donantes.

**Alumno:** Elder Estuardo García Pacheco | **Carné:** 0900 24 9106  
**Universidad Mariano Gálvez** | Ingeniería de Software

---

## Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap (via CDN) |
| Backend | PHP 8.1 |
| Base de datos | MySQL 8.0 |
| Control de versiones | Git + GitHub |
| CI/CD | GitHub Actions |

## Estructura del Proyecto

```
donatu/
├── index.php               # Página pública con campañas
├── login.php               # Autenticación
├── logout.php
├── dashboard.php           # Panel administrativo
├── config/
│   ├── app.php             # Configuración global + sesiones
│   └── database.php        # Conexión MySQL
├── assets/
│   ├── css/style.css       # Estilos principales (diseño Figma)
│   └── js/main.js          # JavaScript general
├── includes/
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
├── modules/
│   ├── campaigns/          # Gestión de campañas
│   ├── donations/          # Registro de donaciones
│   ├── expenses/           # Registro de egresos
│   └── reports/            # Reportes por campaña
├── database/
│   └── donatu.sql          # Schema completo MySQL
└── .github/workflows/
    └── ci.yml              # Pipeline CI/CD
```

## Instalación

### Prerequisitos
- PHP >= 8.1
- MySQL >= 8.0
- Servidor web (XAMPP, WAMP, Laragon)

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/ElderGP18/sistema-donaciones.git
cd sistema-donaciones

# 2. Importar la base de datos
mysql -u root -p < database/donatu.sql

# 3. Configurar la conexión en config/database.php
#    Cambiar DB_USER y DB_PASS si es necesario

# 4. Apuntar el virtual host a la carpeta raíz
#    O acceder desde: http://localhost/sistema-donaciones/
```

### Credenciales iniciales
```
Correo:     admin@donatu.com
Contraseña: password
```
> Cambiar en producción con: `password_hash('TuPassword', PASSWORD_BCRYPT)`

## Sprints

| Sprint | Contenido | Estado |
|---|---|---|
| Sprint 1 | Login, Dashboard, Campañas, Donaciones, Egresos, Reportes | ✅ Completado |
| Sprint 2 | Módulo Donantes completo, Edición Campañas, Gestión Usuarios | ✅ Completado |
| Sprint 3 | Pruebas, CI/CD completo, despliegue en producción | 🔄 Pendiente |

## Módulos Sprint 1

- **Autenticación** — Login con validación y sesiones PHP
- **Dashboard** — KPIs en tiempo real (campañas, recaudado, egresos, saldo)
- **Campañas** — Crear, listar y ver detalle con progreso visual
- **Donaciones** — Registrar con donante nuevo o existente
- **Egresos** — Registrar gastos por campaña
- **Reportes** — Resumen financiero con gráfico de barras por mes

## Licencia

MIT
