const esbuild = require('esbuild');
const path = require('path');

const isDev = process.argv.includes('--dev');

async function build(options) {
    if (isDev) {
        const ctx = await esbuild.context(options);
        await ctx.watch();
        console.log('Watching for changes...');
    } else {
        await esbuild.build(options);
        console.log('Build complete:', options.outdir || options.outfile);
    }
}

const baseOptions = {
    bundle: true,
    format: 'iife',
    target: ['es2020'],
    sourcemap: isDev ? 'inline' : false,
    minify: !isDev,
    logLevel: 'info',
};

// 1. Non-Alpine global JS
build({
    ...baseOptions,
    entryPoints: [path.resolve(__dirname, '../resources/js/index.js')],
    outfile: path.resolve(__dirname, '../resources/js/dist/index.js'),
});

// 2. Alpine component — awrelSettingsTabs
build({
    ...baseOptions,
    entryPoints: [path.resolve(__dirname, '../resources/js/components/settings-tabs.js')],
    outfile: path.resolve(__dirname, '../resources/js/dist/components/settings-tabs.js'),
});

// 3. Alpine component — awrelColorPicker
build({
    ...baseOptions,
    entryPoints: [path.resolve(__dirname, '../resources/js/components/color-picker.js')],
    outfile: path.resolve(__dirname, '../resources/js/dist/components/color-picker.js'),
});

// 4. Alpine component — awrelRangeSlider
build({
    ...baseOptions,
    entryPoints: [path.resolve(__dirname, '../resources/js/components/range-slider.js')],
    outfile: path.resolve(__dirname, '../resources/js/dist/components/range-slider.js'),
});
