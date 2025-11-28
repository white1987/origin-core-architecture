# Origin-Core Template

This folder contains a minimal, ready-to-use implementation of the  
**Origin-Core Architecture** — a deterministic multi-universe framework.

## Features

- Single entry (`index.php`)
- Unified router (`core/router.php`)
- Universe isolation (`/universes/<name>/`)
- Shared layer for helpers (`/shared/`)
- No business logic inside `core/`

## How to use

1. Copy all files from `/template` into your project root.
2. Start a PHP server:

   ```bash
   php -S localhost:8000 index.php
