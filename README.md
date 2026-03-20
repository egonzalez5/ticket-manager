<p align="center">
<a href="#">
<img src="https://cdn-icons-png.flaticon.com/512/906/906334.png" width="120" alt="Ticket Manager Logo">
</a>
</p>

<h1 align="center">Ticket Manager</h1>

<p align="center">
Sistema de gestión de tickets desarrollado con Laravel y Vue para administrar incidencias, solicitudes o soporte dentro de una organización.
</p>

<p align="center">
<img src="https://img.shields.io/badge/Laravel-Framework-red">
<img src="https://img.shields.io/badge/Vue-3-green">
<img src="https://img.shields.io/badge/Inertia.js-Adapter-purple">
<img src="https://img.shields.io/badge/Docker-Container-blue">
<img src="https://img.shields.io/badge/PostgreSQL-Database-blue">
</p>

---

## Acerca del proyecto

Ticket Manager es una aplicación web diseñada para gestionar tickets de soporte, incidencias o solicitudes dentro de una organización.

El proyecto está orientado a demostrar el desarrollo de una aplicación moderna utilizando **Laravel, Vue 3, Inertia.js y Docker**, siguiendo buenas prácticas de arquitectura y desarrollo Full Stack.

El sistema permite gestionar el ciclo de vida completo de un ticket, desde su creación hasta su resolución.

---

## Funcionalidades

El sistema incluye funcionalidades como:

- Autenticación de usuarios
- Creación de tickets
- Asignación de tickets a usuarios
- Estados de tickets (abierto, en progreso, resuelto, cerrado)
- Comentarios en tickets
- Adjuntos en tickets
- Sistema de notificaciones
- Procesamiento de colas con Redis
- Arquitectura preparada para escalar

---

## Stack tecnológico

### Backend

- PHP
- Laravel
- PostgreSQL

### Frontend

- Vue 3
- Inertia.js
- Tailwind CSS
- Vite

### Infraestructura

- Docker
- Docker Compose
- Redis

---

## Instalación

Clonar el repositorio:

git clone https://github.com/egonzalez5/ticket-manager.git  
cd ticket-manager

Levantar contenedores Docker:

docker compose up -d

Instalar dependencias de Laravel:

composer install

Instalar dependencias del frontend:

npm install

Copiar archivo de entorno:

cp .env.example .env

Generar clave de aplicación:

php artisan key:generate

Ejecutar migraciones:

php artisan migrate

Ejecutar servidor de desarrollo frontend:

npm run dev

---

## Comandos útiles

Ejecutar worker de colas:

php artisan queue:work

Ejecutar pruebas:

php artisan test

---

## Capturas

Las capturas del sistema serán agregadas a medida que avance el desarrollo del proyecto.

---

## Objetivo del proyecto

Este proyecto fue desarrollado como parte de un portafolio profesional para demostrar habilidades en:

- Laravel
- Vue 3
- Inertia.js
- Arquitectura de aplicaciones web
- Docker
- Desarrollo Full Stack

---

## Autor

Eduardo González

Desarrollador Full Stack  
Laravel | Vue | PostgreSQL / MySQL | Docker | Bootstrap / Tailwind
