#!/usr/bin/env node

/**
 * libuiBuilder 设计器 Bug 测试脚本
 * 测试问题：当切换操作系统平台或修改Window组件属性时，子组件会从视觉显示中消失
 * 
 * 使用方法：
 * 1. 确保已安装 Node.js 和 Puppeteer: npm install puppeteer
 * 2. 运行: node test_designer_bug.js
 */

const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

// 测试配置
const CONFIG = {
    headless: false, // 设置为 true 可以在无头模式下运行
    slowMo: 50,      // 减慢操作速度以便观察
    timeout: 30000,  // 超时时间
    screenshotDir: './test_screenshots' // 截图保存目录
};

// 创建截图目录
if (!fs.existsSync(CONFIG.screenshotDir)) {
    fs.mkdirSync(CONFIG.screenshotDir, { recursive: true });
}

/**
 * 获取时间戳用于文件名
 */
function getTimestamp() {
    return new Date().toISOString().replace(/[:.]/g, '-');
}

/**
 * 截图并保存
 */
async function takeScreenshot(page, name) {
    const timestamp = getTimestamp();
    const filename = path.join(CONFIG.screenshotDir, `${timestamp}_${name}.png`);
    await page.screenshot({ path: filename, fullPage: true });
    console.log(`截图已保存: ${filename}`);
    return filename;
}

/**
 * 等待指定时间
 */
function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * 模拟拖拽操作
 */
async function dragAndDrop(page, sourceSelector, targetSelector) {
    const source = await page.$(sourceSelector);
    const target = await page.$(targetSelector);
    
    if (!source) throw new Error(`找不到源元素: ${sourceSelector}`);
    if (!target) throw new Error(`找不到目标元素: ${targetSelector}`);
    
    const sourceBox = await source.boundingBox();
    const targetBox = await target.boundingBox();
    
    // 模拟拖拽过程
    await page.mouse.move(sourceBox.x + sourceBox.width / 2, sourceBox.y + sourceBox.height / 2);
    await page.mouse.down();
    await delay(100);
    await page.mouse.move(targetBox.x + targetBox.width / 2, targetBox.y + targetBox.height / 2);
    await delay(100);
    await page.mouse.up();
    await delay(500); // 等待拖拽完成
}

/**
 * 获取组件数量
 */
async function getComponentCount(page) {
    return await page.evaluate(() => {
        return document.querySelectorAll('.designer-component').length;
    });
}

/**
 * 获取Window组件内的子组件数量
 */
async function getWindowChildCount(page) {
    return await page.evaluate(() => {
        const windows = document.querySelectorAll('[data-component-type="window"]');
        if (windows.length === 0) return 0;
        
        const windowElement = windows[0];
        // 查找window-content或component-content内的子组件
        const windowContent = windowElement.querySelector('.window-content') || 
                             windowElement.querySelector('.component-content');
        
        if (!windowContent) return 0;
        
        // 查找直接子组件
        const childComponents = windowContent.querySelectorAll('.designer-component');
        return childComponents.length;
    });
}

/**
 * 检查子组件是否可见
 */
async function areChildComponentsVisible(page) {
    return await page.evaluate(() => {
        const windows = document.querySelectorAll('[data-component-type="window"]');
        if (windows.length === 0) return false;
        
        const windowElement = windows[0];
        const windowContent = windowElement.querySelector('.window-content') || 
                             windowElement.querySelector('.component-content');
        
        if (!windowContent) return false;
        
        const childComponents = windowContent.querySelectorAll('.designer-component');
        if (childComponents.length === 0) return false;
        
        // 检查每个子组件是否可见
        for (let component of childComponents) {
            const style = window.getComputedStyle(component);
            if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
                console.log('发现不可见的子组件:', component.dataset.componentType, {
                    display: style.display,
                    visibility: style.visibility,
                    opacity: style.opacity
                });
                return false;
            }
            
            // 检查是否在视口内
            const rect = component.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) {
                console.log('子组件尺寸为0:', component.dataset.componentType, rect);
                return false;
            }
        }
        
        return true;
    });
}

