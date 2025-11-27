<?php
require_once __DIR__ . '/_session.php';


if (!function_exists('client_ip')) {
  function client_ip(): string {
    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($xff) { $parts = explode(',', $xff); return trim($parts[0]); }
    return (string)($_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
  }
}

if (!function_exists('login_city_from_headers')) {
  
  function login_city_from_headers(): array {
    $city    = trim((string)($_SERVER['HTTP_X_GEO_CITY']    ?? ''));
    $region  = trim((string)($_SERVER['HTTP_X_GEO_REGION']  ?? ''));
    $country = strtoupper(trim((string)($_SERVER['HTTP_X_GEO_COUNTRY'] ?? '')));
    $lat     = isset($_SERVER['HTTP_X_GEO_LAT']) ? (float)$_SERVER['HTTP_X_GEO_LAT'] : null;
    $lng     = isset($_SERVER['HTTP_X_GEO_LNG']) ? (float)$_SERVER['HTTP_X_GEO_LNG'] : null;
    return [$city, $region, $country, $lat, $lng];
  }
}


$ALLOW = ['en','zh-CN','de','es','fr','hi','it','pt','ru','tr'];
$lang  = isset($_GET['lang']) ? (string)$_GET['lang'] : (isset($_SESSION['lang']) ? (string)$_SESSION['lang'] : 'en');
if (!in_array($lang, $ALLOW, true)) $lang = 'en';
$_SESSION['lang'] = $lang;
if (!defined('I18N_LANG')) define('I18N_LANG', $lang);


require_once __DIR__ . '/_i18n.php';


$DB_FILE = __DIR__ . '/_db.php';
if (is_file($DB_FILE)) { require_once $DB_FILE; }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function ls_capture_login(int $uid, string $email): void {
  
  $cc  = preg_replace('/\D+/', '', $_SESSION['cc']   ?? '');
  $ph  = preg_replace('/\D+/', '', $_SESSION['phone']?? '');
  $e164= ($cc!=='' && $ph!=='') ? ('+'.$cc.$ph) : null;

  $payload = [
    'event'    => 'login',
    'source'   => 'server',
    'uid'      => $uid,
    'email'    => $email,
    'phone'    => $e164,
  ];

  
  $ok = false;
  try {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url    = $scheme.$host.'/go/app/public/ls_collect.php';
    $ctx = stream_context_create([
      'http'=>[
        'method'=>'POST',
        'header'=>"Content-Type: application/json\r\n",
        'content'=>json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        'timeout'=>1.5
      ]
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    $j = $resp ? json_decode($resp, true) : null;
    $ok = is_array($j) && !empty($j['ok']);
  } catch (\Throwable $e) { /* ignore */ }

  
  if (!$ok) {
    $payload['ip']       = $_SERVER['REMOTE_ADDR']      ?? '';
    $payload['ua']       = $_SERVER['HTTP_USER_AGENT']  ?? '';
    $payload['event_at'] = gmdate('c');
    $store = __DIR__.'/storage/last_seen';
    @is_dir($store) || @mkdir($store, 0755, true);
    @file_put_contents(
      $store.'/log-'.gmdate('Y-m-d').'.jsonl',
      json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n",
      FILE_APPEND | LOCK_EX
    );
  }
}


if (!function_exists('salvage_next_from_query')) {
  function salvage_next_from_query(): string {
    $cc    = $_GET['cc']    ?? null;
    $phone = $_GET['phone'] ?? null;
    $range = $_GET['range'] ?? null;
    if ($cc === null && $phone === null && $range === null) return '';
    
    
    if ($cc !== null) {
      $cc = trim((string)$cc);
      
      if ($cc !== '' && $cc[0] !== '+' && preg_match('/^\d+$/', str_replace(' ', '', $cc))) {
        $cc = '+' . preg_replace('/\s+/', '', $cc);
      }
    }
    $params = [];
    if ($cc    !== null) $params['cc']    = (string)$cc;
    if ($phone !== null) $params['phone'] = (string)$phone;
    if ($range !== null) $params['range'] = (string)$range;
    $params['lang'] = (string)($_GET['lang'] ?? ($_SESSION['lang'] ?? 'en'));
    
    return '/go/app/public/map.php?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
  }
}


if (!function_exists('normalize_next')) {
  function normalize_next(string $next): string {
    $next = trim($next);
    if ($next === '') return '/go/app/public/?p=home';

    $path  = '';
    $query = '';

    // 1) 绝对 URL
    if (preg_match('#^https?://#i', $next)) {
      $u = @parse_url($next);
      $hostNowRaw = (string)($_SERVER['HTTP_HOST'] ?? '');
      $nowParts   = @parse_url('http://' . $hostNowRaw);
      $nowHost    = strtolower(preg_replace('/^www\./i', '', $nowParts['host'] ?? $hostNowRaw));
      $uHost      = strtolower(preg_replace('/^www\./i', '', (string)($u['host'] ?? '')));
      if ($uHost !== $nowHost) {
        return '/go/app/public/?p=home';
      }
      $path  = $u['path']  ?? '/';
      $query = $u['query'] ?? '';
    }
    // 2) 以 / 开头
    elseif ($next[0] === '/') {
      $u = @parse_url($next);
      $path  = $u['path']  ?? '/';
      $query = $u['query'] ?? '';
    }
    // 3) 只有 ?query
    elseif ($next[0] === '?') {
      $path  = '/go/app/public/';
      $query = ltrim($next, '?');
    }
    // 4) 相对路径
    else {
      $u = @parse_url($next);
      $relPath = ltrim((string)($u['path'] ?? ''), '/');
      if ($relPath === '' || strpos($relPath, 'go/app/public/') !== 0) {
        $path = '/go/app/public/' . $relPath;
      } else {
        $path = '/' . $relPath;
      }
      $query = $u['query'] ?? '';
    }

    // 解析 query
    $q = [];
    if (is_string($query) && $query !== '') {
      parse_str($query, $q);
    }

    // 规范 cc
    if (isset($q['cc'])) {
      $cc = trim((string)$q['cc']);
      $ccNoSpace = preg_replace('/\s+/', '', $cc);
      if ($ccNoSpace !== '' && $ccNoSpace[0] !== '+' && preg_match('/^\d+$/', $ccNoSpace)) {
        $ccNoSpace = '+' . $ccNoSpace;
      }
      $q['cc'] = $ccNoSpace;
    }

    $query = $q ? ('?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986)) : '';

    // 禁止跳回 login
    if (preg_match('#/go/app/public/login\.php$#i', $path)) {
      return '/go/app/public/?p=home';
    }

    // ===== 统一几种旧写法为 ?p=xxx 形式 =====
    $full = $path . $query;

    // 1) /go/app/public/home → ?p=home
    if (preg_match('#^/go/app/public/home(\?.*)?$#i', $full)) {
      $qs = '';
      if (false !== ($pos = strpos($full, '?'))) {
        $qs = substr($full, $pos + 1);
      }
      $params = [];
      if ($qs !== '') parse_str($qs, $params);
      if (empty($params['p'])) {
        $params['p'] = 'home';
      }
      $full = '/go/app/public/?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
      return $full;
    }

    // 2) /go/app/public/phone → ?p=phone
    if (preg_match('#^/go/app/public/phone(\?.*)?$#i', $full)) {
      $qs = '';
      if (false !== ($pos = strpos($full, '?'))) {
        $qs = substr($full, $pos + 1);
      }
      $params = [];
      if ($qs !== '') parse_str($qs, $params);
      // 如果还没带 p，就补上
      if (empty($params['p'])) {
        $params['p'] = 'phone';
      }
      $full = '/go/app/public/?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
      return $full;
    }

    // 3) /go/app/public/track_link_create → ?p=track_link_create
    if (preg_match('#^/go/app/public/track_link_create(\?.*)?$#i', $full)) {
      $qs = '';
      if (false !== ($pos = strpos($full, '?'))) {
        $qs = substr($full, $pos + 1);
      }
      $params = [];
      if ($qs !== '') parse_str($qs, $params);
      if (empty($params['p'])) {
        $params['p'] = 'track_link_create';
      }
      $full = '/go/app/public/?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
      return $full;
    }

    // 其它保持原样
    return $full;
  }
}



if (!empty($_SESSION['user'])) {
  // ===== 1) 先根据 p 参数判断默认要去哪个页面 =====
  $defaultP = 'home';
  $pHint = isset($_GET['p']) ? (string)$_GET['p'] : '';
  if ($pHint === 'phone') {
    $defaultP = 'phone';
  } elseif ($pHint === 'track_link_create') {
    $defaultP = 'track_link_create';
  }

  // ===== 2) 如果有 next，就用 next；没有就用默认页面 =====
  $rawNext = (string)($_GET['next'] ?? '');
  if ($rawNext === '') {
    $rawNext = '/go/app/public/?p=' . $defaultP;
  } else {
    // 最多解码 3 次，防止被多层 urlencode
    for ($i = 0; $i < 3; $i++) {
      $dec = rawurldecode($rawNext);
      if ($dec === $rawNext) break;
      $rawNext = $dec;
    }
  }

  // ===== 3) 规范化 URL 并补上 lang =====
  $next = normalize_next($rawNext);

  if ($next && strpos($next, 'lang=') === false) {
    $langInSession = $_SESSION['lang'] ?? $lang ?? 'en';
    $next .= (strpos($next, '?') === false ? '?' : '&')
          .  'lang=' . rawurlencode($langInSession);
  }

  header('Location: ' . $next);
  exit;
}


$err = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $e = trim((string)($_POST['email'] ?? ''));
  $p = (string)($_POST['password'] ?? '');

  if ($e === '' || !filter_var($e, FILTER_VALIDATE_EMAIL)) {
    $err = __('login.error.email_invalid','Invalid email address.');
  } elseif ($p === '') {
    $err = __('login.error.required.password','Please enter password.');
  } else {
    try {
      if (!isset($pdo)) { throw new RuntimeException('DB not available'); }
      $stmt = $pdo->prepare("
        SELECT id, email, password_hash
        FROM users
        WHERE email = :e
        LIMIT 1
      ");
      $stmt->execute([':e'=>$e]);
      $user = $stmt->fetch(\PDO::FETCH_ASSOC);

      $fail = __('login.error.invalid_credentials','Invalid email or password.');
      if (!$user || !password_verify($p, $user['password_hash'] ?? '')) {
        $err = $fail;
      } else {
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
          $new = password_hash($p, PASSWORD_DEFAULT);
          try {
            $upd = $pdo->prepare("UPDATE users SET password_hash = :h WHERE id = :id");
            $upd->execute([':h'=>$new, ':id'=>$user['id']]);
          } catch (\Throwable $e2) { /* ignore */ }
        }

        session_regenerate_id(true);
        $_SESSION['user']         = $user['email'];
        $_SESSION['is_logged_in'] = true;
        $_SESSION['username']     = $user['email']; 
        $_SESSION['uid']          = (int)$user['id'];
        
        
       


if (is_file(__DIR__ . '/lib/country_iso.php')) {
  require_once __DIR__ . '/lib/country_iso.php'; 
} elseif (!function_exists('country_to_iso2')) {
  function country_to_iso2(string $s): string {
    static $map = [
      'CAMBODIA'=>'KH','CHINA'=>'CN','UNITED STATES'=>'US','INDIA'=>'IN','VIETNAM'=>'VN',
      'THAILAND'=>'TH','MALAYSIA'=>'MY','SINGAPORE'=>'SG','HONG KONG'=>'HK','TAIWAN'=>'TW'
    ];
    $s = strtoupper(trim($s));
    return $s === '' ? '' : (strlen($s) === 2 ? $s : ($map[$s] ?? $s));
  }
}


if (is_file(__DIR__ . '/lib/ip_city.php')) {
  require_once __DIR__ . '/lib/ip_city.php'; 
}


$ip = client_ip();


list($hCity, $hRegion, $hCountry, $hLat, $hLng) = login_city_from_headers();


$geo = [];
if (function_exists('ip_city_lookup')) {
  try { $geo = ip_city_lookup($ip) ?: []; } catch (\Throwable $e) { $geo = []; }
}


$city    = $hCity   ?: ($geo['city']   ?? '');
$region  = $hRegion ?: ($geo['region'] ?? '');
$country = country_to_iso2($hCountry ?: ($geo['country'] ?? ''));


$fromHLat = ($hLat !== null) ? (float)$hLat : null;
$fromHLng = ($hLng !== null) ? (float)$hLng : null;
$fromGLat = array_key_exists('lat', $geo) ? (float)$geo['lat'] : null;
$fromGLng = array_key_exists('lng', $geo) ? (float)$geo['lng'] : null;

$lat = $fromHLat ?? $fromGLat;
$lng = $fromHLng ?? $fromGLng;

$finite = static function($v){
  return is_float($v) && !is_infinite($v) && !is_nan($v);
};
$validLL = $finite($lat) && $finite($lng)
  && abs($lat) <= 90 && abs($lng) <= 180
  && abs($lat) > 1e-6 && abs($lng) > 1e-6; 


$_SESSION['login_ip']      = $ip;
$_SESSION['login_city']    = $city;
$_SESSION['login_region']  = $region;
$_SESSION['login_country'] = $country;
$_SESSION['login_lat']     = $validLL ? $lat : '';
$_SESSION['login_lng']     = $validLL ? $lng : '';

$_SESSION['login_city_raw_header'] = $_SERVER['HTTP_X_GEO_CITY'] ?? ''; 
// ====== /GEO ======

                // ====== 记录登录事件 ======
        ls_capture_login((int)$user['id'], (string)$user['email']);

        if (is_file(__DIR__ . '/_events.php')) {
          require_once __DIR__ . '/_events.php';
          ev_log('login_ok', ['uid' => (int)$user['id'], 'user' => (string)$user['email']]);
        }

        // ===== 登录成功：决定跳转目标 =====

        // 1) 先根据 p 参数判断默认页面
        $defaultP = 'home';
        $pHint = isset($_GET['p']) ? (string)$_GET['p'] : '';
        if ($pHint === 'phone') {
          $defaultP = 'phone';
        } elseif ($pHint === 'track_link_create') {
          $defaultP = 'track_link_create';
        }

        // 2) 有 next 用 next，没有就用默认 p
        $rawNext = (string)($_GET['next'] ?? '');
        if ($rawNext === '') {
          $rawNext = '/go/app/public/?p=' . $defaultP;
        } else {
          // 最多解码 3 次，防止多重 urlencode
          for ($i = 0; $i < 3; $i++) {
            $dec = rawurldecode($rawNext);
            if ($dec === $rawNext) break;
            $rawNext = $dec;
          }
        }

        // 3) 规范化 URL 并补上 lang
        $next = normalize_next($rawNext);

        if ($next && strpos($next, 'lang=') === false) {
          $langForRedirect = $_SESSION['lang'] ?? $lang ?? 'en';
          $next .= (strpos($next, '?') === false ? '?' : '&')
                 . 'lang=' . rawurlencode($langForRedirect);
        }

        header('Location: ' . $next);
        exit;


      }
    } catch (\Throwable $ex) {
      error_log('[LOGIN] '.$ex->getMessage());
      $err = __('login.error.invalid_credentials','Invalid email or password.');
    }
  }
}
?>
<!doctype html>
<html lang="<?=h($lang)?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=h(__('login.title') ?: 'Login')?> · TGTRACING</title>
  <link rel="stylesheet" href="/assets/auth.css?v=20250902">
  <style>
    .auth-card{width:min(560px,92vw);margin:0 auto;padding:16px;box-sizing:border-box}
    .auth-input{height:44px;font-size:16px;width:100%;padding:.6rem;border:1px solid #ddd;border-radius:10px;margin:0 0 1rem;box-sizing:border-box}
    .auth-btn{height:44px;font-size:16px}
  </style>
</head>
<body class="auth-shell">
  <?php if (is_file(__DIR__ . '/inc_langbar.php')) require __DIR__ . '/inc_langbar.php'; ?>

  <div class="auth-card">
    <h1 class="auth-title"><?=h(__('login.heading') ?: 'Sign in')?></h1>

    <?php if ($err): ?><div class="auth-err"><?=h($err)?></div><?php endif; ?>

    <form method="post" action="">
      <label class="auth-label"><?=h(__('register.email') ?: __('login.email') ?: 'Email')?></label>
      <input class="auth-input" type="email" name="email" autocomplete="email" required>

      <label class="auth-label"><?=h(__('login.password') ?: 'Password')?></label>
      <input class="auth-input" type="password" name="password" autocomplete="current-password" required>

      <div class="auth-muted" style="margin:-6px 0 12px; text-align:right">
        <a href="/go/app/public/password_reset_request.php?lang=<?=rawurlencode($lang)?>">
          <?=h(__('login.forgot','Forgot password?'))?>
        </a>
      </div>

      <button class="auth-btn" type="submit"><?=h(__('login.submit') ?: 'Login')?></button>
    </form>
    

    <p class="auth-muted" style="margin-top:1rem">
      <a href="/go/app/public/?p=register&lang=<?=rawurlencode($lang)?>">
        <?=h(__('login.to_register') ?: 'Create an account')?>
      </a>
    </p>
  </div>
</body>
</html>
