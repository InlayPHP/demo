import { readFile } from 'node:fs/promises';
import { resolve } from 'node:path';

const buildDirectory = resolve('public/build');
const manifest = JSON.parse(
    await readFile(resolve(buildDirectory, 'manifest.json'), 'utf8'),
);
const stylesheet = manifest['resources/css/app.css'];

if (!stylesheet?.file) {
    throw new Error('The production manifest does not contain the app stylesheet.');
}

const css = await readFile(resolve(buildDirectory, stylesheet.file), 'utf8');
const requiredUtilities = [
    '.min-h-\\(--inlay-control-height\\)',
    '.ring-\\(--inlay-border\\)',
    '.text-\\(--inlay-text\\)',
];
const missingUtilities = requiredUtilities.filter(
    (utility) => !css.includes(utility),
);

if (missingUtilities.length > 0) {
    throw new Error(
        `The production stylesheet is missing Inlay utilities: ${missingUtilities.join(', ')}. ` +
            'Ensure Tailwind scans node_modules/@inlayphp.',
    );
}

console.log('Verified Inlay utilities in the production stylesheet.');
