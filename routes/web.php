<?php
use App\Controllers\{StoreController, AuthController, AdminController};
$s = new StoreController();
$a = new AuthController();
$admin = new AdminController();
if ($method === 'GET') {
    if ($path === '/track-order') { $s->trackPage(); return; }
    if (preg_match('~^/order/([a-f0-9]{64})/status$~', $path, $m)) { $s->orderStatus($m[1]); return; }
    if (preg_match('~^/control-center/(products|categories|features|gallery|reviews|faqs|team)/new$~', $path, $m)) { $admin->page($m[1], 'new'); return; }
    if (preg_match('~^/control-center/(products|categories|features|gallery|reviews|faqs|team)/([1-9][0-9]*)/edit$~', $path, $m)) { $admin->page($m[1], 'edit', (int) $m[2]); return; }
    if (preg_match('~^/control-center/(orders|reservations|messages|payments)/([1-9][0-9]*)$~', $path, $m)) { $admin->detail($m[1], (int) $m[2]); return; }
    if ($path==='/control-center/media/list') {(new App\Controllers\MediaController())->listing();return;}
    if (preg_match('~^/media/([a-f0-9]{40}\.(?:webp|jpg|png))$~', $path, $m)) {
        $f = ROOT . '/storage/uploads/' . $m[1];
        if (!is_file($f)) {
            http_response_code(404);
            return;
        }
        header('Content-Type: ' . match (pathinfo($f, PATHINFO_EXTENSION)) {'jpg'=>'image/jpeg','png'=>'image/png',default=>'image/webp'});
        header('Content-Length: ' . filesize($f));
        header('Cache-Control: public,max-age=31536000,immutable');
        readfile($f);
        return;
    }
    if ($path === '/api/health') {
        header('Content-Type: application/json');
        App\Core\DB::one('SELECT COUNT(*) n FROM settings');
        echo '{"status":"ok"}';
        return;
    }
    if ($path === '/admin') {
        redirect('/control-center');
    }
    if ($path === '/login') {
        $a->loginPage();
        return;
    }
    if ($path === '/forgot-password') {
        $a->forgotPage();
        return;
    }
    if ($path === '/reset-password') {
        $a->resetPage();
        return;
    }
    if (preg_match('~^/control-center/export/(orders|customers|reservations)$~', $path, $m)) {
        $admin->export($m[1]);
        return;
    }
    if (preg_match('~^/control-center(?:/([a-z-]+))?/?$~', $path, $m)) {
        $admin->page($m[1] ?? 'dashboard');
        return;
    }
    if (preg_match('~^/menu/([a-z0-9-]+)$~', $path, $m)) {
        $s->product($m[1]);
        return;
    }
    if (preg_match('~^/order/([a-f0-9]{64})$~', $path, $m)) {
        $s->order($m[1]);
        return;
    }
    $pages = [
        '/' => 'home',
        '/menu' => 'menu',
        '/about' => 'about',
        '/gallery' => 'gallery',
        '/contact' => 'contact',
        '/cart' => 'cart',
        '/checkout' => 'checkout',
        '/faq'=>'faq', '/delivery'=>'delivery', '/privacy'=>'privacy', '/terms'=>'terms',
    ];
    if (isset($pages[$path])) {
        $s->page($pages[$path]);
        return;
    }
}
if ($method === 'POST') {
    if ($path === '/track-order') { $s->track(); return; }
    if ($path==='/control-center/media/upload') {(new App\Controllers\MediaController())->upload();return;}
    if (preg_match('~^/payment/(ipn|success|fail|cancel)$~', $path, $m)) {
        $s->paymentCallback($m[1]);
        return;
    }
    if (
        preg_match(
            '~^/control-center/(products|categories|features|gallery|reviews|faqs)/(save|delete)$~',
            $path,
            $m,
        )
    ) {
        $m[2] === 'save' ? $admin->saveEntity($m[1]) : $admin->deleteEntity($m[1]);
        return;
    }
    if (preg_match('~^/control-center/(orders|cash|reservations|messages)/status$~', $path, $m)) {
        $admin->status($m[1]);
        return;
    }
    switch ($path) {
        case '/login':
            $a->login();
            return;
        case '/logout':
            $a->logout();
            return;
        case '/forgot-password':
            $a->forgot();
            return;
        case '/reset-password':
            $a->reset();
            return;
        case '/control-center/security':
            $a->password();
            return;
        case '/control-center/settings':
            $admin->saveSettings();
            return;
        case '/control-center/team':
            $admin->team();
            return;
        case '/cart':
            $s->cart();
            return;
        case '/checkout':
            $s->checkout();
            return;
        case '/pay':
            $s->pay();
            return;
        case '/contact':
            $s->contact('message');
            return;
        case '/reservations':
            $s->contact('reservation');
            return;
    }
}
http_response_code(404);
view('error', ['title' => 'Page not found', 'message' => 'The page you requested does not exist.']);
