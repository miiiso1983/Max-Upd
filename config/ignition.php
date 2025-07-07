<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ignition Settings
    |--------------------------------------------------------------------------
    |
    | Ignition is a beautiful error page for Laravel applications running
    | in local development. Here you may configure if it should be enabled
    | and any settings that should be applied to the error page.
    |
    */

    'enabled' => env('IGNITION_ENABLED', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Ignition Editor
    |--------------------------------------------------------------------------
    |
    | Choose your preferred editor to use when clicking file paths in Ignition.
    | Supported: "phpstorm", "vscode", "vscode-insiders", "vscode-remote",
    |            "vscode-tunnel", "sublime", "atom", "nova", "macvim", "emacs",
    |            "idea", "netbeans"
    |
    */

    'editor' => env('IGNITION_EDITOR', 'vscode'),

    /*
    |--------------------------------------------------------------------------
    | Ignition Theme
    |--------------------------------------------------------------------------
    |
    | Here you may specify which theme Ignition should use.
    |
    | Supported: "light", "dark", "auto"
    |
    */

    'theme' => env('IGNITION_THEME', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Sharing
    |--------------------------------------------------------------------------
    |
    | You can share local errors with colleagues or others around the world.
    | Sharing is completely optional, anonymous, and secure.
    |
    */

    'sharing' => [
        'enabled' => env('IGNITION_SHARING_ENABLED', false),
        'anonymize_ips' => env('IGNITION_ANONYMIZE_IPS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Register Ignition commands
    |--------------------------------------------------------------------------
    |
    | Ignition comes with an additional make command that lets you create
    | new solution classes more easily. To keep your application clean
    | you can choose to disable registering these commands.
    |
    */

    'register_commands' => env('IGNITION_REGISTER_COMMANDS', app()->environment('local')),

    /*
    |--------------------------------------------------------------------------
    | Ignored Solution Providers
    |--------------------------------------------------------------------------
    |
    | You may specify a list of solution providers (as fully qualified class
    | names) that shouldn't be loaded. Ignition will ignore these classes
    | and possible solutions provided by them will never be displayed.
    |
    */

    'ignored_solution_providers' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Runnable Solutions
    |--------------------------------------------------------------------------
    |
    | Some solutions that Ignition displays are runnable and can perform
    | various tasks. Runnable solutions are enabled when your app has
    | debug mode enabled. You may also fully disable this feature.
    |
    */

    'enable_runnable_solutions' => env('IGNITION_ENABLE_RUNNABLE_SOLUTIONS', null),

    /*
    |--------------------------------------------------------------------------
    | Remote Path Mapping
    |--------------------------------------------------------------------------
    |
    | If you are using a remote development server, like Laravel Homestead,
    | Docker, or even a remote VPS, it will be necessary to specify your
    | path mapping to get remote file links working on your local side.
    |
    | Leaving one, or both of these, empty or null will not trigger the remote
    | URL changes and Ignition will treat your editor links as local files.
    |
    | "remote_sites_path" is an absolute base path for your sites or projects
    | in Homestead, Vagrant, Docker, or another remote development server.
    |
    | Example value: "/home/vagrant/Code"
    |
    | "local_sites_path" is an absolute base path for your sites or projects
    | on your local computer where your IDE or code editor is running on.
    |
    | Example values: "/Users/<name>/Code", "C:\Users\<name>\Documents\Code"
    |
    */

    'remote_sites_path' => env('IGNITION_REMOTE_SITES_PATH', ''),
    'local_sites_path' => env('IGNITION_LOCAL_SITES_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Housekeeping Endpoint Prefix
    |--------------------------------------------------------------------------
    |
    | Ignition registers a couple of routes when it is enabled. Below you may
    | specify a route prefix that will be used to host all internal links.
    |
    */

    'housekeeping_endpoint_prefix' => '_ignition',

    /*
    |--------------------------------------------------------------------------
    | Settings File Path
    |--------------------------------------------------------------------------
    |
    | You may specify the path to the Ignition settings file. This is the file
    | that Ignition will use to store user preferences like the theme.
    |
    */

    'settings_file_path' => '',

    /*
    |--------------------------------------------------------------------------
    | Recorders
    |--------------------------------------------------------------------------
    |
    | Ignition will record certain information about your application during
    | the request. Here you can choose which recorders should be enabled.
    |
    */

    'recorders' => [
        Spatie\LaravelIgnition\Recorders\DumpRecorder\DumpRecorder::class => [
            'enabled' => env('IGNITION_RECORD_DUMPS', true),
            'max_dumps' => env('IGNITION_RECORD_DUMPS_MAX_DUMPS', 200),
        ],

        Spatie\LaravelIgnition\Recorders\JobRecorder\JobRecorder::class => [
            'enabled' => env('IGNITION_RECORD_JOBS', true),
            'max_jobs' => env('IGNITION_RECORD_JOBS_MAX_JOBS', 50),
        ],

        Spatie\LaravelIgnition\Recorders\LogRecorder\LogRecorder::class => [
            'enabled' => env('IGNITION_RECORD_LOGS', true),
            'max_logs' => env('IGNITION_RECORD_LOGS_MAX_LOGS', 200),
        ],

        Spatie\LaravelIgnition\Recorders\QueryRecorder\QueryRecorder::class => [
            'enabled' => env('IGNITION_RECORD_QUERIES', true),
            'max_queries' => env('IGNITION_RECORD_QUERIES_MAX_QUERIES', 200),
            'report_slow_queries' => env('IGNITION_RECORD_QUERIES_REPORT_SLOW_QUERIES', true),
            'slow_query_threshold' => env('IGNITION_RECORD_QUERIES_SLOW_QUERY_THRESHOLD', 500),
            'report_bindings' => env('IGNITION_RECORD_QUERIES_REPORT_BINDINGS', true),
        ],

        Spatie\LaravelIgnition\Recorders\RequestRecorder\RequestRecorder::class => [
            'enabled' => env('IGNITION_RECORD_REQUESTS', true),
            'max_requests' => env('IGNITION_RECORD_REQUESTS_MAX_REQUESTS', 10),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Open AI Key
    |--------------------------------------------------------------------------
    |
    | Ignition comes with AI powered solutions. Set your Open AI key here.
    |
    */

    'open_ai_key' => env('IGNITION_OPEN_AI_KEY'),

    /*
    |--------------------------------------------------------------------------
    | With Stack Trace
    |--------------------------------------------------------------------------
    |
    | When this setting is true, the stack trace will be included in the
    | error details. This can be helpful for debugging, but you may want
    | to disable it in production for security reasons.
    |
    */

    'with_stack_trace' => env('IGNITION_WITH_STACK_TRACE', true),

    /*
    |--------------------------------------------------------------------------
    | Hide Solutions
    |--------------------------------------------------------------------------
    |
    | When this setting is true, solutions will not be displayed in Ignition.
    | This can be useful if you want to use Ignition only for error reporting.
    |
    */

    'hide_solutions' => env('IGNITION_HIDE_SOLUTIONS', false),

];
