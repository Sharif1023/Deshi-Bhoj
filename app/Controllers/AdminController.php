<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{DB, Auth, ValidationException};
use App\Services\{Content, OrderService, UploadService, RichText, MediaService, Translations, Locale};
final class AdminController
{
    public function page(string $section = 'dashboard', string $mode = 'list', int $editId = 0): void
    {
        Auth::requireUser(in_array($section, ['settings', 'team'], true));
        if (isset($_GET['edit']) && $mode === 'list') redirect('/control-center/' . $section . '/' . (int) $_GET['edit'] . '/edit');
        $configs = require ROOT . '/config/entities.php';
        $data = [
            'admin' => true,
            'section' => $section,
            'mode' => $mode,
            'editingLocale' => Locale::current(),
            'title' => ucwords(str_replace('-', ' ', $section)),
        ];
        if (isset($configs[$section])) {
            $data['config'] = $configs[$section];
            $where = []; $args = [];
            $query = is_string($_GET['q'] ?? null) ? trim(substr($_GET['q'], 0, 100)) : '';
            $categoryId = max(0, (int) ($_GET['category_id'] ?? 0));
            if ($section === 'products' && $categoryId) { $where[] = 'category_id=?'; $args[] = $categoryId; }
            if ($section === 'categories' && $categoryId) { $where[] = 'id=?'; $args[] = $categoryId; }
            if ($query !== '') {
                $column = isset($data['config']['fields']['name']) ? 'name' : 'title';
                $where[] = "($column LIKE ? OR EXISTS (SELECT 1 FROM content_translations ct WHERE ct.entity=? AND ct.entity_id=$section.id AND ct.value LIKE ?))";
                array_push($args, '%' . $query . '%', $section, '%' . $query . '%');
            }
            $data['rows'] = $mode === 'list' ? Translations::rows($section, DB::all("SELECT * FROM $section" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY sort_order,id', $args)) : [];
            $data['categories'] = Translations::rows('categories', DB::all('SELECT * FROM categories ORDER BY sort_order,id'));
            $data['edit'] = $mode === 'edit' ? DB::one("SELECT * FROM $section WHERE id=?", [$editId]) : null;
            if ($mode === 'edit' && !$data['edit']) throw new ValidationException('Item not found.');
            $data['categoryId'] = $categoryId; $data['query'] = $query;
            $template = $mode === 'list' ? 'entities' : 'entity-edit';
        } else {
            $template = $section;
            switch ($section) {
                case 'dashboard':
                    $data['stats'] = [
                        'Homepage views' => setting('analytics.views', 0),
                        'Orders' => DB::one('SELECT COUNT(*) n FROM orders')['n'],
                        'Collected revenue' => money(
                            (int) DB::one(
                                "SELECT COALESCE(SUM(total),0) n FROM orders WHERE payment_status='paid'",
                            )['n'],
                        ),
                        'Reservations' => DB::one(
                            "SELECT COUNT(*) n FROM reservations WHERE status='Pending'",
                        )['n'],
                        'Unread messages' => DB::one(
                            "SELECT COUNT(*) n FROM messages WHERE status='Unread'",
                        )['n'],
                    ];
                    $data['orders'] = DB::all('SELECT * FROM orders ORDER BY id DESC LIMIT 8');
                    $data['activities'] = DB::all(
                        'SELECT * FROM activities ORDER BY id DESC LIMIT 12',
                    );
                    break;
                case 'orders':
                    $this->orders();
                    return;
                case 'reservations':
                case 'messages':
                case 'payments':
                    $where = '';
                    $args = [];
                    if (!empty($_GET['q'])) {
                        $column = match ($section) {
                            'orders' => 'number',
                            'payments' => 'transaction_id',
                            default => 'name',
                        };
                        $where = " WHERE $column LIKE ?";
                        $args[] = '%' . substr((string) $_GET['q'], 0, 100) . '%';
                    }
                    $page = max(1, (int) ($_GET['page'] ?? 1));
                    $data['pageNumber'] = $page;
                    $data['rows'] = DB::all(
                        "SELECT * FROM $section" .
                            $where .
                            ' ORDER BY id DESC LIMIT 25 OFFSET ' .
                            ($page - 1) * 25,
                        $args,
                    );
                    $data['total'] = (int) DB::one(
                        "SELECT COUNT(*) n FROM $section" . $where,
                        $args,
                    )['n'];
                    $template = 'operations';
                    break;
                case 'media':
                    $data['rows']=DB::all('SELECT * FROM media ORDER BY id DESC LIMIT 200');
                    break;
                case 'customers':
                    $data['rows'] = DB::all(
                        'SELECT email,MAX(customer_name) name,MAX(phone) phone,COUNT(*) orders_count,SUM(total) total FROM orders GROUP BY email ORDER BY orders_count DESC',
                    );
                    break;
                case 'settings':
                    $data['groups'] = require ROOT . '/config/settings.php';
                    $data['selectedGroup'] = is_string($_GET['group'] ?? null) ? $_GET['group'] : '';
                    if ($data['selectedGroup'] !== '' && !isset($data['groups'][$data['selectedGroup']])) throw new ValidationException('Unknown settings page.');
                    break;
                case 'team':
                    $data['rows'] = DB::all('SELECT id,name,email,role FROM users ORDER BY id');
                    $data['edit'] = $mode === 'edit' ? DB::one('SELECT id,name,email,role FROM users WHERE id=?', [$editId]) : null;
                    if ($mode === 'edit' && (!$data['edit'] || $editId === (int) user()['id'])) throw new ValidationException('Use account security for your own account.');
                    break;
                case 'security':
                    break;
                default:
                    http_response_code(404);
                    view('error', [
                        'title' => 'Page not found',
                        'message' => 'This admin section does not exist.',
                    ]);
                    return;
            }
        }
        view('admin/' . $template, $data);
    }
    public function saveEntity(string $entity): void
    {
        Auth::requireUser();
        $configs = require ROOT . '/config/entities.php';
        if (!isset($configs[$entity])) {
            throw new ValidationException('Unknown content type.');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $translations = Translations::submitted($entity, $configs[$entity]['fields']);
        $editingLanguage = in_array($_POST['content_locale'] ?? '', ['en', 'bn'], true) ? $_POST['content_locale'] : 'en';
        foreach (Translations::fields($entity) as $field => $_type) {
            if ($translations) $_POST[$field] = plain($translations[$editingLanguage][$field]) !== '' ? $translations[$editingLanguage][$field] : $translations[$editingLanguage === 'en' ? 'bn' : 'en'][$field];
        }
        $values = [];
        foreach ($configs[$entity]['fields'] as $field => $type) {
            $value =
                $type === 'checkbox'
                    ? (isset($_POST[$field])
                        ? 1
                        : 0)
                    : input(
                        $field,
                        in_array($type, ['textarea', 'images', 'rich', 'rich-inline']) ? 20000 : 500,
                        !str_ends_with($type, '?') && !in_array($type, ['image', 'images']) && $field !== 'allergens',
                    );
            if ($type === 'slug' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
                throw new ValidationException(
                    'Slug must contain lowercase letters, numbers and hyphens.',
                );
            }
            if (in_array($type, ['number', 'rating', 'category'])) {
                if (
                    filter_var($value, FILTER_VALIDATE_INT) === false ||
                    (int) $value < 0 ||
                    (int) $value > 100000
                ) {
                    throw new ValidationException('Invalid ' . $field . '.');
                }
                $value = (int) $value;
                if ($type === 'rating' && ($value < 1 || $value > 5)) {
                    throw new ValidationException('Rating must be 1–5.');
                }
                if (
                    $type === 'category' &&
                    !DB::one('SELECT id FROM categories WHERE id=?', [$value])
                ) {
                    throw new ValidationException('Choose a category.');
                }
            }
            if ($type === 'money') {
                $value = $this->parseMoney($value);
                if ($value < 100 || $value > 10000000) {
                    throw new ValidationException('Product price must be ৳1–100,000.');
                }
            }
            if (in_array($type, ['rich','rich-inline'])) {
                $value=RichText::clean($value, $type==='rich-inline');
                if ($field!=='allergens' && RichText::plain($value)==='') throw new ValidationException('Please complete '.$field.'.');
            }
            if (in_array($type, ['image', 'images'])) {
                $list=MediaService::validateImages($value, $type==='images'?12:1);
                $value=$type==='images'?json_encode($list?:['/assets/kacchi.png']):($list[0]??'/assets/kacchi.png');
            }
            $values[$field] = $value;
        }
        DB::transaction(function () use ($entity, $id, $values, $translations) {
            if ($id) {
                if (!DB::one("SELECT id FROM $entity WHERE id=?", [$id])) {
                    throw new ValidationException('Item not found.');
                }
                $set = implode(',', array_map(fn($f) => $f . '=?', array_keys($values)));
                DB::run("UPDATE $entity SET $set WHERE id=?", [...array_values($values), $id]);
            } else {
                $id = DB::insert($entity, $values);
            }
            Translations::save($entity, $id, $translations);
            DB::audit(ucfirst($entity) . ' saved');
        });
        flash('Changes saved.');
        redirect('/control-center/' . $entity);
    }
    public function deleteEntity(string $entity): void
    {
        Auth::requireUser();
        $configs = require ROOT . '/config/entities.php';
        if (!isset($configs[$entity])) {
            throw new ValidationException('Unknown content type.');
        }
        $id = (int) input('id');
        if (
            $entity === 'categories' &&
            DB::one('SELECT id FROM products WHERE category_id=?', [$id])
        ) {
            throw new ValidationException(
                'Move products to another category before deleting this category.',
            );
        }
        DB::transaction(function () use ($entity, $id) {
            DB::run('DELETE FROM content_translations WHERE entity=? AND entity_id=?', [$entity, $id]);
            DB::run("DELETE FROM $entity WHERE id=?", [$id]);
        });
        DB::audit(ucfirst($entity) . ' #' . $id . ' deleted');
        flash('Item deleted.');
        redirect('/control-center/' . $entity);
    }
    public function status(string $entity): void
    {
        Auth::requireUser();
        $id = (int) input('id');
        if ($entity === 'orders') {
            OrderService::transition($id, input('status'));
        } elseif ($entity === 'cash') {
            DB::transaction(function () use ($id) {
                $s = DB::run(
                    "UPDATE orders SET payment_status='paid' WHERE id=? AND payment_method='cod' AND payment_status='unpaid' AND status='Delivered'",
                    [$id],
                );
                if (!$s->rowCount()) {
                    throw new ValidationException(
                        'Cash can be recorded only once on a delivered cash order.',
                    );
                }
                DB::audit('Cash collected for order #' . $id);
            });
            $entity = 'orders';
        } elseif ($entity === 'reservations') {
            $next = choice('status', ['Confirmed', 'Completed', 'Cancelled']);
            $r = DB::one('SELECT status FROM reservations WHERE id=?', [$id]);
            $allowed = [
                'Pending' => ['Confirmed', 'Cancelled'],
                'Confirmed' => ['Completed', 'Cancelled'],
            ];
            if (!$r || !in_array($next, $allowed[$r['status']] ?? [], true)) {
                throw new ValidationException('Invalid reservation transition.');
            }
            DB::run('UPDATE reservations SET status=? WHERE id=? AND status=?', [
                $next,
                $id,
                $r['status'],
            ]);
            DB::audit('Reservation #' . $id . ' → ' . $next);
        } elseif ($entity === 'messages') {
            DB::run('UPDATE messages SET status=? WHERE id=?', [
                choice('status', ['Read', 'Unread']),
                $id,
            ]);
        } else {
            throw new ValidationException('Unknown operation.');
        }
        flash('Updated.');
        redirect('/control-center/' . $entity . (!empty($_POST['detail']) ? '/' . $id : ''));
    }
    public function saveSettings(): void
    {
        Auth::requireUser(true);
        $groups=require ROOT.'/config/settings.php';$values=[];
        $group = is_string($_POST['settings_group'] ?? null) ? $_POST['settings_group'] : '';
        if ($group !== '') {
            if (!isset($groups[$group])) throw new ValidationException('Unknown settings page.');
            $groups = [$group => $groups[$group]];
        }
        $fields = array_merge(...array_values($groups));
        $translations = Translations::submitted('settings', $fields);
        $editingLanguage = in_array($_POST['content_locale'] ?? '', ['en', 'bn'], true) ? $_POST['content_locale'] : 'en';
        foreach ($translations[$editingLanguage] ?? [] as $key => $value) $_POST[str_replace('.', '_', $key)] = plain($value) !== '' ? $value : $translations[$editingLanguage === 'en' ? 'bn' : 'en'][$key];
        foreach($groups as $fields) foreach($fields as $key=>$type) {
            $name=str_replace('.','_',$key);
            $value=$type==='checkbox'?isset($_POST[$name]):input($name,20000,!in_array($type,['url','image']));
            if(in_array($type,['rich','rich-inline'])) $value=RichText::clean($value,$type==='rich-inline');
            if($type==='money') $value=$this->parseMoney($value);
            if($type==='number') { if(!ctype_digit($value)||(int)$value<1||(int)$value>100)throw new ValidationException('Guest limit must be 1–100.');$value=(int)$value; }
            if($type==='image') {$list=MediaService::validateImages($value,1);$value=$list[0]??'';}
            if($type==='url'&&$value!==''&&!preg_match('~^https://[^\s]+$~',$value))throw new ValidationException('Social links must use HTTPS.');
            if($type==='email'&&!filter_var($value,FILTER_VALIDATE_EMAIL))throw new ValidationException('Invalid email.');
            $values[$key]=$value;
        }
        DB::transaction(function()use($values,$translations){foreach($values as $key=>$value)Content::save($key,$value);Translations::save('settings',0,$translations);DB::audit('Website settings updated');});
        flash('Website settings saved.');redirect('/control-center/settings' . ($group !== '' ? '?' . http_build_query(['group' => $group]) : ''));
    }
    public function team(): void
    {
        $u = Auth::requireUser(true);
        $action = choice('action', ['create', 'update', 'delete']);
        $id = (int) ($_POST['id'] ?? 0);
        if ($action !== 'create' && $id === $u['id']) {
            throw new ValidationException(
                'You cannot change your own role or delete your account.',
            );
        }
        if ($action === 'create') {
            DB::insert('users', [
                'name' => input('name', 100),
                'email' => emailInput(),
                'password' => Auth::password(input('password', 200)),
                'role' => choice('role', ['owner', 'manager']),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($action === 'update') {
            DB::run('UPDATE users SET name=?,email=?,role=?,session_version=session_version+1 WHERE id=?', [input('name', 100), emailInput(), choice('role', ['owner', 'manager']), $id]);
        } else {
            DB::run('DELETE FROM users WHERE id=?', [$id]);
        }
        DB::audit('Team account ' . $action);
        flash('Team updated.');
        redirect('/control-center/team');
    }
    public function export(string $entity): void
    {
        Auth::requireUser();
        $sql = match ($entity) {
            'orders'
                => 'SELECT number,customer_name,email,phone,total,status,payment_method,payment_status,created_at FROM orders ORDER BY id DESC',
            'customers'
                => 'SELECT email,MAX(customer_name) name,MAX(phone) phone,COUNT(*) order_count FROM orders GROUP BY email',
            'reservations' => 'SELECT * FROM reservations ORDER BY id DESC',
            default => throw new ValidationException('Export unavailable.'),
        };
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $entity . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        $query = DB::run($sql);
        $first = true;
        while ($row = $query->fetch()) {
            if ($first) {
                fputcsv($out, array_keys($row), ',', '"', '');
                $first = false;
            }
            fputcsv(
                $out,
                array_map(
                    fn($v) => preg_match('/^[\s]*[=+@-]/', (string) $v) ? "'" . $v : $v,
                    array_values($row),
                ),
                ',',
                '"',
                '',
            );
        }
        fclose($out);
    }
    public function orders(): void
    {
        Auth::requireUser();
        $query = is_string($_GET['q'] ?? null) ? trim(substr($_GET['q'], 0, 100)) : '';
        $status = is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
        $type = is_string($_GET['type'] ?? null) ? $_GET['type'] : '';
        $where = []; $args = [];
        if ($query !== '') { $where[] = '(number LIKE ? OR customer_name LIKE ? OR phone LIKE ?)'; array_push($args, ...array_fill(0, 3, '%' . $query . '%')); }
        if (in_array($status, OrderService::STATUSES, true)) { $where[] = 'status=?'; $args[] = $status; } else $status = '';
        if (in_array($type, ['pickup', 'delivery'], true)) { $where[] = 'order_type=?'; $args[] = $type; } else $type = '';
        $sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $pageNumber = max(1, (int) ($_GET['page'] ?? 1));
        $rows = DB::all('SELECT * FROM orders' . $sql . ' ORDER BY id DESC LIMIT 25 OFFSET ' . (($pageNumber - 1) * 25), $args);
        $total = (int) DB::one('SELECT COUNT(*) n FROM orders' . $sql, $args)['n'];
        $counts = array_column(DB::all('SELECT status,COUNT(*) n FROM orders GROUP BY status'), 'n', 'status');
        view('admin/orders', compact('rows', 'total', 'pageNumber', 'query', 'status', 'type', 'counts') + ['admin' => true, 'section' => 'orders', 'title' => 'Orders']);
    }
    public function detail(string $entity, int $id): void
    {
        Auth::requireUser();
        if (!in_array($entity, ['orders', 'reservations', 'messages', 'payments'], true)) throw new ValidationException('Unknown operation.');
        $row = DB::one("SELECT * FROM $entity WHERE id=?", [$id]);
        if (!$row) { http_response_code(404); throw new ValidationException('Record not found.'); }
        $data = ['admin' => true, 'section' => $entity, 'title' => ucfirst($entity) . ' #' . $id, 'detail' => true, 'rows' => [$row], 'total' => 1, 'pageNumber' => 1];
        if ($entity === 'orders') {
            $data['o'] = $row;
            $data['items'] = DB::all('SELECT * FROM order_items WHERE order_id=?', [$id]);
            $data['events'] = OrderService::events($id);
            view('admin/order-detail', $data);
        } else view('admin/operations', $data);
    }
    private function parseMoney(string $v): int
    {
        if (!preg_match('/^(\d{1,7})(?:\.(\d{1,2}))?$/', $v, $m)) {
            throw new ValidationException('Enter an amount with at most two decimal places.');
        }
        return (int) $m[1] * 100 + (int) str_pad($m[2] ?? '', 2, '0');
    }
}
