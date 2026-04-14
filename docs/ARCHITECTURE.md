# Architecture

## Application Layers

- `app/Http` : Controllers, middleware, requests.
- `app/Models` : Eloquent models.
- `app/Services` : Business/service layer.
- `app/Repositories` : Optional data-access abstractions.
- `app/Modules` : Feature modules grouped by domain.

## Suggested Module Layout

Inside each module folder:

- `Actions/`
- `DTOs/`
- `Services/`
- `Repositories/`
- `Http/Controllers/`
- `Http/Requests/`

## Existing Module Seeds

- `app/Modules/Accounting`
- `app/Modules/Reporting`
- `app/Modules/Shared`
