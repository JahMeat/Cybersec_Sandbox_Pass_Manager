<?php
if (!isset($_SESSION['bf_guard'])) {
    $_SESSION['bf_guard'] = [];
}
if (!function_exists('bf_key')) {
    function bf_key($u) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return hash('sha256', strtolower(trim((string)$u)).'|'.$ip);
    }
}
if (!function_exists('bf_is_locked')) {
    function bf_is_locked($u) {
        $k = bf_key($u);
        $now = time();
        $slot = $_SESSION['bf_guard'][$k] ?? ['fails'=>[], 'lock_until'=>0];
        return $slot['lock_until'] > $now ? ($slot['lock_until'] - $now) : 0;
    }
}
if (!function_exists('bf_record_fail')) {
    function bf_record_fail($u) {
        $k = bf_key($u);
        $now = time();
        $slot = $_SESSION['bf_guard'][$k] ?? ['fails'=>[], 'lock_until'=>0];
        // keep last 5 minutes
        $window = 300;
        $slot['fails'] = array_values(array_filter($slot['fails'], fn($t)=> $t >= $now - $window));
        $slot['fails'][] = $now;
        // if >=3 fails, lock for 1 minutes
        if (count($slot['fails']) >= 3) {
            $slot['lock_until'] = $now + 60;
            $slot['fails'] = []; // reset fails on lock
        }
        $_SESSION['bf_guard'][$k] = $slot;
    }
}
if (!function_exists('bf_record_success')) {
    function bf_record_success($u) {
        $k = bf_key($u);
        unset($_SESSION['bf_guard'][$k]);
    }
}
?>
