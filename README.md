# Biblioteca API - Guia practica con Testing en PEST

## Proposito del proyecto

Este proyecto fue desarrollado como una **guia practica** para implementar y validar una API REST en Laravel.

El enfoque principal fue aplicar **testing automatizado con PEST** para verificar:

- Autenticacion con Sanctum.
- Control de acceso por roles y permisos.
- Reglas de autorizacion con Policies.
- Flujo CRUD de libros.
- Flujo de prestamos y devoluciones.

En particular, se probaron `BookPolicy` y `LoanPolicy` para garantizar que cada rol (`bibliotecario`, `docente`, `estudiante`) tenga solo los permisos permitidos.

## Tecnologias utilizadas

- Laravel 12
- PHP
- Laravel Sanctum
- Spatie Permission
- PEST + PHPUnit

## Roles y permisos

La asignacion de roles/permisos se centraliza en `database/seeders/PermissionSeeder.php`.

Roles creados:

- `bibliotecario`
- `docente`
- `estudiante`

Permisos clave por rol:

- `bibliotecario`: crear, actualizar, eliminar y consultar libros; ver préstamos.
- `docente`: consultar libros, crear préstamo, devolver préstamo, ver préstamos.
- `estudiante`: consultar libros, crear préstamo, devolver prestamo, ver préstamos.

## Endpoints de la API (v1)

Base URL: `http://localhost:8000/api/v1`

> Nota: todos los endpoints excepto `POST /login` requieren autenticacion con Bearer Token (`auth:sanctum`).

| Metodo | Endpoint | Descripcion | Auth |
|---|---|---|---|
| POST | `/login` | Iniciar sesion | No |
| POST | `/logout` | Cerrar sesion (revoca tokens) | Si |
| GET | `/profile` | Perfil del usuario autenticado | Si |
| GET | `/books` | Listar libros (con filtros) | Si |
| GET | `/books/{book}` | Ver detalle de libro | Si |
| POST | `/books` | Crear libro (bibliotecario) | Si |
| PATCH | `/books/{book}` | Actualizar libro (bibliotecario) | Si |
| DELETE | `/books/{book}` | Eliminar libro (bibliotecario) | Si |
| GET | `/loans` | Historial/listado de prestamos | Si |
| POST | `/loans` | Crear prestamo | Si |
| POST | `/loans/{loan}/return` | Registrar devolucion | Si |

## Estructura JSON de requests y responses

### 1) Login

**Request** `POST /api/v1/login`

```json
{
	"email": "docente@example.com",
	"password": "password"
}
```

**Response 200**

```json
{
	"access_token": "1|token_generado",
	"token_type": "Bearer",
	"user": {
		"id": 2,
		"name": "Pepe Docente",
		"email": "docente@example.com"
	}
}
```

**Response 422 (credenciales invalidas)**

```json
{
	"message": "Invalid credentials"
}
```

### 2) Perfil y logout

**Request** `GET /api/v1/profile`

**Response 200**

```json
{
	"user": {
		"id": 1,
		"name": "Lucas Bibliotecario",
		"email": "bibliotecario@example.com"
	}
}
```

**Request** `POST /api/v1/logout`

**Response 200**

```json
{
	"message": "Logged out successfully"
}
```

### 3) Libros

#### Crear libro

**Request** `POST /api/v1/books`

```json
{
	"title": "Clean Code",
	"description": "A Handbook of Agile Software Craftsmanship",
	"ISBN": "9780132350884",
	"total_copies": 5,
	"available_copies": 5,
	"is_available": true
}
```

**Response 200**

```json
{
	"id": 10,
	"title": "Clean Code",
	"description": "A Handbook of Agile Software Craftsmanship",
	"ISBN": "9780132350884",
	"total_copies": 5,
	"available_copies": 5,
	"is_available": "Disponible"
}
```

**Response 422 (validacion)**

```json
{
	"message": "The given data was invalid.",
	"errors": {
		"description": ["The description field is required."],
		"ISBN": ["The ISBN field is required."]
	}
}
```

#### Listar libros (con filtros)

**Request** `GET /api/v1/books?title=principito&isbn=12345678912&is_available=1`

Filtros soportados:

- `title` (coincidencia parcial)
- `isbn` (solo digitos)
- `is_available` (`0` o `1`)

**Response 200 (ejemplo simplificado)**

