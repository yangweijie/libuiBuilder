#!/usr/bin/env node

/**
 * libuiBuilder 设计器 Bug 测试脚本（简化版）
 * 测试问题：当切换操作系统平台或修改Window组件属性时，子组件会从视觉显示中消失
 */

const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

// 测试结果
const testResults = {
    steps: [],
    bugsFound: [],
    screenshots: []
};

/**
 * 记录测试步骤
 */
function logStep(step, success = true, details = '') {
    const result = {
        step,
        success,
        timestamp: new Date().toISOString(),
        details
    };
    testResults.steps.push(result);
    console.log(`${success ? '✅' : '❌'} ${step} ${details}`);
}

/**
 * 截图并保存
 */
async function takeScreenshot(page, stepName) {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const screenshotDir = './test_screenshots';
    
    if (!fs.existsSync(screenshotDir)) {
        fs.mkdirSync(screenshotDir, { recursive: true });
    }
    
    const filename = path.join(screenshotDir, `${timestamp}_${stepName}.png`);
    await page.screenshot({ path: filename });
    testResults.screenshots.push({ step: stepName, file: filename });
    return filename;
}

/**
 * 检查子组件是否可见
 */
async function checkChildComponentsVisible(page, context) {
    return await page.evaluate((context) => {
        const windows = document.querySelectorAll('[data-component-type="window"]');
        if (windows.length === 0) {
            console.log(`${context}: 未找到Window组件`);
            return { visible: false, reason: 'no-window' };
        }
        
        const windowElement = windows[0];
        const windowContent = windowElement.querySelector('.window-content') || 
                             windowElement.querySelector('.component-content');
        
        if (!windowContent) {
            console.log(`${context}: 未找到Window内容区域`);
            return { visible: false, reason: 'no-content' };
        }
        
        const childComponents = windowContent.querySelectorAll('.designer-component');
        console.log(`${context}: 找到 ${childComponents.length} 个子组件`);
        
        if (childComponents.length === 0) {
            return { visible: false, reason: 'no-children', count: 0 };
        }
        
        // 检查每个子组件
        const invisibleComponents = [];
        for (let i = 0; i < childComponents.length; i++) {
            const component = childComponents[i];
            const style = window.getComputedStyle(component);
            const rect = component.getBoundingClientRect();
            
            const isVisible = style.display !== 'none' && 
                            style.visibility !== 'hidden' && 
                            style.opacity !== '0' &&
                            rect.width > 0 && 
                            rect.height > 0;
            
            if (!isVisible) {
                invisibleComponents.push({
                    type: component.dataset.componentType,
                    id: component.dataset.componentId,
                    display: style.display,
                    visibility: style.visibility,
                    opacity: style.opacity,
                    width: rect.width,
                    height: rect.height
                });
            }
        }
        
        return {
            visible: invisibleComponents.length === 0,
            count: childComponents.length,
            invisible: invisibleComponents,
            reason: invisibleComponents.length > 0 ? 'invisible-children' : 'all-visible'
        };
    }, context);
}

/**
 * 模拟拖拽操作
 */
async function simulateDragAndDrop(page, sourceSelector, targetSelector) {
    const source = await page.$(sourceSelector);
    const target = await page.$(targetSelector);
    
    if (!source || !target) {
        throw new Error(`拖拽失败: 源(${sourceSelector})或目标(${targetSelector})未找到`);
    }
    
    const sourceBox = await source.boundingBox();
    const targetBox = await target.boundingBox();
    
    await page.mouse.move(sourceBox.x + sourceBox.width / 2, sourceBox.y + sourceBox.height / 2);
    await page.mouse.down();
    await new Promise(resolve => setTimeout(resolve, 100));
    await page.mouse.move(targetBox.x + targetBox.width / 2, targetBox.y + targetBox.height / 2);
    await new Promise(resolve => setTimeout(resolve, 100));
    await page.mouse.up();
    await new Promise(resolve => setTimeout(resolve, 500));
}

/**
 * 主测试函数
 */
