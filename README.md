# Backend Architecture

* **[Project description](https://github.com/alexander-kovalev2110/full-stack-web-proj_REST-API-BANK/blob/master/PHP-test.pdf)**
* **[Swagger (OpenAPI)](https://alexander-kovalev2110.github.io/full-stack-web-proj_REST-API-BANK/Swagger-OpenAPI/dist/index.html)**

This project is built with **PHP + Symfony + Doctrine ORM** and follows the principles of **Hexagonal Architecture (Ports and Adapters)** and **Domain-Driven Design (DDD)**. It features a strict separation between HTTP presentation, application, and domain logic using **CQRS** (Command Query Responsibility Segregation).

The architecture ensures predictable data flow:

**Command (Write) Flow:**
```
FE → Kernel → Security → Argument Resolver → Controller → CommandBus → CommandHandler → Domain Entities → Repository Port → Doctrine Adapter → JsonResponse → FE
```

**Query (Read) Flow:**
```
FE → Kernel → Security → Argument Resolver → Controller → QueryBus → QueryHandler → Query Port → Doctrine Adapter → View DTO → JsonResponse → FE
```

---

## Architectural Principles

*   **Hexagonal Architecture (Ports & Adapters)** — The core domain and application layers are isolated from external technical dependencies. They communicate with the outside world using abstract interfaces ("Ports"). Frameworks, databases, security, and external APIs are treated as external "Adapters" located strictly in the Infrastructure layer.
*   **Domain-Driven Design (DDD)** — The codebase is structured around business contexts and subdomains (`Customer` and `Transaction`). Domain aggregates and entities protect business invariants, while repository interfaces abstract data persistence.
*   **Separation of Concerns** — Each layer has a single responsibility.
*   **Thin Controllers / Fat Domain & Handlers** — Controllers only delegate to command/query buses; business rules are enforced inside domain models, and orchestration lives in handler classes.
*   **DTO-driven boundaries** — Request and response models are explicit and decoupled from domain objects.
*   **Domain-first approach** — Application handlers operate on domain entities and interfaces, abstracting database infrastructure.
*   **Explicit CQRS data flow** — Clear separation of write actions (Commands) and read actions (Queries) with no side effects.

---

## Request Lifecycle Overview

### 1. Kernel Layer (HTTP Entry Point)
*   **Purpose:** Handles the incoming HTTP request and bootstraps the Symfony application.
*   **Flow:** `Incoming HTTP Request → Kernel`

### 2. Security Layer
*   **Purpose:** Handles authentication and authorization.
*   **Responsibilities:**
    *   JWT authentication (resolving endpoints via tokens).
    *   Checking user permissions.
    *   Resolving the authenticated `Customer` entity.
    *   Flow: `FE → Kernel → Security`

### 3. Argument Resolver Layer
*   **Purpose:** Automatically deserializes the incoming HTTP request body and query parameters into structured DTO models.
*   **Responsibilities:**
    *   Parse raw HTTP input.
    *   Hydrate and validate the Request DTO.
    *   Throw validation errors immediately on invalid payload structure (returning `400 Bad Request` with structured validation messages).
*   **Structure:**
    *   [RequestPayloadResolver.php](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Api/ArgumentResolver/RequestPayloadResolver.php)
    *   [FilterTransactionRequestResolver.php](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Api/ArgumentResolver/FilterTransactionRequestResolver.php)

### 4. DTO Layer (Request Models)
*   **Purpose:** Strongly typed input definitions protecting application boundaries.
*   **Structure:**
    *   [src/Application/Customer/DTO/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Application/Customer/DTO) (e.g. `RegisterRequest`, `LoginRequest`)
    *   [src/Application/Transaction/DTO/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Application/Transaction/DTO) (e.g. `AmountTransactionRequest`, `FilterTransactionRequest`)
*   **Characteristics:**
    *   Strict typing and readonly attributes.
    *   Symfony validation constraints (`#[Assert\NotBlank]`, `#[Assert\Positive]`, etc.).

### 5. Controller Layer (HTTP Orchestration)
*   **Purpose:** Coordinates request handling without containing business or persistence logic.
*   **Characteristics:**
    *   No validation logic.
    *   No database interaction.
    *   Dispatches commands or queries to the bus.
*   **Structure:**
    *   [src/Api/Controller/Customer/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Api/Controller/Customer) (e.g. `RegisterController`, `LoginController`)
    *   [src/Api/Controller/Transaction/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Api/Controller/Transaction) (e.g. `CreateTransactionController`, `GetTransactionListController`)

### 6. Messenger Bus
*   **Purpose:** Decouples controllers from handlers.
*   **Structure:**
    *   [src/Infrastructure/Bus/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Infrastructure/Bus) (e.g. `CommandBus` and `QueryBus` wrapping Symfony Messenger)

### 7. Handler Layer (Application Logic)
*   **Purpose:** Implements application-level orchestration and transactional boundaries.
*   **Responsibilities:**
    *   Retrieve domain entities from repository ports.
    *   Trigger state modifications on domain aggregates.
    *   Save changed entities back to persistent storage.
*   **Structure:**
    *   [src/Application/Customer/Command/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Application/Customer/Command) (e.g. `RegisterCustomerHandler`)
    *   [src/Application/Transaction/Command/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Application/Transaction/Command) (e.g. `CreateTransactionHandler`)
    *   [src/Application/Transaction/Query/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Application/Transaction/Query) (e.g. `GetTransactionListHandler`)

### 8. Repository Layer (Data Access Interfaces)
*   **Purpose:** Encapsulate database retrieval and persistence.
*   **Structure:**
    *   Domain interfaces (Ports): [src/Domain/Customer/CustomerRepositoryInterface.php](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Domain/Customer/CustomerRepositoryInterface.php) and [src/Domain/Transaction/TransactionRepositoryInterface.php](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Domain/Transaction/TransactionRepositoryInterface.php).
    *   Infrastructure implementations (Adapters): [src/Infrastructure/Repository/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Infrastructure/Repository) and [src/Infrastructure/Query/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Infrastructure/Query).

### 9. Domain Layer (Entities & Exceptions)
*   **Purpose:** Represent core business models, rules, and validation invariants.
*   **Structure:**
    *   [src/Domain/Customer/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Domain/Customer)
    *   [src/Domain/Transaction/](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Domain/Transaction)
*   **Characteristics:**
    *   Free from framework/HTTP concerns.
    *   Contains database mapping annotations (Doctrine).

### 10. Response Layer (Output Models / View DTOs)
*   **Purpose:** Defines structured, immutable payloads returned by the API.
*   **Examples:**
    *   [AuthView.php](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Application/Customer/DTO/AuthView.php)
    *   [TransactionView.php](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Application/Transaction/DTO/TransactionView.php)
*   **Benefits:**
    *   Prevents exposing internal ORM entity relations directly to JSON.
    *   Decouples API contract representation from the database schema.

---

## Exception Handling & Validation

1.  **Request Payload Validation:**
    Occurs at the resolver stage via [RequestPayloadResolver](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Api/ArgumentResolver/RequestPayloadResolver.php). Violations return a `400 Bad Request` JSON payload containing errors.
2.  **Domain Exception Handling:**
    Domain-level exceptions (e.g. `CustomerNotFoundException`, `TransactionNotFoundException`) extend [DomainException](file:///d:/KA/tests/github/REST-API-BANK_backend/src/Domain/Exception/DomainException.php) which defines the target HTTP status. They are globally intercepted by [ApiExceptionListener](file:///d:/KA/tests/github/REST-API-BANK_backend/src/EventListener/ApiExceptionListener.php) and converted to consistent JSON responses.

---

## Folder Structure Overview

```
src/
  Api/                      # Primary (Driving) Adapters
    Controller/             # HTTP Entry Points
    ArgumentResolver/       # Custom parameter binding & validation resolvers
  Domain/                   # Core business logic & entities
    Customer/               # Customer domain entity, interfaces, exceptions
    Transaction/            # Transaction domain entity, interfaces, exceptions
    Exception/              # Base domain exceptions
  Application/              # Application orchestration & Use cases
    Customer/               # Customer Commands, Handlers, DTOs, Ports
    Transaction/            # Transaction Commands, Queries, Handlers, DTOs, Ports
  Infrastructure/           # Secondary (Driven) Adapters
    Bus/                    # CQRS Bus Messenger implementations
    Query/                  # Optimized read projections (SQL/DQL)
    Repository/             # Doctrine database mapping repositories
    Security/               # Password hashing & Token generation adapters
  EventListener/            # Global event listeners (Exception listener)
```

---

## Core Philosophy

Backend is responsible for:
*   Enforcing business rules and protecting domain integrity.
*   Validating all input parameter structures.
*   Providing clean port-adapter boundaries for ease of testing.
*   Returning structured contracts (View DTOs) instead of raw entities.
