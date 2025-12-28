<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\Builder;

// create a temporary ui file in repo
$file = __DIR__ . '/canvas_test.ui.html';
$html = <<<HTML
<window title="Canvas Test">
  <vbox>
    <label>Canvas below</label>
    <canvas id="mycanvas" width="320" height="180" ondraw="drawHandler"></canvas>
  </vbox>
</window>
HTML;
file_put_contents($file, $html);

$handlers = [
    'drawHandler' => function($canvasBuilder, $drawCtx, $params) {
        echo "drawHandler invoked with params: ";
        if (is_array($params)) {
            echo json_encode([ 'AreaWidth' => $params['AreaWidth'] ?? null, 'AreaHeight' => $params['AreaHeight'] ?? null ]);
        } else {
            var_dump($params);
        }
        echo "\n";

        if ($drawCtx) {
            // draw a simple rectangle to test the API (safe fallback)
            $drawCtx->fillRect(10, 10, 100, 40, [200, 100, 50, 1]);
        }
    }
];

try {
    $root = HtmlRenderer::render($file, $handlers);
    echo "Render returned: " . get_class($root) . "\n";

    // recursive traversal to find canvas
    $finder = function($node, $depth = 0) use (&$finder) {
        $indent = str_repeat('  ', $depth);
        echo $indent . "Node: " . get_class($node) . "\n";
        foreach ($node->getChildren() as $child) {
            // print child's class
            echo $indent . "  Child: " . get_class($child) . "\n";
            if (get_class($child) === 'Kingbes\\Libui\\View\\Components\\CanvasBuilder') {
                echo $indent . "    Found CanvasBuilder. Config: ";
                var_dump($child->getConfig('width'), $child->getConfig('height'), $child->getConfig('onDraw'));
            }
            // recurse
            $finder($child, $depth + 2);
        }
    };

    $finder($root);
} catch (Throwable $e) {
    echo "Render error: " . $e->getMessage() . "\n";
}

// cleanup
@unlink($file);

