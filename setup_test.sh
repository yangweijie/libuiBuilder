#!/bin/bash

# libuiBuilder 设计器 Bug 测试安装脚本

echo "🔧 设置 libuiBuilder 设计器 Bug 测试环境"
echo "=========================================="

# 检查 Node.js 是否安装
if ! command -v node &> /dev/null; then
    echo "❌ Node.js 未安装"
    echo "请先安装 Node.js: https://nodejs.org/"
    exit 1
fi

echo "✅ Node.js 版本: $(node --version)"

# 检查 npm 是否安装
if ! command -v npm &> /dev/null; then
    echo "❌ npm 未安装"
    exit 1
fi

echo "✅ npm 版本: $(npm --version)"

# 安装依赖
echo "📦 安装依赖..."
npm install

if [ $? -eq 0 ]; then
    echo "✅ 依赖安装完成"
else
    echo "❌ 依赖安装失败"
    exit 1
fi

# 创建测试截图目录
echo "📁 创建测试目录..."
mkdir -p test_screenshots
echo "✅ 测试目录已创建"

# 显示使用说明
echo ""
echo "🎉 安装完成！"
echo ""
echo "运行测试:"
echo "1. 完整测试（打开浏览器）: npm test"
echo "2. 无头测试（不打开浏览器）: npm run test:headless"
echo ""
echo "详细说明请查看: TEST_DESIGNER_BUG.md"
echo ""