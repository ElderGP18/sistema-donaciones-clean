# Sistema de Gestión de Donaciones y Rendición de Cuentas

Plataforma web para la gestión transparente de donaciones, seguimiento de beneficiarios y rendición de cuentas a donantes.

## Descripción

Sistema que permite a organizaciones sin fines de lucro registrar donaciones, gestionar proyectos financiados, reportar el uso de los fondos y brindar transparencia total a los donantes mediante un portal de consulta.

## Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Frontend | React + Vite + TailwindCSS |
| Backend / API | Node.js + Express |
| Base de datos | PostgreSQL |
| Autenticación | JWT + bcrypt |
| Infraestructura | Railway / Render |
| CI/CD | GitHub Actions |

## Estructura del Proyecto

```
sistema-donaciones/
├── backend/          # API REST con Node.js + Express
│   ├── src/
│   │   ├── controllers/
│   │   ├── models/
│   │   ├── routes/
│   │   ├── middleware/
│   │   └── config/
│   ├── tests/
│   └── package.json
├── frontend/         # React + Vite + TailwindCSS
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── services/
│   │   └── context/
│   └── package.json
├── docs/             # Documentación del proyecto
│   ├── fase1/
│   └── fase2/
├── .github/
│   └── workflows/    # Pipelines CI/CD
└── docker-compose.yml
```

## Instalación y Ejecución

### Prerequisitos
- Node.js >= 18
- PostgreSQL >= 14
- npm >= 9

### Backend

```bash
cd backend
npm install
cp .env.example .env   # Configurar variables de entorno
npm run dev
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

### Variables de entorno (backend)

```env
PORT=3000
DATABASE_URL=postgresql://usuario:password@localhost:5432/donaciones_db
JWT_SECRET=tu_secreto_jwt
NODE_ENV=development
```

## Funcionalidades Principales

- Registro y autenticación de usuarios (donantes, administradores)
- Gestión de campañas de donación
- Registro y seguimiento de donaciones
- Panel de transparencia con reportes públicos
- Notificaciones automáticas a donantes
- Dashboard administrativo con métricas
- Exportación de reportes en PDF

## Metodología

El proyecto sigue la metodología **Scrum** con sprints de 2 semanas, gestionado en GitHub Projects.

## Licencia

MIT
