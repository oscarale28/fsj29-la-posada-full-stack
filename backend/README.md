# 🏨 Sistema de Gestión de Alojamientos 'La Posada' - API Backend

Una API REST completa para la gestión de alojamientos y cuentas de usuario, construida con PHP y con documentación interactiva Swagger UI.

## 🏛️ Arquitectura del Sistema

### Implementación de Screaming Architecture

Este proyecto implementa **Screaming Architecture**, donde la estructura del código refleja claramente el dominio del negocio. La organización por módulos de dominio hace que el propósito de la aplicación sea evidente desde la estructura de carpetas:

```
src/
├── AccommodationManagement/    # 🏨 Gestión de Alojamientos (Dominio Principal)
│   ├── Entities/              # Entidades del dominio
│   ├── Controllers/           # Controladores HTTP
│   ├── Services/              # Lógica de negocio
│   └── Repositories/          # Acceso a datos
├── UserManagement/            # 👥 Gestión de Usuarios
├── Authentication/            # 🔐 Autenticación y Autorización
└── Shared/                    # 🔧 Componentes compartidos
    ├── Http/                  # Router, Middleware
    ├── Security/              # JWT, Hashing
    └── Database/              # Conexión y utilidades
```

### Funcionalidades Principales de la API

#### 🏨 Gestión de Alojamientos
- **Búsqueda y filtrado** de alojamientos por ubicación, precio y amenidades
- **Creación de alojamientos** (solo administradores)
- **Visualización pública** de catálogo de alojamientos
- **Gestión de amenidades** dinámicas por alojamiento

#### 👥 Gestión de Usuarios
- **Registro y autenticación** con JWT
- **Roles de usuario** (usuario/administrador)
- **Gestión de alojamientos favoritos** por usuario
- **Perfiles de usuario** personalizables

#### 🔐 Seguridad y Autenticación
- **JWT tokens** con expiración configurable
- **Middleware de autenticación** por roles
- **Validación robusta** de datos de entrada
- **Hashing seguro** de contraseñas

## 🌐 Despliegue en Producción con Dokploy

### URL de Producción
**🔗 https://api.la-posada.fqstudio.dev/docs**

### Configuración de Dokploy

El proyecto está configurado para despliegue automático en Dokploy con:

1. **Dockerfile optimizado** para producción
2. **Variables de entorno** seguras
3. **SSL/TLS** automático

### Proceso de Despliegue

1. **Construcción de imagen de producción**
   ```bash
   docker build -f Dockerfile -t la-posada-api:latest .
   ```

2. **Configuración en Dokploy**
   - Repositorio conectado para CI/CD automático
   - Variables de entorno configuradas de forma segura
   - Dominio personalizado con SSL automático
   - Monitoreo y logs centralizados

3. **Health Check configurado**
   ```dockerfile
   HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
       CMD curl -f http://localhost/docs || exit 1
   ```

## 📚 Documentación de la API

### Documentación Interactiva
Visita https://api.la-posada.fqstudio.dev/docs para la documentación completa e interactiva con Swagger UI.

### Autenticación
La mayoría de endpoints requieren autenticación JWT. Incluye el token en el header Authorization:
```
Authorization: Bearer <tu-jwt-token>
```

## 🔧 Configuración

La aplicación utiliza la configuración de base de datos existente del archivo `.env`. No se requiere configuración adicional de base de datos.

### Variables de Entorno
- `DB_HOST` - Host de la base de datos
- `DB_PORT` - Puerto de la base de datos (por defecto: 3306)
- `DB_NAME` - Nombre de la base de datos
- `DB_USERNAME` - Usuario de la base de datos
- `DB_PASSWORD` - Contraseña de la base de datos
- `JWT_SECRET` - Clave secreta para tokens JWT
- `JWT_EXPIRATION` - Tiempo de expiración del token en segundos
- `APP_ENV` - Entorno de la aplicación (development/production)
- `APP_DEBUG` - Habilitar modo debug (true/false)

## 📝 Endpoints de la API

### Autenticación
- `POST /api/auth/register` - Registrar nuevo usuario
- `POST /api/auth/login` - Inicio de sesión de usuario
- `POST /api/auth/refresh` - Refrescar token JWT
- `POST /api/auth/validate` - Validar token JWT

### Endpoints Públicos
- `GET /api/accommodations` - Listar alojamientos (con filtros)
- `GET /api/accommodations/{id}` - Obtener alojamiento específico

### Endpoints de Usuario (Autenticación Requerida)
- `GET /api/users/accommodations` - Obtener alojamientos del usuario
- `POST /api/users/accommodations` - Agregar alojamiento a cuenta de usuario
- `DELETE /api/users/accommodations/{id}` - Remover alojamiento de cuenta de usuario

### Endpoints de Administrador (Rol Admin Requerido)
- `POST /api/admin/accommodations` - Crear nuevo alojamiento
