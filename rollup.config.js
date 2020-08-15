import babel from 'rollup-plugin-babel';
import pkg from './package.json';

const config = {
    input: 'assets-src/main.js',
    output: [{
        file: pkg.module,
        format: 'esm',
    }, ],
    plugins: [
        babel({
            babelrc: false,
            presets: [
                ['env', {
                    modules: false
                }]
            ],
        }),
    ],
};

export default config;