/**
 * 获取组件详细信息用于调试
 */
async function getComponentDebugInfo(page) {
    return await page.evaluate(() => {
        const components = document.querySelectorAll('.designer-component');
        const info = [];
        
        components.forEach(comp => {
            const rect = comp.getBoundingClientRect();
            const style = window.getComputedStyle(comp);
            info.push({
                id: comp.dataset.componentId,
                type: comp.dataset.componentType,
                parent: comp.dataset.parent,
                position: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
                display: style.display,
                visibility: style.visibility,
                opacity: style.opacity,
                zIndex: style.zIndex,
                html: comp.outerHTML.substring(0, 200) + '...'
            });
        });
        
        return info;
    });
}

/**
 * 主测试函数
 */
async function runTest() {
    console.log('开始测试 libuiBuilder 设计器 Bug...');
    console.log('========================================');
    
    let browser;
    let page;
    
    try {
        // 1. 启动浏览器
        console.log('1. 启动浏览器...');
        browser = await puppeteer.launch({
            headless: CONFIG.headless,
            slowMo: CONFIG.slowMo,
            args: ['--window-size=1400,900']
        });
        
        page = await browser.newPage();
        await page.setViewport({ width: 1400, height: 900 });
        
        // 2. 打开设计器页面
        console.log('2. 打开设计器页面...');
        const designerPath = path.join(__dirname, 'tools', 'designer.html');
        const fileUrl = `file://${designerPath}`;
        await page.goto(fileUrl, { waitUntil: 'networkidle0' });
        await delay(2000); // 等待页面完全加载
        
        await takeScreenshot(page, '01_initial_load');
        
        // 3. 拖拽Window组件到画布
        console.log('3. 拖拽Window组件到画布...');
        const windowComponent = '.component-item[data-component="window"]';
        const designCanvas = '#designCanvas';
        
        await dragAndDrop(page, windowComponent, designCanvas);
        await delay(1000);
        
        await takeScreenshot(page, '02_window_added');
        
        // 验证Window组件已添加
        const windowCount = await page.evaluate(() => {
            return document.querySelectorAll('[data-component-type="window"]').length;
        });
        
        console.log(`Window组件数量: ${windowCount}`);
        if (windowCount === 0) {
            throw new Error('Window组件添加失败');
        }
        
        // 4. 在Window中添加HBox组件
        console.log('4. 在Window中添加HBox组件...');
        const hboxComponent = '.component-item[data-component="hbox"]';
        
        // 先点击Window组件选中它
        await page.click('[data-component-type="window"]');
        await delay(500);
        
        // 拖拽HBox到Window中
        await dragAndDrop(page, hboxComponent, '[data-component-type="window"]');
        await delay(1000);
        
        await takeScreenshot(page, '03_hbox_added');
        
        // 5. 在HBox中添加Input组件
        console.log('5. 在HBox中添加Input组件...');
        const inputComponent = '.component-item[data-component="input"]';
        
        // 先点击HBox组件选中它
        await page.click('[data-component-type="hbox"]');
        await delay(500);
        
        // 拖拽Input到HBox中
        await dragAndDrop(page, inputComponent, '[data-component-type="hbox"]');
        await delay(1000);
        
        await takeScreenshot(page, '04_input_added');
        
        // 验证子组件已添加
        const childCountBefore = await getWindowChildCount(page);
        console.log(`Window内子组件数量（修改前）: ${childCountBefore}`);
        
        if (childCountBefore < 2) {
            throw new Error(`子组件添加失败，期望至少2个，实际${childCountBefore}个`);
        }
        
        // 6. 修改Window属性（边距）
        console.log('6. 修改Window属性（边距）...');
        
        // 点击Window组件以显示属性面板
        await page.click('[data-component-type="window"]');
        await delay(1000);
        
        // 修改边距属性为false
        const marginedSelect = 'select[data-prop="margined"]';
        await page.select(marginedSelect, 'false');
        await delay(1500); // 等待属性更新和组件重新渲染
        
        await takeScreenshot(page, '05_window_margined_false');
        
        // 验证修改后子组件是否可见
        const visibleAfterMargined = await areChildComponentsVisible(page);
        console.log(`修改边距后子组件是否可见: ${visibleAfterMargined}`);
        
        if (!visibleAfterMargined) {
            console.log('⚠️  BUG重现：修改Window边距属性后子组件消失！');
            
            // 获取调试信息
            const debugInfo = await getComponentDebugInfo(page);
            console.log('调试信息:', JSON.stringify(debugInfo, null, 2));
        }
        
        // 7. 切换操作系统平台
        console.log('7. 切换操作系统平台...');
        
        // 切换到macOS
        await page.click('.platform-btn[data-platform="macos"]');
        await delay(2000); // 等待平台切换和组件重新渲染
        
        await takeScreenshot(page, '06_platform_macos');
        
        // 验证切换平台后子组件是否可见
        const visibleAfterPlatformSwitch = await areChildComponentsVisible(page);
        console.log(`切换到macOS后子组件是否可见: ${visibleAfterPlatformSwitch}`);
        
        if (!visibleAfterPlatformSwitch) {
            console.log('⚠️  BUG重现：切换操作系统平台后子组件消失！');
            
            // 获取调试信息
            const debugInfo = await getComponentDebugInfo(page);
            console.log('调试信息:', JSON.stringify(debugInfo, null, 2));
        }
        
        // 切换回Windows
        await page.click('.platform-btn[data-platform="windows"]');
        await delay(2000);
        
        await takeScreenshot(page, '07_platform_windows_restored');
        
        // 8. 最终验证
        console.log('8. 最终验证...');
        const finalChildCount = await getWindowChildCount(page);
        const finalVisible = await areChildComponentsVisible(page);
        
        console.log(`最终Window内子组件数量: ${finalChildCount}`);
        console.log(`最终子组件是否可见: ${finalVisible}`);
        
        // 总结
        console.log('\n========================================');
        console.log('测试总结:');
        console.log('========================================');
        
        if (!visibleAfterMargined || !visibleAfterPlatformSwitch) {
            console.log('❌ BUG确认：子组件在以下操作后消失:');
            if (!visibleAfterMargined) console.log('  - 修改Window边距属性');
            if (!visibleAfterPlatformSwitch) console.log('  - 切换操作系统平台');
            
            console.log('\n可能的原因分析:');
            console.log('1. 在refreshComponent方法中，重新创建组件时子组件未正确重新渲染');
            console.log('2. updateComponentStyles方法可能没有正确处理子组件的样式更新');
            console.log('3. 容器组件的子组件渲染逻辑可能存在问题');
            console.log('\n建议检查以下代码:');
            console.log('- tools/designer.js 中的 refreshComponent 方法');
            console.log('- tools/designer.js 中的 updateComponentStyles 方法');
            console.log('- tools/designer.js 中的 renderComponent 方法（特别是容器组件的子组件渲染）');
            
            process.exit(1); // 测试失败
        } else {
            console.log('✅ 测试通过：子组件在所有操作后保持可见');
            console.log('\n注意：如果手动测试时发现问题但自动化测试通过，');
            console.log('可能是由于测试速度太快或交互方式不同。');
            console.log('建议：');
            console.log('1. 增加操作之间的延迟时间');
            console.log('2. 检查是否有异步操作未等待');
            console.log('3. 验证拖拽操作的准确性');
        }
        
    } catch (error) {
        console.error('测试失败:', error);
        console.error(error.stack);
        
        if (page) {
            await takeScreenshot(page, 'error_state');
        }
        
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

// 运行测试
runTest();