
<?php

return [
    'github_username' => env('GITHUB_USERNAME', 'your-github-username'),
    'github_token' => env('GITHUB_TOKEN', ''), // For private repos
    'cache_duration' => env('REPO_CACHE_DURATION', 3600), // Cache duration in seconds
];
