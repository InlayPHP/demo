import type { ThemeSource } from '@inlayphp/theme';

/**
 * Standalone renderers are not mounted inside a Panel, so this bridge points
 * their semantic contract at the demo's shared light/dark CSS variables.
 */
export const standaloneRendererTheme = {
    accent: 'var(--inlay-demo-accent)',
    'accent-foreground': 'var(--inlay-demo-accent-foreground)',
    surface: 'var(--inlay-demo-surface)',
    'surface-muted': 'var(--inlay-demo-surface-muted)',
    foreground: 'var(--inlay-demo-foreground)',
    muted: 'var(--inlay-demo-muted)',
    border: 'var(--inlay-demo-border)',
    'control-border': 'var(--inlay-demo-control-border)',
    hover: 'var(--inlay-demo-hover)',
    badge: 'var(--inlay-demo-badge)',
    danger: 'var(--inlay-demo-danger)',
    'danger-surface': 'var(--inlay-demo-danger-surface)',
    success: 'var(--inlay-demo-success)',
    'success-surface': 'var(--inlay-demo-success-surface)',
    warning: 'var(--inlay-demo-warning)',
    'warning-surface': 'var(--inlay-demo-warning-surface)',
    info: 'var(--inlay-demo-info)',
    'info-surface': 'var(--inlay-demo-info-surface)',
    overlay: 'var(--inlay-demo-overlay)',
    scrim: 'var(--inlay-demo-scrim)',
    shadow: 'var(--inlay-demo-shadow)',
} satisfies ThemeSource;
