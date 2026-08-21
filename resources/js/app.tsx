import { createInertiaApp } from '@inertiajs/react';
import type { ResolvedComponent } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from '@/hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
type PageModule = { default: ResolvedComponent };

const pages = {
    ...import.meta.glob<PageModule>('./pages-preview/**/*.tsx'),
    ...import.meta.glob<PageModule>('./pages/inlay/auth/login.tsx'),
    ...import.meta.glob<PageModule>('./pages/inlay/account-settings.tsx'),
    ...import.meta.glob<PageModule>('./pages/inlay/resource/index.tsx'),
    ...import.meta.glob<PageModule>('./pages/inlay/resource/form.tsx'),
    ...import.meta.glob<PageModule>('./pages/inlay/resource/view.tsx'),
    ...import.meta.glob<PageModule>('./pages/inlay-media-manager/index.tsx'),
    ...import.meta.glob<PageModule>('./pages/demo/*.tsx'),
};

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    strictMode: true,
    resolve: (name) =>
        resolvePageComponent<PageModule>(
            [`./pages-preview/${name}.tsx`, `./pages/${name}.tsx`],
            pages,
        ).then((page) => page.default),
    setup({ el, App, props }) {
        if (el) {
            createRoot(el).render(<App {...props} />);
        }
    },
    progress: { color: '#4B5563' },
});

initializeTheme();
