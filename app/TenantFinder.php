<?php

namespace App;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder as BaseTenantFinder;

class TenantFinder extends BaseTenantFinder
{

    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();
        
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // For local development, handle localhost with subdomain
        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            // Extract subdomain from localhost
            $parts = explode('.', $host);
            if (count($parts) > 1 && $parts[0] !== 'www') {
                $subdomain = $parts[0];
                return Tenant::where('domain', $subdomain)->first();
            }

            // For development, also check if tenant parameter is passed
            if ($request->has('tenant')) {
                return Tenant::where('domain', $request->get('tenant'))->first();
            }
            
            return null;
        }
        
        // For production, handle subdomains
        $parts = explode('.', $host);
        
        // If it's a subdomain (more than 2 parts), get the subdomain
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            return Tenant::where('domain', $subdomain)->first();
        }

        // If it's a custom domain, find by full domain
        return Tenant::where('domain', $host)->first();
    }
}
