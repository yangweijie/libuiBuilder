// 简单的调试脚本来诊断设计器组件消失问题
const fs = require('fs');
const path = require('path');

console.log('🔍 分析libuiBuilder设计器组件消失问题...\n');

// 读取designer.js文件
const designerPath = path.join(__dirname, 'tools/designer.js');
const designerContent = fs.readFileSync(designerPath, 'utf8');

console.log('📄 分析文件:', designerPath);
console.log('📏 文件大小:', designerContent.length, '字符\n');

// 查找关键方法
const methods = [
    { name: 'refreshComponent', desc: '刷新组件方法' },
    { name: 'updateComponentStyles', desc: '更新组件样式方法' },
    { name: 'renderComponent', desc: '渲染组件方法' },
    { name: 'createComponentElement', desc: '创建组件元素方法' }
];

console.log('🔎 查找关键方法:');
methods.forEach(method => {
    const regex = new RegExp(`${method.name}\([^)]*\)\s*\{[^}]*\}`, 'gs');
    const matches = designerContent.match(regex);
    if (matches) {
        console.log(`\n✅ ${method.name} - ${method.desc}`);
        console.log(`   找到 ${matches.length} 个匹配`);
        
        // 显示方法的前几行
        const firstMatch = matches[0];
        const lines = firstMatch.split('\n');
        console.log('   方法内容预览:');
        for (let i = 0; i < Math.min(10, lines.length); i++) {
            console.log(`   ${lines[i].substring(0, 100)}${lines[i].length > 100 ? '...' : ''}`);
        }
    } else {
        console.log(`\n❌ ${method.name} - 未找到`);
    }
});

// 查找可能导致问题的代码模式
console.log('\n🔍 查找可能导致组件消失的代码模式:');

const problematicPatterns = [
    { pattern: 'innerHTML\\s*=', desc: '使用innerHTML可能清除子元素' },
    { pattern: 'replaceChild', desc: 'replaceChild可能丢失子元素引用' },
    { pattern: 'remove\\(\\)', desc: '直接移除元素' },
    { pattern: 'children\\s*=\\s*\\[\\]', desc: '清空子组件数组' },
    { pattern: 'component\\.children\\s*=', desc: '直接赋值子组件数组' }
];

problematicPatterns.forEach(item => {
    const regex = new RegExp(item.pattern, 'g');
    const matches = designerContent.match(regex);
    if (matches) {
        console.log(`\n⚠️  ${item.desc}`);
        console.log(`   找到 ${matches.length} 处使用`);
        
        // 显示上下文
        const lines = designerContent.split('\n');
        let foundLines = [];
        for (let i = 0; i < lines.length; i++) {
            if (regex.test(lines[i])) {
                foundLines.push(`   第 ${i + 1} 行: ${lines[i].trim().substring(0, 80)}${lines[i].trim().length > 80 ? '...' : ''}`);
                if (foundLines.length >= 3) break;
            }
        }
        foundLines.forEach(line => console.log(line));
    }
});

// 分析refreshComponent方法的实现
console.log('\n🔬 详细分析refreshComponent方法:');
const refreshMethodRegex = /refreshComponent\(component\)\s*\{[\s\S]*?\n\s*\}/;
const refreshMatch = designerContent.match(refreshMethodRegex);

if (refreshMatch) {
    const methodContent = refreshMatch[0];
    console.log('方法完整内容:');
    console.log(methodContent);
    
    // 分析潜在问题
    console.log('\n📋 潜在问题分析:');
    
    if (methodContent.includes('element.parentNode.replaceChild')) {
        console.log('❌ 问题1: 使用replaceChild替换整个元素，可能导致事件监听器丢失');
    }
    
    if (methodContent.includes('currentChildren = [...component.children]')) {
        console.log('✅ 良好实践: 保存子组件副本');
    }
    
    if (methodContent.includes('component.children = []')) {
        console.log('⚠️  警告: 清空子组件数组，需要确保重新添加');
    }
    
    if (methodContent.includes('this.renderComponent(child, component)')) {
        console.log('✅ 良好实践: 重新渲染子组件');
    } else {
        console.log('❌ 问题: 没有重新渲染子组件');
    }
    
    // 检查是否处理了嵌套子组件
    if (methodContent.includes('child.children') && methodContent.includes('length > 0')) {
        console.log('✅ 良好实践: 检查嵌套子组件');
    } else {
        console.log('⚠️  警告: 可能没有处理嵌套子组件');
    }
}

console.log('\n💡 建议的修复方案:');
console.log('1. 在refreshComponent方法中，确保子组件被正确重新渲染');
console.log('2. 使用深拷贝保存子组件状态，避免引用问题');
console.log('3. 确保容器组件的内容区域被正确找到和更新');
console.log('4. 添加调试日志来跟踪组件状态变化');

console.log('\n🔧 快速诊断命令:');
console.log('要测试设计器，可以:');
console.log('1. 打开 tools/designer.html 在浏览器中');
console.log('2. 按F12打开开发者工具');
console.log('3. 在Console中执行: designer.refreshComponent(designer.components[0])');
console.log('4. 观察子组件是否消失');

console.log('\n✅ 分析完成！');
