// 调试脚本：检查Window组件嵌套问题
console.log('=== Window组件嵌套问题调试 ===');

// 获取设计器实例
const designer = window.designer;

if (designer) {
    console.log('设计器实例找到');
    console.log('根级组件数量:', designer.components.length);
    
    // 检查所有根级组件
    designer.components.forEach((component, index) => {
        console.log(`根级组件 ${index}:`, {
            id: component.id,
            type: component.type,
            parent: component.parent,
            childrenCount: component.children.length,
            children: component.children.map(child => ({
                id: child.id,
                type: child.type,
                parent: child.parent
            }))
        });
    });
    
    // 检查是否有Window组件
    const windowComponents = designer.components.filter(c => c.type === 'window');
    console.log('Window组件数量:', windowComponents.length);
    
    if (windowComponents.length > 0) {
        windowComponents.forEach((windowComp, index) => {
            console.log(`Window组件 ${index}:`, {
                id: windowComp.id,
                type: windowComp.type,
                parent: windowComp.parent,
                childrenCount: windowComp.children.length,
                children: windowComp.children.map(child => ({
                    id: child.id,
                    type: child.type,
                    parent: child.parent
                }))
            });
        });
    }
    
    // 检查生成的HTML
    const generatedHTML = designer.generateHTML();
    console.log('生成的HTML:', generatedHTML);
    
} else {
    console.error('设计器实例未找到');
}