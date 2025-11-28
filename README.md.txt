🌌 Origin-Core Architecture
A Universe-Oriented Micro-Framework for Infinite Scalability

Built solo on a 2016 laptop, driven purely by curiosity + AI collaboration.
Origin-Core introduces a new architectural paradigm: Universe-Oriented Architecture (UOA) —
a system where entire worlds (universes) can be created or removed without ever touching the core.

🚀 Why Origin-Core?

Most frameworks (Laravel, Symfony, MVC, HMVC) assume the world is one big monolith.
Origin-Core breaks this model and introduces multiple universes, each a self-contained world.

✔ Multi-Universe Architecture

Each universe is isolated:

/universes/home/
/universes/api/
/universes/admin/
/universes/blog/

✔ Core-Free Expansion

Add or delete a Universe → zero impact to /core.

✔ Zero-Config Routing

Two parameters define everything:?u=universe&p=page
Which maps to:/universes/{u}/pages/{p}.php

No controllers.
No route tables.
No YAML.
No annotations.
No magic.
100% predictable.

✔ Infinite Page Depth
?u=home&p=auth/login → /universes/home/pages/auth/login.php

✔ Extreme Modularity

Add a universe instantly

Delete a universe instantly

No side effects

No shared state pollution

🗂 Directory Structure

Origin-Core/
  template/
    index.php
    core/
      router.php
      config.php
      rules.php
    shared/
      helpers/
        html.php
      ui/
        layout.php
    universes/
      home/
        pages/
          index.php
          auth/
            login.php
      api/
        pages/
          status.php
      admin/
        pages/
          index.php
      blog/
        pages/
          index.php

🛰 Universe Routing Model

?u=home&p=index         → universes/home/pages/index.php
?u=admin&p=index        → 403 Forbidden (core rules)
?u=admin&p=index&key=demo → allowed
?u=api&p=status         → JSON response
?u=blog&p=index         → Blog universe

🔥 Key Concepts
1. Universe = Self-Contained World

Each universe includes its own:

pages

access rules

structure

2. Core = Immutable Layer

core/router.php never changes.

3. Shared Layer

Reusable helpers & UI pieces:

shared/ui/
shared/helpers/

Universes may optionally use them.

🔌 API Universe Example
Visit: ?u=api&p=status
Returns:
{
  "universe": "api",
  "page": "status",
  "status": "ok",
  "time": 1700000000
}

🔐 Admin Universe Example
?u=admin&p=index        → forbidden
?u=admin&p=index&key=demo → allowed

⚡ Getting Started
1. Clone
git clone https://github.com/xxx/Origin-Core.git
2. Run
cd Origin-Core/template
php -S localhost:8000 index.php

Open in browser:

?u=home&p=index

?u=home&p=auth/login

?u=api&p=status

?u=admin&p=index&key=demo

?u=blog&p=index

🧬 Create a New Universe
Create folder: universes/shop/pages/index.php
Add:
<?php declare(strict_types=1); ?>

<?php render_origin_page('Shop Universe', function () { ?>
    <h1>Hello from Shop Universe!</h1>
<?php }); ?>
Visit: ?u=shop&p=index

📊 Architecture Comparison
Feature	Origin-Core	Laravel	Symfony	Slim	Classic MVC
Zero Config Routing	✔	✘	✘	✘	✘
Add/Delete Module w/o touching core	✔	✘	✘	✘	✘
Multi-Universe	✔	✘	✘	✘	✘
Infinite Page Depth	✔	⚠	⚠	✔	⚠
Core Stability	★★★★★	★★	★★★	★★★	★★
Learning Curve	★☆☆	★★★	★★★★	★☆☆	★★

🏁 License

MIT

