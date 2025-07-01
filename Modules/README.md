# Module Template

When creating a new module in this Laravel boilerplate, follow this structure:

## Module Structure
```
Modules/YourModule/
├── Actions/             # Business logic actions
│   ├── CreateEntityAction.php
│   ├── UpdateEntityAction.php
│   └── DeleteEntityAction.php
├── Config/
│   └── config.php
├── Console/
├── Database/
│   ├── Migrations/
│   ├── Seeders/
│   └── factories/
├── Entities/            # Models
├── Http/
│   ├── Controllers/     # Thin controllers
│   ├── Middleware/
│   └── Requests/
├── Providers/
│   ├── YourModuleServiceProvider.php
│   └── RouteServiceProvider.php
├── Resources/
│   ├── assets/
│   ├── lang/
│   └── views/
├── Routes/
│   ├── api.php
│   └── web.php
├── Tests/
│   ├── Feature/
│   └── Unit/
│       └── Actions/     # Action tests
├── composer.json
├── module.json
├── package.json
└── README.md           # Module-specific instructions
```

## Module-Specific AI Instructions

Create a `README.md` in each module with:

1. **Module Purpose**: Clear description of what this module does
2. **Dependencies**: List of other modules this depends on
3. **API Endpoints**: If applicable, list all routes
4. **Business Rules**: Domain-specific logic and constraints
5. **Testing Guidelines**: How to test this module

## Example Module README Template

```markdown
# [ModuleName] Module

## Purpose
Brief description of what this module handles.

## Dependencies
- Core module
- Any other module dependencies

## Key Features
- Feature 1
- Feature 2

## Actions
This module follows the action-driven pattern. Key actions include:
- `CreateEntityAction` - Creates new entity with business validation
- `UpdateEntityAction` - Updates entity following business rules
- `DeleteEntityAction` - Soft deletes with cascade handling

## API Endpoints (if applicable)
- `GET /api/module-name/resource` - List resources
- `POST /api/module-name/resource` - Create resource (uses CreateEntityAction)
- etc.

## Models
- `ModelName` - Description of what it represents

## Business Rules
1. Specific rule 1 (implemented in XyzAction)
2. Specific rule 2 (validated in AbcAction)

## Testing
```bash
# Test everything
php artisan test Modules/ModuleName

# Test only actions
php artisan test Modules/ModuleName/Tests/Unit/Actions
```

## Module-Specific Conventions
- All business logic must be in Action classes
- Controllers should only handle HTTP concerns
- Complex queries should be in repository classes
```

## Creating a New Module

```bash
php artisan module:make YourModuleName
```

Then create a README.md in the module directory with the above template filled out.