```json
[
	{
		"id": 1,
		"title": "El principito",
		"description": "Un libro muy famoso",
		"ISBN": "12345678912",
		"total_copies": 8,
		"available_copies": 5,
		"is_available": "Disponible"
	}
]
```

#### Ver detalle de libro

**Request** `GET /api/v1/books/{book}`

**Response 404 (sin coincidencias)**

```json
{
	"message": "There are no matches for the searched book"
}
```

#### Actualizar libro

**Request** `PATCH /api/v1/books/{book}`

```json
{
	"title": "Updated Title",
	"available_copies": 10,
	"total_copies": 10
}
```

**Response 200**: devuelve el libro actualizado en formato `BookResource`.

#### Eliminar libro

**Request** `DELETE /api/v1/books/{book}`

**Response 200**: devuelve el libro eliminado en formato `BookResource`.

### 4) Prestamos y devoluciones

#### Crear prestamo

**Request** `POST /api/v1/loans`

```json
{
	"requester_name": "Student Name",
	"book_id": 1
}
```

**Response 201**

```json
{
	"id": 4,
	"requester_name": "Student Name",
	"book_id": 1,
	"return_at": null,
	"created_at": "2026-03-09T12:00:00.000000Z",
	"updated_at": "2026-03-09T12:00:00.000000Z"
}
```

**Response 422 (libro no disponible)**

```json
{
	"message": "Book is not available"
}
```

#### Listar prestamos

**Request** `GET /api/v1/loans`

**Response 200 (ejemplo)**

```json
[
	{
		"id": 4,
		"requester_name": "Student Name",
		"book": {
			"id": 1,
			"title": "Clean Code",
			"description": "A Handbook of Agile Software Craftsmanship",
			"ISBN": "9780132350884",
			"total_copies": 5,
			"available_copies": 4,
			"is_available": "Disponible"
		},
		"is_active": true,
		"return_at": null,
		"created_at": "2026-03-09T12:00:00.000000Z",
		"updated_at": "2026-03-09T12:00:00.000000Z"
	}
]
```

#### Devolver prestamo

**Request** `POST /api/v1/loans/{loan}/return`

**Response 200**

```json
{
	"id": 4,
	"requester_name": "Student Name",
	"book": {
		"id": 1,
		"title": "Clean Code",
		"description": "A Handbook of Agile Software Craftsmanship",
		"ISBN": "9780132350884",
		"total_copies": 5,
		"available_copies": 5,
		"is_available": "Disponible"
	},
	"is_active": false,
	"return_at": "2026-03-09T13:00:00.000000Z",
	"created_at": "2026-03-09T12:00:00.000000Z",
	"updated_at": "2026-03-09T13:00:00.000000Z"
}
```

**Response 422 (ya devuelto)**

```json
{
	"message": "Loan already returned"
}
```

## Errores comunes HTTP en este proyecto

- `401 Unauthorized`: request sin token o token invalido.
- `403 Forbidden`: usuario autenticado sin permisos (policy/rol).
- `404 Not Found`: recurso no encontrado (por ejemplo libro inexistente).
- `422 Unprocessable Entity`: errores de validacion o reglas de negocio.

## Estructura del proyecto

```text
app/
	Http/
		Controllers/
			AuthController.php
			BookController.php
			LoanController.php
			ReturnLoanController.php
		Requests/
			AuthLoginRequest.php
			StoreBookRequest.php
			UpdateBookRequest.php
			StoreLoanRequest.php
		Resources/
			BookResource.php
			LoanResource.php
	Models/
		Book.php
		Loan.php
		User.php
	Policies/
		BookPolicy.php
		LoanPolicy.php
database/
	migrations/
	seeders/
		PermissionSeeder.php
		BookSeeder.php
		DatabaseSeeder.php
routes/
	api.php
tests/
	Feature/
		AccessTest.php
		AuthTest.php
		BookCreateTest.php
		BookDeleteTest.php
		BookGetDataTest.php
		BookLoggedOutTest.php
		BookUpdateTest.php
		LoanTest.php
		LogoutTest.php
	Unit/
		AuthUnitTest.php
```

## Ejecucion rapida

1. Instalar dependencias:

```bash
composer install
```

2. Configurar entorno y base de datos (`.env`).

3. Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

4. Levantar servidor local:

```bash
php artisan serve
```

5. Ejecutar pruebas:

```bash
php artisan test
```

