#!/usr/bin/env node

/**
 * libuiBuilder 设计器 Bug 测试脚本（无头模式）
 * 用于CI/CD环境或快速测试
 */

const puppeteer = require('puppeteer');
const path = require('path');

// 测试配置
const CONFIG = {
    headless: 'new', // 使用新的无头模式
    timeout: 30000,
    viewport: { width: 1280, height: 800 }
};

/**
 * 检查子组件是否可见
 */
async function checkChildComponentsVisible(page, context) {
    return await page.evaluate((context) => {
        try {
            const windows = document.querySelectorAll('[data-component-type="window"]');
            if (windows.length === 0) {
                return { visible: false, reason: 'no-window', context };
            }
            
            const windowElement = windows[0];
            const windowContent = windowElement.querySelector('.window-content') || 
                                 windowElement.querySelector('.component-content');
            
            if (!windowContent) {
                return { visible: false, reason: 'no-content', context };
            }
            
            const childComponents = windowContent.querySelectorAll('.designer-component');
            
            if (childComponents.length === 0) {
                return { visible: false, reason: 'no-children', count: 0, context };
            }
            
            // 快速检查：是否有可见的子组件
            let visibleCount = 0;
            for (let component of childComponents) {
                const style = window.getComputedStyle(component);
                const rect = component.getBoundingClientRect();
                
                if (style.display !== 'none' && 
                    style.visibility !== 'hidden' && 
                    style.opacity !== '0' &&
                    rect.width > 0 && 
                    rect.height > 0) {
                    visibleCount++;
                }
            }
            
            return {
                visible: visibleCount === childComponents.length,
                count: childComponents.length,
                visibleCount,
                context
            };
        } catch (error) {
            return { visible: false, reason: 'error', error: error.message, context };
        }
    }, context);
}

/**
 * 模拟点击操作
 */
async function clickElement(page, selector, delayMs = 500) {
    await page.waitForSelector(selector, { timeout: 5000 });
    await page.click(selector);
    await new Promise(resolve => setTimeout(resolve, delayMs));
}

/**
 * 模拟选择操作
 */
async function selectOption(page, selector, value, delayMs = 1000) {
    await page.waitForSelector(selector, { timeout: 5000 });
    await page.select(selector, value);
    await new Promise(resolve => setTimeout(resolve, delayMs));
}

/**
 * 主测试函数
 */
