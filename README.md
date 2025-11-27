# Origin-Core Architecture

A minimal, modular PHP skeleton built around the **Universe Architecture**.  
This repo is the clean core used behind the TGTRACING project, extracted and simplified for public use and learning.

## Features

- 🧩 **Universe Architecture**
  - `user`, `trace`, `env` universes separated by folders
  - Each universe has its own `routes`, `controller`, `service`, `views`
- 🧠 **Central router**
  - All HTTP requests go through `core/router.php`
  - Universes only register their routes and handlers
- 🌍 **Simple i18n hook**
  - Global i18n loader in `core/i18n.php`
  - Ready to mount real translation files later
- 🧪 **Tiny demo pages**
  - `/?p=home` – core home page
  - `/?p=user_hello` – demo from User universe
  - `/?p=user_login` – demo login page
  - `/?p=env_hello` – demo from Env universe
  - `/?p=env_admin` – demo env admin entry

## Directory structure

```text
core/
  bootstrap.php      # Entry bootstrap
  router.php         # Central router
  helpers.php        # Common helpers
  i18n.php           # i18n loader (global)
  session.php        # Session wrapper
  universe/
    user/
      routes/        # user/routes/routes.php
      controller/
      service/
      views/
    trace/
      ...
    env/
      ...
pages/
  home.php           # Home demo
  user/login.php     # User login demo
  env/admin.php      # Env admin demo
docs/
  whitepaper.md      # (optional) design notes
i18n/
  en.json
  zh-CN.json
index.php            # Front controller
