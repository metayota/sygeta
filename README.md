# Sygeta

Sygeta is a modular, web-based business suite combining customer relationship management (CRM) and enterprise resource planning (ERP). It helps teams manage customer relationships, sales, communication, documents, and everyday business processes from one system.

- [Product website](https://www.sygeta.com/)
- [Developer portal](https://developer.sygeta.com/)
- [Product documentation](https://sygeta.com/en/documentation/)

## Features

The application includes modules for contacts and partners, sales pipelines and activities, tasks and calendars, meetings and webinars, email and newsletters, contracts, sales documents and invoices, and document management. Resource-based components support custom forms, data editors, translations, and workflows. REST endpoints and OpenAPI definitions support integration with other systems.

Sygeta supports self-hosted deployments for organizations and resellers that want control over their infrastructure and data. See the product website for current hosted offerings and support options.

## Technology

- PHP application using the Metayota resource framework and PDO database access.
- JavaScript components and PHP-rendered HTML templates.
- Apache rewrite rules for application routes, resources, and API endpoints.
- Composer dependencies for email, JWT, PDF generation, and electronic invoices.
- npm dependencies for TinyMCE and signature capture; Puppeteer for HTML-to-PDF rendering.

## Repository structure

| Path | Purpose |
| --- | --- |
| `scripts/` | Application modules, framework code, templates, and browser components |
| `scripts/api/` | REST endpoints and OpenAPI specifications |
| `conf/` | Database, application, and cache configuration |
| `node-scripts/` | Puppeteer HTML-to-PDF helper and its dependencies |
| `images/`, `fonts/` | Static assets |
| `uploads/` | Instance-specific uploaded files |
| `cache/` | Runtime cache |
| `.htaccess` | Apache routing and HTTP configuration |

## Deployment notes

This repository contains an existing application deployment rather than a complete one-command installer. Provision a separate development environment before changing a running instance.

1. Provide Apache with PHP support, `mod_rewrite`, and permission to apply the supplied `.htaccess` rules. The current server uses PHP 8.3; a minimum supported PHP version is not declared in `composer.json`.
2. Install PHP dependencies with `composer install` and browser dependencies with `npm ci` from the repository root.
3. If HTML-to-PDF rendering is needed, run `npm ci` inside `node-scripts/` and provide the browser and system dependencies required by Puppeteer.
4. Configure your own database connection in `conf/db-config.php` and review `conf/main-config.php` and `conf/cache-config.php` for the target environment. The committed configuration intentionally leaves credentials and secrets blank; supply instance-specific values locally and keep them out of commits. The PDO layer expects `DB_HOST`, `DB_NAME`, `DB_USERNAME`, and `DB_PASSWORD` globals.
5. Provision a compatible database schema and initial data from a trusted, sanitized source. No standalone schema migration or bootstrap procedure is documented here.
6. Point the Apache document root at the repository root, configure HTTPS, and give the PHP service account appropriate access to runtime directories such as `cache/` and `uploads/`.
7. Configure only the integrations you intend to use, including mail, authentication, payments, and external services.

Treat configuration, database exports, and uploaded files as instance data: review and sanitize them before sharing a checkout or creating a new deployment. Never reuse production credentials or customer data in development.

## Development

Application resources are grouped under `scripts/<resource>/`. Components commonly combine PHP, JavaScript, HTML templates, and styles; `.htaccess` maps `/call/`, `/resource/`, and `/api/v1/` requests to their implementations.

Use PHP linting for changed PHP files and JavaScript syntax checks where appropriate. This repository does not define a root-level automated test command. Validate affected workflows in an isolated configured instance before deploying.

## License

The project is licensed under the [MIT License](LICENSE). Bundled third-party libraries, fonts, and other assets retain their respective licenses.