async function runHeadlessTest() {
    console.log('🔍 运行无头模式测试...\n');
    
    const browser = await puppeteer.launch({
        headless: CONFIG.headless,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    await page.setViewport(CONFIG.viewport);
    
    const testResults = {
        passed: 0,
        failed: 0,
        details: []
    };
    
    try {
        // 1. 打开设计器
        console.log('1. 打开设计器页面...');
        const designerPath = path.join(__dirname, 'tools', 'designer.html');
        await page.goto(`file://${designerPath}`, { 
            waitUntil: 'networkidle0',
            timeout: CONFIG.timeout 
        });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // 2. 添加Window组件（使用JavaScript直接添加，避免复杂的拖拽）
        console.log('2. 添加Window组件...');
        await page.evaluate(() => {
            // 直接调用设计器的方法添加组件
            if (window.designer) {
                window.designer.addComponent('window', 100, 100);
            } else {
                // 如果无法访问designer对象，尝试模拟点击
                const canvas = document.getElementById('designCanvas');
                const event = new MouseEvent('click', {
                    clientX: canvas.offsetLeft + 100,
                    clientY: canvas.offsetTop + 100
                });
                canvas.dispatchEvent(event);
            }
        });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // 3. 添加HBox组件
        console.log('3. 添加HBox组件到Window...');
        await page.evaluate(() => {
            const windowElement = document.querySelector('[data-component-type="window"]');
            if (windowElement && window.designer) {
                const windowComponent = window.designer.findComponentById(windowElement.dataset.componentId);
                if (windowComponent) {
                    window.designer.addComponent('hbox', 50, 50, windowComponent);
                }
            }
        });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // 4. 添加Input组件
        console.log('4. 添加Input组件到HBox...');
        await page.evaluate(() => {
            const hboxElement = document.querySelector('[data-component-type="hbox"]');
            if (hboxElement && window.designer) {
                const hboxComponent = window.designer.findComponentById(hboxElement.dataset.componentId);
                if (hboxComponent) {
                    window.designer.addComponent('input', 20, 20, hboxComponent);
                }
            }
        });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // 5. 检查初始状态
        console.log('5. 检查初始状态...');
        const initialCheck = await checkChildComponentsVisible(page, '初始状态');
        if (initialCheck.visible && initialCheck.count >= 2) {
            console.log(`   ✅ 初始状态: ${initialCheck.count}个子组件可见`);
            testResults.passed++;
        } else {
            console.log(`   ❌ 初始状态: 只有${initialCheck.visibleCount || 0}/${initialCheck.count || 0}个子组件可见`);
            testResults.failed++;
        }
        testResults.details.push(initialCheck);
        
        // 6. 修改Window属性
        console.log('6. 修改Window边距属性...');
        await page.evaluate(() => {
            const windowElement = document.querySelector('[data-component-type="window"]');
            if (windowElement) {
                windowElement.click(); // 选中Window
                
                // 修改边距属性
                const select = document.querySelector('select[data-prop="margined"]');
                if (select) {
                    select.value = 'false';
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        });
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // 7. 检查修改属性后
        console.log('7. 检查修改边距后...');
        const afterMarginedCheck = await checkChildComponentsVisible(page, '修改边距后');
        if (afterMarginedCheck.visible && afterMarginedCheck.count >= 2) {
            console.log(`   ✅ 修改边距后: ${afterMarginedCheck.count}个子组件可见`);
            testResults.passed++;
        } else {
            console.log(`   ❌ 修改边距后: 只有${afterMarginedCheck.visibleCount || 0}/${afterMarginedCheck.count || 0}个子组件可见`);
            console.log(`       可能BUG: 修改Window属性后子组件消失`);
            testResults.failed++;
        }
        testResults.details.push(afterMarginedCheck);
        
        // 8. 切换平台
        console.log('8. 切换操作系统平台...');
        await page.evaluate(() => {
            const macosBtn = document.querySelector('.platform-btn[data-platform="macos"]');
            if (macosBtn) {
                macosBtn.click();
            }
        });
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // 9. 检查切换平台后
        console.log('9. 检查切换平台后...');
        const afterPlatformCheck = await checkChildComponentsVisible(page, '切换平台后');
        if (afterPlatformCheck.visible && afterPlatformCheck.count >= 2) {
            console.log(`   ✅ 切换平台后: ${afterPlatformCheck.count}个子组件可见`);
            testResults.passed++;
        } else {
            console.log(`   ❌ 切换平台后: 只有${afterPlatformCheck.visibleCount || 0}/${afterPlatformCheck.count || 0}个子组件可见`);
            console.log(`       可能BUG: 切换操作系统平台后子组件消失`);
            testResults.failed++;
        }
        testResults.details.push(afterPlatformCheck);
        
        // 输出测试结果
        console.log('\n========================================');
        console.log('📊 测试结果汇总');
        console.log('========================================');
        console.log(`总测试项: ${testResults.passed + testResults.failed}`);
        console.log(`通过: ${testResults.passed}`);
        console.log(`失败: ${testResults.failed}`);
        
        if (testResults.failed > 0) {
            console.log('\n❌ 测试失败: 发现子组件消失的问题');
            console.log('\n🔍 问题分析:');
            console.log('根据失败的项目，可能的问题包括:');
            
            if (afterMarginedCheck.visibleCount < afterMarginedCheck.count) {
                console.log('- 修改Window边距属性后子组件重新渲染失败');
                console.log('  检查: refreshComponent() 方法中的子组件处理逻辑');
            }
            
            if (afterPlatformCheck.visibleCount < afterPlatformCheck.count) {
                console.log('- 切换操作系统平台后子组件样式更新失败');
                console.log('  检查: updateComponentStyles() 方法中的组件遍历逻辑');
            }
            
            console.log('\n💡 调试建议:');
            console.log('1. 运行完整测试查看截图: node test_designer_bug_simple.js');
            console.log('2. 检查浏览器控制台是否有错误信息');
            console.log('3. 验证组件数据结构是否完整');
            
            process.exit(1);
        } else {
            console.log('\n✅ 所有测试通过！');
            console.log('\n⚠️  注意: 无头模式测试可能无法完全模拟用户交互');
            console.log('建议同时运行完整测试进行验证。');
        }
        
    } catch (error) {
        console.error('❌ 测试执行失败:', error.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
}

// 运行测试
runHeadlessTest();