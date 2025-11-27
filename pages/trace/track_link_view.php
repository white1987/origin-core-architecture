<?php
// /go/app/public/register.php  — ensure DB ($pdo) is available
require_once __DIR__ . '/_session.php';

/* ① 语言固定（沿用你的白名单） */
$ALLOW = ['en','zh-CN','de','es','fr','hi','it','pt','ru','tr'];
$lang  = isset($_GET['lang']) ? (string)$_GET['lang'] : (isset($_SESSION['lang']) ? (string)$_SESSION['lang'] : 'en');
if (!in_array($lang, $ALLOW, true)) $lang = 'en';
$_SESSION['lang'] = $lang;
if (!defined('I18N_LANG')) define('I18N_LANG', $lang);

/* ② i18n */
require_once __DIR__ . '/_i18n.php';

/* ③ 显式接入数据库（关键修复） */
if (!class_exists('PDO')) { die('PDO not available'); }
// 优先引入你现有的 _db.php（若 _session.php 里没引过）
if (!isset($pdo) || !($pdo instanceof PDO)) {
  $dbFile = __DIR__ . '/_db.php';
  if (is_file($dbFile)) {
    require_once $dbFile;
  }
}
// 仍然拿不到就报 500，避免“无提示失败”
if (!isset($pdo) || !($pdo instanceof PDO)) {
  http_response_code(500);
  $fatal = true;
}

/* ④ 小工具 */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'en');

/* ⑤ CSRF */
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }

$err = ''; $ok = '';

/* ⑥ POST: 执行注册 */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $err = __('reset.error.csrf','Security check failed. Please refresh and try again.');
  } else {
    $e  = trim((string)($_POST['email'] ?? ''));
    $p1 = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['confirm_password'] ?? '');

    if ($e === '' || !filter_var($e, FILTER_VALIDATE_EMAIL)) {
      $err = __('register.error.email_invalid','Invalid email address.');
    } elseif ($p1 === '' || $p2 === '') {
      $err = __('register.error.required.password','Please enter password.');
    } elseif ($p1 !== $p2) {
      $err = __('register.error.password_mismatch','Passwords do not match.');
    } elseif (strlen($p1) < 8) {
      $err = __('register.error.password_policy','Password must be at least 8 characters.');
    } elseif (!isset($pdo) || !($pdo instanceof PDO)) {
      $err = __('common.error','Error');
    } else {
      try {
        // users 表：email 唯一
        $q = $pdo->prepare("SELECT 1 FROM users WHERE email = :e LIMIT 1");
        $q->execute([':e'=>$e]);
        if ($q->fetchColumn()) {
          $err = __('register.error.duplicate','Email already exists.');
        } else {
          $hash = password_hash($p1, PASSWORD_DEFAULT);
          $ins  = $pdo->prepare("
            INSERT INTO users (email, password_hash, created_at)
            VALUES (:e, :h, :ts)
          ");
          $ins->execute([':e'=>$e, ':h'=>$hash, ':ts'=>time()]);
          $ok = __('register.success','Registration successful.');
          // 如需：注册后跳转登录
          // header('Location: /go/app/public/login.php?lang='.rawurlencode($lang)); exit;
        }
      } catch (\Throwable $ex) {
        error_log('[REGISTER] '.$ex->getMessage());
        $err = __('common.error','Error');
      }
    }
  }
}

/* ⑦ 诊断：?diag=1 显示 DB 与表状态（临时调试用） */
if (isset($_GET['diag']) && $_GET['diag']=='1') {
  header('Content-Type: text/plain; charset=utf-8');
  echo "lang={$lang}\n";
  echo "pdo=".(isset($pdo)&&$pdo instanceof PDO?'OK':'MISSING')."\n";
  if (isset($pdo) && $pdo instanceof PDO) {
    try {
      $stm = $pdo->query("SHOW TABLES");
      $tables = $stm ? $stm->fetchAll(PDO::FETCH_COLUMN) : [];
      echo "tables: ".implode(',', $tables)."\n";
      // 检查 users 表字段
      $stm = $pdo->query("SHOW COLUMNS FROM users");
      $cols = $stm ? $stm->fetchAll(PDO::FETCH_COLUMN) : [];
      echo "users.columns: ".implode(',', $cols)."\n";
    } catch (\Throwable $e) {
      echo "diag.error: ".$e->getMessage()."\n";
    }
  }
  exit;
}
?>
<!doctype html>
<html lang="<?=h($lang)?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=h(__('register.title','Create account'))?> · TGTRACING</title>
  <link rel="stylesheet" href="/assets/auth.css?v=20250902">
  <style>
    .auth-card{max-width:560px;margin:6rem auto;padding:2rem;border-radius:16px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.08)}
    .title{margin:0 0 1rem;font-size:1.6rem;font-weight:800;color:#111}
    .label{display:block;margin:.25rem 0 .25rem;color:#333}
    .in{width:100%;padding:.6rem;border:1px solid #ddd;border-radius:10px;margin:.25rem 0 1rem;box-sizing:border-box}
    .btn{display:inline-block;padding:.6rem 1rem;border-radius:10px;background:#111;color:#fff;text-decoration:none;border:0;cursor:pointer}
    .muted{color:#666;font-size:.92rem}
    .err{color:#c00;margin:.5rem 0 0}
    .ok{color:#0a7a2d;margin:.5rem 0 0}
  </style>
</head>
<body>

  <?php if (is_file(__DIR__ . '/inc_langbar.php')) { require __DIR__ . '/inc_langbar.php'; } ?>

  <div class="auth-card">
    <h2 class="title"><?=h(__('register.heading', __('register.title','Create account')))?></h2>

    <?php if (!empty($fatal)): ?>
      <div class="err"><?=h(__('common.error','Error'))?> — DB not ready</div>
    <?php endif; ?>

    <?php if ($err): ?><div class="err"><?=h($err)?></div><?php endif; ?>
    <?php if ($ok):  ?><div class="ok"><?=h($ok)?></div><?php endif; ?>

    <form class="auth-form" method="post" action="">
      <input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'] ?? '')?>">

      <label class="label" for="email"><?=h(__('register.email','Email'))?></label>
      <input class="in" id="email" type="email" name="email"
       placeholder="<?=h(__('auth.placeholder.email','Enter your email'))?>"
       value="<?=h($_POST['email'] ?? '')?>" required>

      <label class="label" for="password"><?=h(__('register.password','Password'))?></label>
      <input class="in" id="password" type="password" name="password"
             placeholder="<?=h(__('auth.placeholder.password','Enter your password'))?>" required>

      <label class="label" for="confirm_password"><?=h(__('register.confirm_password','Confirm Password'))?></label>
<input class="in" id="confirm_password" type="password" name="confirm_password"
       placeholder="<?=h(__('register.confirm_password','Confirm Password'))?>" required>

      <button class="btn" type="submit"><?=h(__('register.submit','Create account'))?></button>
    </form>


    <p class="muted" style="margin-top:1rem">
      <a href="/go/app/public/login.php?lang=<?=rawurlencode($lang)?>">
        <?=h(__('register.to_login','Sign in'))?>
      </a>
    </p>
  </div>

</body>
</html>
