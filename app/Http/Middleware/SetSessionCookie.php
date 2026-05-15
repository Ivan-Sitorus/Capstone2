<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $name = null;
        
        if ($request->is('admin/*')) {
            $name = 'admin_session';
        } elseif ($request->is('kasir/*')) {
            $name = 'kasir_session';
        } elseif ($request->is('dapur/*')) {
            $name = 'dapur_session';
        }
        
        if ($name) {
            Config::set('session.cookie', $name);
            Session::setName($name);
        }
        
        return $next($request);
    }
}
