<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use App\Services\UserPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    /**
     * Display user preferences page
     */
    public function index()
    {
        return view('preferences.index');
    }

    /**
     * Get all user preferences
     */
    public function getPreferences()
    {
        $user = Auth::user();
        $preferences = UserPreferenceService::getAll($user);
        
        return response()->json(['preferences' => $preferences]);
    }

    /**
     * Get preferences by category
     */
    public function getByCategory(string $category)
    {
        $user = Auth::user();
        $preferences = UserPreferenceService::getByCategory($user, $category);
        
        return response()->json(['preferences' => $preferences]);
    }

    /**
     * Update user preferences
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $preferences = $request->input('preferences', []);
        
        // Validate each preference
        foreach ($preferences as $key => $value) {
            if (!UserPreferenceService::validatePreference($key, $value)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid value for preference: {$key}"
                ], 422);
            }
        }
        
        // Update preferences
        UserPreferenceService::setMultiple($user, $preferences);
        
        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التفضيلات بنجاح'
        ]);
    }

    /**
     * Update single preference
     */
    public function updateSingle(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);
        
        $user = Auth::user();
        $key = $request->key;
        $value = $request->value;
        
        // Validate preference
        if (!UserPreferenceService::validatePreference($key, $value)) {
            return response()->json([
                'success' => false,
                'message' => "Invalid value for preference: {$key}"
            ], 422);
        }
        
        UserPreferenceService::set($user, $key, $value);
        
        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التفضيل بنجاح'
        ]);
    }

    /**
     * Reset preference to default
     */
    public function reset(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);
        
        $user = Auth::user();
        UserPreferenceService::reset($user, $request->key);
        
        return response()->json([
            'success' => true,
            'message' => 'تم إعادة تعيين التفضيل إلى القيمة الافتراضية'
        ]);
    }

    /**
     * Reset all preferences to defaults
     */
    public function resetAll()
    {
        $user = Auth::user();
        UserPreferenceService::resetAll($user);
        
        return response()->json([
            'success' => true,
            'message' => 'تم إعادة تعيين جميع التفضيلات إلى القيم الافتراضية'
        ]);
    }

    /**
     * Toggle sidebar collapsed state
     */
    public function toggleSidebar()
    {
        $user = Auth::user();
        $collapsed = UserPreferenceService::toggleSidebar($user);
        
        return response()->json([
            'success' => true,
            'collapsed' => $collapsed
        ]);
    }

    /**
     * Set theme mode
     */
    public function setTheme(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:light,dark,auto',
            'color' => 'nullable|in:blue,green,purple,red,orange,teal',
        ]);
        
        $user = Auth::user();
        
        UserPreferenceService::setThemeMode($user, $request->mode);
        
        if ($request->has('color')) {
            UserPreferenceService::setThemeColor($user, $request->color);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث إعدادات المظهر'
        ]);
    }

    /**
     * Set language preferences
     */
    public function setLanguage(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:ar,en',
            'timezone' => 'nullable|string',
        ]);
        
        $user = Auth::user();
        
        UserPreferenceService::setLanguage($user, $request->locale);
        
        if ($request->has('timezone')) {
            UserPreferenceService::setTimezone($user, $request->timezone);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث إعدادات اللغة'
        ]);
    }

    /**
     * Set dashboard widgets
     */
    public function setDashboardWidgets(Request $request)
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*' => 'string',
        ]);
        
        $user = Auth::user();
        UserPreferenceService::setDashboardWidgets($user, $request->widgets);
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث ودجات لوحة التحكم'
        ]);
    }

    /**
     * Get available options for preferences
     */
    public function getOptions()
    {
        $options = [
            'themes' => UserPreference::getAvailableThemes(),
            'colors' => UserPreference::getAvailableColors(),
            'languages' => UserPreference::getAvailableLanguages(),
            'timezones' => UserPreference::getAvailableTimezones(),
            'sidebar_positions' => UserPreferenceService::getAvailableOptions('layout.sidebar_position'),
            'items_per_page' => UserPreferenceService::getAvailableOptions('general.items_per_page'),
        ];
        
        return response()->json(['options' => $options]);
    }

    /**
     * Export user preferences
     */
    public function export()
    {
        $user = Auth::user();
        $export = UserPreferenceService::export($user);
        
        return response()->json($export);
    }

    /**
     * Import user preferences
     */
    public function import(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
        ]);
        
        $user = Auth::user();
        UserPreferenceService::import($user, $request->preferences);
        
        return response()->json([
            'success' => true,
            'message' => 'تم استيراد التفضيلات بنجاح'
        ]);
    }

    /**
     * Get preference configuration
     */
    public function getConfig(string $key)
    {
        $config = UserPreferenceService::getPreferenceConfig($key);
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Preference not found'
            ], 404);
        }
        
        return response()->json([
            'config' => $config,
            'options' => UserPreferenceService::getAvailableOptions($key)
        ]);
    }

    /**
     * Get theme preferences for CSS generation
     */
    public function getThemeCSS()
    {
        $user = Auth::user();
        $theme = UserPreferenceService::getThemePreferences($user);
        
        $css = $this->generateThemeCSS($theme);
        
        return response($css, 200, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Generate CSS from theme preferences
     */
    private function generateThemeCSS(array $theme): string
    {
        $mode = $theme['theme.mode'] ?? 'light';
        $color = $theme['theme.color'] ?? 'blue';
        $sidebarCollapsed = $theme['theme.sidebar_collapsed'] ?? false;
        
        $css = ":root {\n";
        
        // Color variables
        $colors = [
            'blue' => ['primary' => '#3B82F6', 'secondary' => '#1E40AF'],
            'green' => ['primary' => '#10B981', 'secondary' => '#047857'],
            'purple' => ['primary' => '#8B5CF6', 'secondary' => '#5B21B6'],
            'red' => ['primary' => '#EF4444', 'secondary' => '#B91C1C'],
            'orange' => ['primary' => '#F97316', 'secondary' => '#C2410C'],
            'teal' => ['primary' => '#14B8A6', 'secondary' => '#0F766E'],
        ];
        
        if (isset($colors[$color])) {
            $css .= "  --color-primary: {$colors[$color]['primary']};\n";
            $css .= "  --color-secondary: {$colors[$color]['secondary']};\n";
        }
        
        // Theme mode variables
        if ($mode === 'dark') {
            $css .= "  --bg-primary: #1F2937;\n";
            $css .= "  --bg-secondary: #374151;\n";
            $css .= "  --text-primary: #F9FAFB;\n";
            $css .= "  --text-secondary: #D1D5DB;\n";
        } else {
            $css .= "  --bg-primary: #FFFFFF;\n";
            $css .= "  --bg-secondary: #F9FAFB;\n";
            $css .= "  --text-primary: #111827;\n";
            $css .= "  --text-secondary: #6B7280;\n";
        }
        
        $css .= "}\n\n";
        
        // Sidebar state
        if ($sidebarCollapsed) {
            $css .= ".sidebar { width: 4rem; }\n";
            $css .= ".main-content { margin-right: 4rem; }\n";
        }
        
        return $css;
    }
}
