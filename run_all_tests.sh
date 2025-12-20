#!/bin/bash

# libuiBuilder 全测试运行器 - 替代 pest tests/

echo "🧪 运行 libuiBuilder 所有测试"
echo "=========================="

# 获取所有测试文件（排除disabled目录和非测试文件）
TEST_FILES=$(find tests/ -name "*Test.php" -not -path "tests/disabled/*" | sort)

if [ -z "$TEST_FILES" ]; then
    echo "❌ 没有找到测试文件"
    exit 1
fi

echo "📋 找到以下测试文件："
echo "$TEST_FILES" | sed 's/^/  - /'
echo ""

# 运行所有测试
echo "🚀 开始运行测试..."
./vendor/bin/pest $TEST_FILES

RESULT=$?

echo ""
if [ $RESULT -eq 0 ]; then
    echo "✅ 所有测试通过！"
else
    echo "❌ 测试失败，退出码: $RESULT"
fi

exit $RESULT