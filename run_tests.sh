#!/bin/bash

# libuiBuilder 测试运行脚本

echo "🧪 libuiBuilder 测试套件"
echo "===================="

# 检查 Pest 是否可用
if [ ! -f "./vendor/bin/pest" ]; then
    echo "❌ Pest 未找到，请先运行 composer install"
    exit 1
fi

# 显示菜单
show_menu() {
    echo ""
    echo "请选择要运行的测试："
    echo "1) 基础测试 (Basic, StateManager, HtmlRenderer)"
    echo "2) 完整测试套件"
    echo "3) 只运行基础测试"
    echo "4) 只运行 StateManager 测试"
    echo "5) 只运行 HtmlRenderer 测试"
    echo "6) 运行 Builder 组件测试"
    echo "7) 运行 Helper 函数测试"
    echo "8) 运行 TableBuilder 测试"
    echo "9) 运行 ComponentRef 测试"
    echo "10) 显示测试覆盖率"
    echo "11) 生成 HTML 覆盖率报告"
    echo "12) 列出所有测试用例"
    echo "0) 退出"
    echo ""
}

# 运行基础测试
run_basic_tests() {
    echo "🔧 运行基础测试..."
    ./vendor/bin/pest tests/BasicTest.php tests/StateManagerBasicTest.php tests/HtmlRendererBasicTest.php
}

# 运行完整测试套件
run_all_tests() {
    echo "🚀 运行完整测试套件..."
    ./vendor/bin/pest
}

# 显示测试覆盖率
show_coverage() {
    echo "📊 生成测试覆盖率报告..."
    ./vendor/bin/pest --coverage
}

# 生成 HTML 覆盖率报告
generate_html_coverage() {
    echo "🌐 生成 HTML 覆盖率报告..."
    ./vendor/bin/pest --coverage --coverage-html=coverage-report
    echo "📁 覆盖率报告已生成到 coverage-report/ 目录"
    echo "🌐 打开 coverage-report/dashboard.html 查看详细报告"
}

# 运行 Builder 组件测试
run_builder_tests() {
    echo "🏗️ 运行 Builder 组件测试..."
    ./vendor/bin/pest tests/BuilderComponentsTest.php
}

# 运行 Helper 函数测试
run_helper_tests() {
    echo "🔧 运行 Helper 函数测试..."
    ./vendor/bin/pest tests/HelperFunctionsTest.php tests/HelperBuilderFunctionsTest.php
}

# 运行 TableBuilder 测试
run_tablebuilder_tests() {
    echo "📊 运行 TableBuilder 测试..."
    ./vendor/bin/pest tests/TableBuilderTest.php
}

# 运行 ComponentRef 测试
run_componentref_tests() {
    echo "🔗 运行 ComponentRef 测试..."
    ./vendor/bin/pest tests/ComponentRefTest.php
}

# 列出所有测试
list_tests() {
    echo "📋 所有测试用例："
    ./vendor/bin/pest --list
}

# 主循环
while true; do
    show_menu
    read -p "请输入选择 (0-12): " choice
    
    case $choice in
        1)
            run_basic_tests
            ;;
        2)
            run_all_tests
            ;;
        3)
            echo "🔧 运行基础测试..."
            ./vendor/bin/pest tests/BasicTest.php
            ;;
        4)
            echo "🏗️ 运行 StateManager 测试..."
            ./vendor/bin/pest tests/StateManagerBasicTest.php
            ;;
        5)
            echo "🎨 运行 HtmlRenderer 测试..."
            ./vendor/bin/pest tests/HtmlRendererBasicTest.php
            ;;
        6)
            run_builder_tests
            ;;
        7)
            run_helper_tests
            ;;
        8)
            run_tablebuilder_tests
            ;;
        9)
            run_componentref_tests
            ;;
        10)
            show_coverage
            ;;
        11)
            generate_html_coverage
            ;;
        12)
            list_tests
            ;;
        0)
            echo "👋 再见！"
            exit 0
            ;;
        *)
            echo "❌ 无效选择，请重新输入"
            ;;
    esac
    
    echo ""
    read -p "按 Enter 键继续..."
done