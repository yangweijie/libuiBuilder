// 拖拽过程调试脚本
console.log('=== 拖拽过程调试 ===');

// 获取设计器实例
const designer = window.designer;

// 重写addComponent函数以添加调试信息
const originalAddComponent = designer.addComponent.bind(designer);
designer.addComponent = function(type, x, y, parentComponent = null) {
    console.log('🔍 addComponent 被调用:', {
        type,
        x,
        y,
        parentComponent: parentComponent ? {
            id: parentComponent.id,
            type: parentComponent.type
        } : null,
        currentComponentsCount: this.components.length
    });
    
    const result = originalAddComponent(type, x, y, parentComponent);
    
    console.log('✅ addComponent 完成:', {
        newComponentsCount: this.components.length,
        allComponents: this.components.map(c => ({
            id: c.id,
            type: c.type,
            parent: c.parent,
            childrenCount: c.children.length
        }))
    });
    
    return result;
};

// 重写getComponentAtPosition函数以添加调试信息
const originalGetComponentAtPosition = designer.getComponentAtPosition.bind(designer);
designer.getComponentAtPosition = function(clientX, clientY) {
    console.log('🔍 getComponentAtPosition 被调用:', { clientX, clientY });
    
    const result = originalGetComponentAtPosition(clientX, clientY);
    
    console.log('✅ getComponentAtPosition 结果:', result ? {
        id: result.id,
        type: result.type
    } : null);
    
    return result;
};

console.log('📝 调试钩子已设置，现在可以拖拽Window组件了');