async function runTest() {
    console.log('🚀 开始测试 libuiBuilder 设计器 Bug');
    console.log('========================================\n');
    
    const browser = await puppeteer.launch({
        headless: false, // 设置为 true 可无头运行
        slowMo: 50,
        args: ['--window-size=1400,900']
    });
    
    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 900 });
    
    try {
        // 步骤1: 打开设计器
        logStep('打开设计器页面');
        const designerPath = path.join(__dirname, 'tools', 'designer.html');
        await page.goto(`file://${designerPath}`, { waitUntil: 'networkidle0' });
        await new Promise(resolve => setTimeout(resolve, 2000));
        await takeScreenshot(page, '01_initial');
        
        // 步骤2: 添加Window组件
        logStep('拖拽Window组件到画布');
        await simulateDragAndDrop(page, 
            '.component-item[data-component="window"]', 
            '#designCanvas'
        );
        await takeScreenshot(page, '02_window_added');
        
        // 步骤3: 添加HBox到Window
        logStep('添加HBox组件到Window');
        await page.click('[data-component-type="window"]');
        await new Promise(resolve => setTimeout(resolve, 500));
        await simulateDragAndDrop(page,
            '.component-item[data-component="hbox"]',
            '[data-component-type="window"]'
        );
        await takeScreenshot(page, '03_hbox_added');
        
        // 步骤4: 添加Input到HBox
        logStep('添加Input组件到HBox');
        await page.click('[data-component-type="hbox"]');
        await new Promise(resolve => setTimeout(resolve, 500));
        await simulateDragAndDrop(page,
            '.component-item[data-component="input"]',
            '[data-component-type="hbox"]'
        );
        await takeScreenshot(page, '04_input_added');
        
        // 初始状态检查
        logStep('检查初始状态子组件可见性');
        const initialCheck = await checkChildComponentsVisible(page, '初始状态');
        if (!initialCheck.visible) {
            testResults.bugsFound.push('初始状态子组件不可见');
            logStep('初始状态检查', false, `子组件不可见: ${initialCheck.reason}`);
        }
        
        // 步骤5: 修改Window边距属性
        logStep('修改Window边距属性为false');
        await page.click('[data-component-type="window"]');
        await new Promise(resolve => setTimeout(resolve, 1000));
        await page.select('select[data-prop="margined"]', 'false');
        await new Promise(resolve => setTimeout(resolve, 1500));
        await takeScreenshot(page, '05_margined_false');
        
        // 检查修改属性后的可见性
        const afterMarginedCheck = await checkChildComponentsVisible(page, '修改边距后');
        if (!afterMarginedCheck.visible) {
            testResults.bugsFound.push('修改Window边距后子组件消失');
            logStep('修改边距后检查', false, `BUG重现: ${afterMarginedCheck.reason}`);
            console.log('不可见组件详情:', JSON.stringify(afterMarginedCheck.invisible, null, 2));
        }
        
        // 步骤6: 切换操作系统平台
        logStep('切换操作系统平台到macOS');
        await page.click('.platform-btn[data-platform="macos"]');
        await new Promise(resolve => setTimeout(resolve, 2000));
        await takeScreenshot(page, '06_platform_macos');
        
        // 检查切换平台后的可见性
        const afterPlatformCheck = await checkChildComponentsVisible(page, '切换平台后');
        if (!afterPlatformCheck.visible) {
            testResults.bugsFound.push('切换操作系统平台后子组件消失');
            logStep('切换平台后检查', false, `BUG重现: ${afterPlatformCheck.reason}`);
            console.log('不可见组件详情:', JSON.stringify(afterPlatformCheck.invisible, null, 2));
        }
        
        // 步骤7: 切换回Windows
        logStep('切换回Windows平台');
        await page.click('.platform-btn[data-platform="windows"]');
        await new Promise(resolve => setTimeout(resolve, 2000));
        await takeScreenshot(page, '07_platform_windows');
        
        // 最终检查
        logStep('最终状态检查');
        const finalCheck = await checkChildComponentsVisible(page, '最终状态');
        
        // 输出测试报告
        console.log('\n========================================');
        console.log('📊 测试报告');
        console.log('========================================');
        console.log(`总步骤数: ${testResults.steps.length}`);
        console.log(`成功步骤: ${testResults.steps.filter(s => s.success).length}`);
        console.log(`失败步骤: ${testResults.steps.filter(s => !s.success).length}`);
        
        if (testResults.bugsFound.length > 0) {
            console.log('\n❌ 发现的BUG:');
            testResults.bugsFound.forEach((bug, index) => {
                console.log(`  ${index + 1}. ${bug}`);
            });
            
            console.log('\n🔍 问题分析:');
            console.log('根据代码分析，可能的原因包括:');
            console.log('1. refreshComponent() 方法重新创建组件时，子组件未正确重新渲染');
            console.log('2. updateComponentStyles() 方法可能丢失子组件的引用');
            console.log('3. 容器组件的DOM结构在重新渲染时可能被破坏');
            
            console.log('\n💡 建议检查的文件:');
            console.log('- tools/designer.js 中的 refreshComponent 方法（约第850行）');
            console.log('- tools/designer.js 中的 updateComponentStyles 方法（约第1350行）');
            console.log('- tools/designer.js 中的 renderComponent 方法（约第400行）');
            
            // 保存详细测试报告
            const report = {
                timestamp: new Date().toISOString(),
                summary: {
                    totalSteps: testResults.steps.length,
                    successfulSteps: testResults.steps.filter(s => s.success).length,
                    failedSteps: testResults.steps.filter(s => !s.success).length,
                    bugsFound: testResults.bugsFound.length
                },
                bugs: testResults.bugsFound,
                steps: testResults.steps,
                screenshots: testResults.screenshots.map(s => s.file)
            };
            
            const reportFile = `test_report_${new Date().toISOString().replace(/[:.]/g, '-')}.json`;
            fs.writeFileSync(reportFile, JSON.stringify(report, null, 2));
            console.log(`\n📄 详细测试报告已保存到: ${reportFile}`);
            
            process.exit(1);
        } else {
            console.log('\n✅ 测试通过！未发现子组件消失的问题。');
            console.log('\n⚠️  注意: 如果手动测试时发现问题但自动化测试通过，');
            console.log('可能是由于:');
            console.log('1. 测试速度太快，未等待所有异步操作完成');
            console.log('2. 拖拽操作的坐标不够精确');
            console.log('3. 组件的选中状态可能影响测试结果');
            console.log('\n建议手动验证bug是否仍然存在。');
        }
        
    } catch (error) {
        console.error('❌ 测试执行失败:', error.message);
        console.error(error.stack);
        await takeScreenshot(page, 'error_state');
        process.exit(1);
    } finally {
        await browser.close();
    }
}

// 运行测试
runTest();
