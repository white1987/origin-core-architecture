# Origin-Core

A minimal modular PHP skeleton based on a **"Universe" architecture**.

Each "universe" (user, env, trace, …) owns its own routes, controllers and
services, while sharing a tiny core (router, session, i18n).

This repository started as the internal core for the
[TGTRACING](https://tgtracing.com/) project and was later extracted as a
stand-alone open-source foundation.

---

## Features

- Very small, framework-agnostic core
- Simple router that discovers universes dynamically
- Each universe maps to its own folder:
  - `core/universe/user`
  - `core/universe/env`
  - `core/universe/trace` (future)
- Plain PHP pages under `pages/` (no templating engine required)
- Ready to be embedded into existing legacy projects

---

## Folder structure

```text
core/
  bootstrap.php   # loads session, helpers, i18n, router
  router.php      # central dispatcher
  helpers.php     # small helper functions
  session.php     # session bootstrap
  i18n.php        # stub for future localization
  universe/
    user/
      controller/UserController.php
      routes/routes.php
    env/
      controller/EnvController.php
      routes/routes.php

pages/
  home.php
  user/login.php
  env/admin.php

index.php
