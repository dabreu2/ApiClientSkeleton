# ApiClientSkeleton
Basic API Client Skeleton


Usage
```
// Initialization
\CFG\Api::init(
    'CLIENT_ID',
    'SERVICE_ID',
    'https://domain.com/api/v1',
    [
        'debug' => true,
        'cache' => new \CFG\Cache\FileCache('../cache', 3600)
    ]
);


// Object driven request
$testInfo = (new \CFG\Objects\Test())
    ->get();

dump($testInfo);
```
