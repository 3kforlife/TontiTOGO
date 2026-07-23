<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttpsForSwagger
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Forcer HTTPS dans les réponses Swagger
        if (str_contains($request->path(), 'api/documentation') || 
            str_contains($request->path(), 'docs')) {
            $content = $response->getContent();
            if ($content) {
                $content = str_replace('http://', 'https://', $content);
                $response->setContent($content);
            }
        }
        
        return $response;
    }
}