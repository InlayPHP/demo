import { createInertiaApp } from '@inertiajs/react'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createRoot } from 'react-dom/client'
import { initializeTheme } from '@/hooks/use-appearance'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'
const pages = {
    ...import.meta.glob('./pages-preview/**/*.tsx'),
    ...import.meta.glob('./pages/inlay/auth/login.tsx'),
    ...import.meta.glob('./pages/inlay/account-settings.tsx'),
    ...import.meta.glob('./pages/inlay/resource/index.tsx'),
    ...import.meta.glob('./pages/inlay/resource/form.tsx'),
    ...import.meta.glob('./pages/inlay/resource/view.tsx'),
    ...import.meta.glob('./pages/inlay-media-manager/index.tsx'),
    ...import.meta.glob('./pages/demo/*.tsx'),
}

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    strictMode: true,
    resolve: (name) => resolvePageComponent([
        `./pages-preview/${name}.tsx`,
        `./pages/${name}.tsx`,
    ], pages),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />)
    },
    progress: { color: '#4B5563' },
})

initializeTheme()
