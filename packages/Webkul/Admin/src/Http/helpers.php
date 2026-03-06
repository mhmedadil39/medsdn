<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('resolveMenuName')) {
    /**
     * Resolve menu display name with custom name support
     * 
     * @param  object  $menuItem
     * @return string
     */
    function resolveMenuName($menuItem): string
    {
        try {
            // Check for custom name in configuration
            $customName = core()->getConfigData(
                'general.settings.menu.' . $menuItem->getKey()
            );
            
            // Return custom name if exists, otherwise default
            return $customName ?? $menuItem->getName();
            
        } catch (\Exception $e) {
            // Log error and fallback to default name
            Log::warning('Failed to resolve custom menu name', [
                'menu_key' => $menuItem->getKey(),
                'error' => $e->getMessage()
            ]);
            
            return $menuItem->getName();
        }
    }
}

if (! function_exists('filterMenuByPermissions')) {
    /**
     * Filter menu items based on user permissions
     * 
     * @param  array  $menuItems
     * @return array
     */
    function filterMenuByPermissions(array $menuItems): array
    {
        return array_filter($menuItems, function ($menuItem) {
            try {
                // Check if user has permission
                if (! bouncer()->hasPermission($menuItem->getPermission())) {
                    return false;
                }
                
                // Recursively filter children
                if ($menuItem->hasChildren()) {
                    $filteredChildren = filterMenuByPermissions(
                        $menuItem->getChildren()
                    );
                    $menuItem->setChildren($filteredChildren);
                    
                    // Remove parent if all children are filtered out
                    return count($filteredChildren) > 0;
                }
                
                return true;
                
            } catch (\Exception $e) {
                // Fail secure: hide menu on error
                Log::error('ACL check failed for menu item', [
                    'menu_key' => $menuItem->getKey(),
                    'error' => $e->getMessage()
                ]);
                
                return false;
            }
        });
    }
}
