<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

App::init();

$window = Builder::window()
    ->title('Complex Table Demo')
    ->size(800,600)
    ->margined(true)
    ->onClosing(function($w){ App::quit(); return 1; });

// sample data with checkboxes and images omitted for safety
$data = [];
for ($i=0;$i<50;$i++){
    $data[] = [
        'id' => $i+1,
        'name' => "User {$i}",
        'email' => "user{$i}@example.com",
        'active' => $i % 2,
        'salary' => rand(30000,120000)
    ];
}

$table = Builder::table()
    ->headers(['ID','Name','Email','Active','Salary'])
    ->data(array_map(function($r){ return array_values($r); }, $data))
    ->options(['sortable'=>true,'multiSelect'=>true,'headerVisible'=>true]);

$window->contains([
    Builder::vbox()->padded(true)->contains([
        Builder::label()->text('Complex Table Demo'),
        $table
    ])
]);

$app = $window;
// build and show
$app->build();

// run main loop using libui App
// Build uses Control::show in Builder logic; assume build shows window
App::main();

