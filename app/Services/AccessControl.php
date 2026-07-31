<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Emp;

/**
 * ศูนย์รวมตรรกะสิทธิ์การใช้งาน (RBAC ระดับเมนู)
 *
 * - admin (ตาราง users / guard web) = เข้าถึงได้ทุกเมนู
 * - พนักงาน (ตาราง emp / guard emp) = เข้าถึงได้เฉพาะเมนูตาม role ของตน
 *
 * สิทธิ์เก็บเป็น "menu_key" (คีย์ใน config/menu.php) ส่วนการบล็อก route
 * เทียบด้วย "namespace" ของ route name (ตัด segment สุดท้ายออก เช่น employee.datatable → employee)
 */
class AccessControl
{
    private static ?array $allowedKeysCache = null;

    /** บัญชีที่ login อยู่ (User หรือ Emp) */
    public static function currentAccount()
    {
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }
        if (Auth::guard('emp')->check()) {
            return Auth::guard('emp')->user();
        }
        return null;
    }

    /** เป็น admin (login ผ่านตาราง users) หรือไม่ */
    public static function isAdmin(): bool
    {
        return Auth::guard('web')->check();
    }

    /** menu_key ทั้งหมดที่เลือกเป็นสิทธิ์ได้ (ข้าม header) — ไล่ sub_menu ทุกระดับ */
    public static function allMenuKeys(): array
    {
        $keys = [];
        foreach (config('menu') as $key => $menu) {
            if (isset($menu['type']) && $menu['type'] === 'header') {
                continue;
            }
            $keys[] = $key;
            self::collectSubMenuKeys($menu, $keys);
        }
        return $keys;
    }

    /** เก็บ key ของ sub_menu แบบ recursive (รองรับเมนูซ้อนหลายชั้น) */
    private static function collectSubMenuKeys(array $menu, array &$keys): void
    {
        if (!isset($menu['sub_menu'])) {
            return;
        }
        foreach ($menu['sub_menu'] as $subKey => $sub) {
            $keys[] = $subKey;
            self::collectSubMenuKeys($sub, $keys);
        }
    }

    /** menu_key ที่บัญชีปัจจุบันเข้าถึงได้ */
    public static function allowedKeys(): array
    {
        if (self::$allowedKeysCache !== null) {
            return self::$allowedKeysCache;
        }

        if (self::isAdmin()) {
            return self::$allowedKeysCache = self::allMenuKeys();
        }

        $account = self::currentAccount();
        if ($account instanceof Emp && $account->role_id && $account->role) {
            return self::$allowedKeysCache = $account->role->menuKeys();
        }

        return self::$allowedKeysCache = [];
    }

    public static function keyAllowed(string $key): bool
    {
        return in_array($key, self::allowedKeys(), true);
    }

    /** ใช้กับ sidebar: เมนูนี้ควรแสดงไหม */
    public static function menuVisible(string $key): bool
    {
        return self::isAdmin() || self::keyAllowed($key);
    }

    /** namespace ของ route name (ตัด segment สุดท้าย) — ไม่มีจุด = route ที่ไม่คุมสิทธิ์ */
    public static function routeNamespace(?string $name): string
    {
        if (!$name) {
            return '';
        }
        $pos = strrpos($name, '.');
        return $pos === false ? '' : substr($name, 0, $pos);
    }

    /** map: menu_key => route namespace — ไล่ sub_menu ทุกระดับ */
    public static function menuNamespaceByKey(): array
    {
        $map = [];
        foreach (config('menu') as $key => $menu) {
            if (isset($menu['type']) && $menu['type'] === 'header') {
                continue;
            }
            self::collectMenuNamespace($key, $menu, $map);
        }
        return $map;
    }

    /** เก็บ namespace ของ key + sub_menu แบบ recursive */
    private static function collectMenuNamespace(string $key, array $menu, array &$map): void
    {
        if (isset($menu['route_name'])) {
            $map[$key] = self::routeNamespace($menu['route_name']);
        }
        if (isset($menu['sub_menu'])) {
            foreach ($menu['sub_menu'] as $subKey => $sub) {
                self::collectMenuNamespace($subKey, $sub, $map);
            }
        }
    }

    /** namespace ทั้งหมดที่ผูกกับเมนู (route ที่อยู่ในขอบเขตการคุมสิทธิ์) */
    public static function allMenuNamespaces(): array
    {
        return array_values(array_unique(array_filter(self::menuNamespaceByKey())));
    }

    /** namespace ที่บัญชีปัจจุบันเข้าถึงได้ */
    public static function allowedNamespaces(): array
    {
        $map = self::menuNamespaceByKey();
        $ns  = [];
        foreach (self::allowedKeys() as $key) {
            if (!empty($map[$key])) {
                $ns[] = $map[$key];
            }
        }
        return array_values(array_unique($ns));
    }

    /**
     * เข้าถึง route นี้ได้ไหม
     * - admin = ได้เสมอ
     * - route ที่ไม่มี namespace หรือไม่ผูกกับเมนู = ปล่อยผ่าน (logout, home, legacy ฯลฯ)
     * - route ที่ผูกกับเมนู = ต้องอยู่ใน namespace ที่ได้รับสิทธิ์
     */
    public static function canAccessRoute(?string $routeName): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        $ns = self::routeNamespace($routeName);
        if ($ns === '') {
            return true;
        }
        if (!in_array($ns, self::allMenuNamespaces(), true)) {
            return true;
        }

        return in_array($ns, self::allowedNamespaces(), true);
    }

    /** URL หน้าแรกหลัง login ตามสิทธิ์ */
    public static function homeUrl(): string
    {
        if (self::isAdmin()) {
            return route('production.planning.index');
        }

        foreach (config('menu') as $key => $menu) {
            if (isset($menu['type']) && $menu['type'] === 'header') {
                continue;
            }
            if (isset($menu['route_name']) && self::keyAllowed($key)) {
                return route($menu['route_name']);
            }
            if (isset($menu['sub_menu'])) {
                foreach ($menu['sub_menu'] as $subKey => $sub) {
                    if (isset($sub['route_name']) && self::keyAllowed($subKey)) {
                        return route($sub['route_name']);
                    }
                }
            }
        }

        return route('no-access');
    }
}
