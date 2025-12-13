# libuiBuilder 测试运行脚本 (PowerShell 版本)

Write-Host "🧪 libuiBuilder 测试套件" -ForegroundColor Green
Write-Host "====================" -ForegroundColor Green

# 检查 Pest 是否可用
if (!(Test-Path "./vendor/bin/pest")) {
    Write-Host "❌ Pest 未找到，请先运行 composer install" -ForegroundColor Red
    exit 1
}

# 显示菜单
function Show-Menu {
    Write-Host ""
    Write-Host "请选择要运行的测试：" -ForegroundColor Cyan
    Write-Host "1) 基础测试 (Basic, StateManager, HtmlRenderer)" -ForegroundColor Yellow
    Write-Host "2) 完整测试套件" -ForegroundColor Yellow
    Write-Host "3) 只运行基础测试" -ForegroundColor Yellow
    Write-Host "4) 只运行 StateManager 测试" -ForegroundColor Yellow
    Write-Host "5) 只运行 HtmlRenderer 测试" -ForegroundColor Yellow
    Write-Host "6) 运行 Builder 组件测试" -ForegroundColor Yellow
    Write-Host "7) 运行 Helper 函数测试" -ForegroundColor Yellow
    Write-Host "8) 运行 TableBuilder 测试" -ForegroundColor Yellow
    Write-Host "9) 运行 ComponentRef 测试" -ForegroundColor Yellow
    Write-Host "10) 显示测试覆盖率" -ForegroundColor Yellow
    Write-Host "11) 生成 HTML 覆盖率报告" -ForegroundColor Yellow
    Write-Host "12) 列出所有测试用例" -ForegroundColor Yellow
    Write-Host "0) 退出" -ForegroundColor Red
    Write-Host ""
}

# 运行基础测试
function Run-BasicTests {
    Write-Host "🔧 运行基础测试..." -ForegroundColor Green
    & ./vendor/bin/pest tests/BasicTest.php tests/StateManagerBasicTest.php tests/HtmlRendererBasicTest.php
}

# 运行完整测试套件
function Run-AllTests {
    Write-Host "🚀 运行完整测试套件..." -ForegroundColor Green
    & ./vendor/bin/pest
}

# 运行 Builder 组件测试
function Run-BuilderTests {
    Write-Host "🏗️ 运行 Builder 组件测试..." -ForegroundColor Green
    & ./vendor/bin/pest tests/BuilderComponentsTest.php
}

# 运行 Helper 函数测试
function Run-HelperTests {
    Write-Host "🔧 运行 Helper 函数测试..." -ForegroundColor Green
    & ./vendor/bin/pest tests/HelperFunctionsTest.php tests/HelperBuilderFunctionsTest.php
}

# 运行 TableBuilder 测试
function Run-TableBuilderTests {
    Write-Host "📊 运行 TableBuilder 测试..." -ForegroundColor Green
    & ./vendor/bin/pest tests/TableBuilderTest.php
}

# 运行 ComponentRef 测试
function Run-ComponentRefTests {
    Write-Host "🔗 运行 ComponentRef 测试..." -ForegroundColor Green
    & ./vendor/bin/pest tests/ComponentRefTest.php
}

# 显示测试覆盖率
function Show-Coverage {
    Write-Host "📊 生成测试覆盖率报告..." -ForegroundColor Green
    & ./vendor/bin/pest --coverage
}

# 生成 HTML 覆盖率报告
function Generate-HtmlCoverage {
    Write-Host "🌐 生成 HTML 覆盖率报告..." -ForegroundColor Green
    & ./vendor/bin/pest --coverage --coverage-html=coverage-report
    Write-Host "📁 覆盖率报告已生成到 coverage-report/ 目录" -ForegroundColor Green
    Write-Host "🌐 打开 coverage-report/dashboard.html 查看详细报告" -ForegroundColor Green
}

# 列出所有测试
function List-Tests {
    Write-Host "📋 所有测试用例：" -ForegroundColor Green
    & ./vendor/bin/pest --list
}

# 主循环
while ($true) {
    Show-Menu
    $choice = Read-Host "请输入选择 (0-12)"
    
    switch ($choice) {
        "1" { Run-BasicTests }
        "2" { Run-AllTests }
        "3" { 
            Write-Host "🔧 运行基础测试..." -ForegroundColor Green
            & ./vendor/bin/pest tests/BasicTest.php
        }
        "4" { 
            Write-Host "🏗️ 运行 StateManager 测试..." -ForegroundColor Green
            & ./vendor/bin/pest tests/StateManagerBasicTest.php
        }
        "5" { 
            Write-Host "🎨 运行 HtmlRenderer 测试..." -ForegroundColor Green
            & ./vendor/bin/pest tests/HtmlRendererBasicTest.php
        }
        "6" { Run-BuilderTests }
        "7" { Run-HelperTests }
        "8" { Run-TableBuilderTests }
        "9" { Run-ComponentRefTests }
        "10" { Show-Coverage }
        "11" { Generate-HtmlCoverage }
        "12" { List-Tests }
        "0" { 
            Write-Host "👋 再见！" -ForegroundColor Green
            exit 0
        }
        default { Write-Host "❌ 无效选择，请重新输入" -ForegroundColor Red }
    }
    
    Write-Host ""
    Read-Host "按 Enter 键继续..."
}