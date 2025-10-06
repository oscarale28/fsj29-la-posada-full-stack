# 🏨 Sistema de Gestión de Alojamientos 'La Posada' - Frontend App

Frontend del sistema de gestión de hospedaje "La Posada", desarrollado con Next.js 15, TypeScript y Tailwind CSS. Este proyecto proporciona una interfaz moderna y responsiva para la gestión de alojamientos, usuarios y reservas.

## 🌐 Demo en Vivo

**URL de Producción**: [https://la-posada.fqstudio.dev/](https://la-posada.fqstudio.dev/)

La aplicación está desplegada en **Dokploy** y disponible para pruebas.

## 🚀 Características

- **Autenticación de usuarios**: Sistema de login y registro con JWT
- **Gestión de alojamientos**: Visualización y administración de hospedajes
- **Panel de administración**: Control total para administradores
- **Dashboard de usuario**: Panel personalizado para clientes
- **Diseño responsivo**: Interfaz adaptable a diferentes dispositivos
- **Server Actions**: Integración con el backend usando Next.js Server Actions

## 🔑 Credenciales de Acceso

### Administrador

- **Email**: `admin@admin.com`
- **Password**: `Admin@123`

## 📁 Estructura del Proyecto

```
src/
├── app/                      # App Router de Next.js
│   ├── admin/               # Páginas de administración
│   ├── dashboard/           # Dashboard de usuario
│   ├── login/               # Página de inicio de sesión
│   └── register/            # Página de registro
├── features/                # Módulos por funcionalidad
│   ├── accommodation-management/
│   ├── administration/
│   ├── authentication/
│   └── user-management/
└── shared/                  # Recursos compartidos
    ├── components/
    ├── config/
    ├── types/
    └── utils/
```

## 🛡️ Stack Tecnológico

- **Framework**: Next.js 15 (App Router)
- **Lenguaje**: TypeScript
- **Estilos**: Tailwind CSS
- **UI Components**: shadcn/ui
- **Validación**: Zod
- **Estado**: React Hooks + Server Actions
- **Autenticación**: JWT (cookies)

## 📝 Notas de Desarrollo

- El proyecto utiliza Server Actions de Next.js para las interacciones con el backend construido con PHP y MySQL.
- La autenticación se maneja mediante tokens JWT almacenados en cookies
- Se implementa validación en cliente y servidor usando Zod
- Los componentes siguen los principios de Clean Architecture
