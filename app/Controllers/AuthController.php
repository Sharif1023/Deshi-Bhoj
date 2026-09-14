<?php
namespace App\Controllers;
use App\Core\{DB, Auth, ValidationException};
final class AuthController
{
    public function loginPage(): void
    {
        if (user()) {
            redirect('/control-center');
        }
        view('auth/login', ['title' => 'Staff sign in']);
    }
    public function login(): void
    {
        Auth::throttle('login:' . ($_SERVER['REMOTE_ADDR'] ?? ''));
        $email = emailInput();
        Auth::throttle('account:' . $email, 15);
        $u = DB::one('SELECT * FROM users WHERE email=?', [$email]);
        $password = input('password', 200);
        if (!$u || !password_verify($password, $u['password'])) {
            throw new ValidationException('Email or password is incorrect.');
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['version'] = $u['session_version'];
        $_SESSION['expires'] = time() + 43200;
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        DB::audit('Staff signed in');
        redirect('/control-center');
    }
    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        redirect('/login');
    }
    public function password(): void
    {
        $u = Auth::requireUser();
        $record = DB::one('SELECT password FROM users WHERE id=?', [$u['id']]);
        if (!password_verify(input('current_password', 200), $record['password'])) {
            throw new ValidationException('Current password is incorrect.');
        }
        DB::run('UPDATE users SET password=?,session_version=session_version+1 WHERE id=?', [
            Auth::password(input('password', 200)),
            $u['id'],
        ]);
        $_SESSION = [];
        session_regenerate_id(true);
        flash('Password changed. Sign in again.');
        redirect('/login');
    }
    public function forgotPage(): void
    {
        view('auth/forgot', ['title' => 'Reset password']);
    }
    public function forgot(): void
    {
        Auth::throttle('reset:' . ($_SERVER['REMOTE_ADDR'] ?? ''), 5);
        $email = emailInput();
        $u = DB::one('SELECT id FROM users WHERE email=?', [$email]);
        if ($u) {
            $token = bin2hex(random_bytes(32));
            DB::transaction(function () use ($email, $token) {
                DB::run('DELETE FROM password_resets WHERE email=?', [$email]);
                DB::insert('password_resets', [
                    'email' => $email,
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => time() + 900,
                ]);
            });
            $link =
                rtrim(env('APP_URL'), '/') .
                '/reset-password?token=' .
                $token .
                '&email=' .
                rawurlencode($email);
            $from = env('MAIL_FROM', 'hello@example.com');
            if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid mail sender configuration');
            }
            if (
                !@mail(
                    $email,
                    'Reset your KOJI password',
                    "Reset your password within 15 minutes:\n" . $link,
                    ['From' => $from],
                )
            ) {
                error_log('Password reset mail delivery failed. Configure PHP mail transport.');
            }
        }
        flash(
            'If the account exists, a reset link will be sent. If email is unavailable, contact the server administrator.',
        );
        redirect('/forgot-password');
    }
    public function resetPage(): void
    {
        view('auth/reset', ['title' => 'Choose a new password']);
    }
    public function reset(): void
    {
        $email = emailInput();
        $hash = hash('sha256', input('token', 64));
        $password = Auth::password(input('password', 200));
        DB::transaction(function () use ($email, $hash, $password) {
            $deleted = DB::run(
                'DELETE FROM password_resets WHERE email=? AND token_hash=? AND expires_at>?',
                [$email, $hash, time()],
            );
            if ($deleted->rowCount() !== 1) {
                throw new ValidationException('Reset link is invalid or expired.');
            }
            DB::run('UPDATE users SET password=?,session_version=session_version+1 WHERE email=?', [
                $password,
                $email,
            ]);
        });
        flash('Password reset. You may sign in.');
        redirect('/login');
    }
}
