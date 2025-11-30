<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\State\StateManager;

class DataGrid
{
    private StateManager $sm;
    private string $stateKey;
    private array $columns;
    private array $config;

    public function __construct(string $stateKey, array $columns, array $config = [])
    {
        $this->sm = StateManager::instance();
        $this->stateKey = $stateKey;
        $this->columns = $columns;
        $this->config = array_merge([
            'pageSize' => 10,
            'sortable' => true,
            'filterable' => true,
        ], $config);

        // 初始化状态
        $this->sm->set("{$stateKey}_all", []);        // 原始完整数据
        $this->sm->set("{$stateKey}_filtered", []);   // 过滤后数据
        $this->sm->set("{$stateKey}_paged", []);      // 当前页数据
        $this->sm->set("{$stateKey}_page", 1);        // 当前页码
        $this->sm->set("{$stateKey}_sortCol", null);  // 排序列索引
        $this->sm->set("{$stateKey}_sortDir", 'asc'); // 排序方向
        $this->sm->set("{$stateKey}_filter", '');     // 过滤关键词
    }

    /**
     * 设置数据源
     */
    public function setData(array $data): void
    {
        $this->sm->set("{$this->stateKey}_all", $data);
        $this->refresh();
    }

    /**
     * 应用过滤
     */
    public function applyFilter(string $keyword): void
    {
        $all = $this->sm->get("{$this->stateKey}_all", []);

        if (empty($keyword)) {
            $filtered = $all;
        } else {
            $filtered = array_filter($all, function($row) use ($keyword) {
                foreach ($row as $cell) {
                    if (stripos((string)$cell, $keyword) !== false) {
                        return true;
                    }
                }
                return false;
            });
            $filtered = array_values($filtered); // 重置索引
        }

        $this->sm->set("{$this->stateKey}_filtered", $filtered);
        $this->sm->set("{$this->stateKey}_filter", $keyword);
        $this->sm->set("{$this->stateKey}_page", 1); // 重置到第一页
        $this->applyPaging();
    }

    /**
     * 应用排序
     */
    public function applySort(int $columnIndex, string $direction = 'asc'): void
    {
        $filtered = $this->sm->get("{$this->stateKey}_filtered", []);

        usort($filtered, function($a, $b) use ($columnIndex, $direction) {
            $valA = $a[$columnIndex] ?? '';
            $valB = $b[$columnIndex] ?? '';

            // 尝试数值比较
            if (is_numeric($valA) && is_numeric($valB)) {
                $cmp = $valA <=> $valB;
            } else {
                $cmp = strcasecmp((string)$valA, (string)$valB);
            }

            return $direction === 'desc' ? -$cmp : $cmp;
        });

        $this->sm->set("{$this->stateKey}_filtered", $filtered);
        $this->sm->set("{$this->stateKey}_sortCol", $columnIndex);
        $this->sm->set("{$this->stateKey}_sortDir", $direction);
        $this->applyPaging();
    }

    /**
     * 切换排序方向
     */
    public function toggleSort(int $columnIndex): void
    {
        $currentCol = $this->sm->get("{$this->stateKey}_sortCol");
        $currentDir = $this->sm->get("{$this->stateKey}_sortDir", 'asc');

        if ($currentCol === $columnIndex) {
            // 同一列，切换方向
            $newDir = $currentDir === 'asc' ? 'desc' : 'asc';
        } else {
            // 不同列，默认升序
            $newDir = 'asc';
        }

        $this->applySort($columnIndex, $newDir);
    }

    /**
     * 应用分页
     */
    public function applyPaging(): void
    {
        $filtered = $this->sm->get("{$this->stateKey}_filtered", []);
        $page = max(1, $this->sm->get("{$this->stateKey}_page", 1));
        $pageSize = $this->config['pageSize'];

        $total = count($filtered);
        $totalPages = max(1, (int)ceil($total / $pageSize));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $start = ($page - 1) * $pageSize;
        $paged = array_slice($filtered, $start, $pageSize);

        $this->sm->set("{$this->stateKey}_paged", $paged);
        $this->sm->set("{$this->stateKey}_page", $page);
        $this->sm->set("{$this->stateKey}_pageInfo", "Page {$page} / {$totalPages} (Total: {$total})");
    }

    /**
     * 跳转到指定页
     */
    public function gotoPage(int $page): void
    {
        $this->sm->set("{$this->stateKey}_page", $page);
        $this->applyPaging();
    }

    /**
     * 下一页
     */
    public function nextPage(): void
    {
        $current = $this->sm->get("{$this->stateKey}_page", 1);
        $this->gotoPage($current + 1);
    }

    /**
     * 上一页
     */
    public function prevPage(): void
    {
        $current = $this->sm->get("{$this->stateKey}_page", 1);
        $this->gotoPage($current - 1);
    }

    /**
     * 刷新（重新计算过滤、排序、分页）
     */
    public function refresh(): void
    {
        $filter = $this->sm->get("{$this->stateKey}_filter", '');
        $this->applyFilter($filter);

        $sortCol = $this->sm->get("{$this->stateKey}_sortCol");
        if ($sortCol !== null) {
            $sortDir = $this->sm->get("{$this->stateKey}_sortDir", 'asc');
            $this->applySort($sortCol, $sortDir);
        }
    }

    /**
     * 获取事件处理器（用于绑定到 HtmlRenderer）
     */
    public function getHandlers(): array
    {
        return [
            'filter' => fn() => $this->applyFilter(
                $this->sm->get("{$this->stateKey}_filterInput", '')
            ),
            'clearFilter' => function() {
                $this->sm->set("{$this->stateKey}_filterInput", '');
                $this->applyFilter('');
            },
            'sort' => function() {
                $colIndex = $this->sm->get("{$this->stateKey}_sortColumnIndex", 0);
                $this->toggleSort($colIndex);
            },
            'nextPage' => fn() => $this->nextPage(),
            'prevPage' => fn() => $this->prevPage(),
            'refresh' => fn() => $this->refresh(),
        ];
    }
}