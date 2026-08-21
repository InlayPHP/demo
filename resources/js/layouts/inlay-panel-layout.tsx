import { router, usePage } from '@inertiajs/react';
import { NotificationCenter } from '@inlayphp/notifications-react';
import { Notifications } from '@inlayphp/notifications-react';
import { Panel } from '@inlayphp/panels-react';
import type { PanelResource } from '@inlayphp/panels-react';
import { LogOut, Moon, Sun } from 'lucide-react';
import { useMemo } from 'react';
import type { PropsWithChildren } from 'react';
import { inlayIcons } from '@/components/inlay-icons';
import { useAppearance } from '@/hooks/use-appearance';
import type { User } from '@/types';

type PanelPageProps = {
    auth: { user: User };
    inlayPanel: PanelResource;
    inlayPage?: Record<string, unknown>;
    inlayNotifications?: { all?: unknown; unread?: unknown; toasts?: unknown } | null;
    page?: Record<string, unknown>;
    resource?: Record<string, unknown>;
};

// Canonical breadcrumb: muted 12px parent, strong current segment. The current
// segment is derived from real page data when the page provides it (page.type)
// and otherwise from the navigation item matching the current URL.
const PAGE_TYPE_BREADCRUMBS: Record<string, string> = {
    dashboard: 'Dashboard',
    'shop-dashboard': 'Shop',
    'hr-dashboard': 'HR',
    'register-team': 'New team',
    settings: 'Settings',
    'account-settings': 'Account',
};

function currentBreadcrumbSegment(
    inlayPage: PanelPageProps['inlayPage'],
    inlayPanel: PanelResource,
): string {
    const type = inlayPage?.type;
    if (typeof type === 'string' && PAGE_TYPE_BREADCRUMBS[type]) {
        return PAGE_TYPE_BREADCRUMBS[type];
    }

    if (typeof window === 'undefined') return 'Administration';

    const pathname = window.location.pathname;
    const items = [
        ...inlayPanel.navigationItems,
        ...inlayPanel.navigationGroups.flatMap((group) => group.items),
    ];
    const active = items
        .filter((item) => item.url && pathname.startsWith(item.url))
        .sort((a, b) => (b.url?.length ?? 0) - (a.url?.length ?? 0))[0];

    return active?.label ?? 'Administration';
}

export default function InlayPanelLayout({ children }: PropsWithChildren) {
    const { auth, inlayPanel, inlayPage, inlayNotifications, page, resource } =
        usePage<PanelPageProps>().props;
    const { resolvedAppearance, updateAppearance } = useAppearance();

    const breadcrumbSegment = useMemo(
        () => currentBreadcrumbSegment(inlayPage, inlayPanel),
        [inlayPage, inlayPanel],
    );

    const markNotificationRead = (databaseId: string | number) => {
        router.post(
            `${inlayPanel.path}/notifications/mark-read`,
            { id: databaseId },
            { preserveScroll: true },
        );
    };

    const markAllNotificationsRead = () => {
        router.post(
            `${inlayPanel.path}/notifications/mark-all-read`,
            {},
            { preserveScroll: true },
        );
    };

    return (
        <>
            <Panel
                conditionValues={{ page: inlayPage ?? page, resource }}
                icons={inlayIcons}
                onNavigate={(href) => router.visit(href)}
                resource={inlayPanel}
                slots={{
                    headerStart: (
                        <nav
                            aria-label="Breadcrumb"
                            className="hidden items-center gap-2 text-xs text-(--inlay-muted) lg:flex"
                            data-slot="topbar-breadcrumb"
                        >
                            <span>Workspace</span>
                            <span aria-hidden="true">/</span>
                            <strong className="font-semibold text-(--inlay-panel-text)">
                                {breadcrumbSegment}
                            </strong>
                        </nav>
                    ),
                    sidebarFooter: ({ collapsed }) =>
                        collapsed ? (
                            <div
                                aria-label="Current workspace"
                                className="grid size-10 place-items-center self-center rounded-(--inlay-radius) border border-(--inlay-panel-sidebar-border) bg-(--inlay-panel-sidebar-hover) text-sm font-semibold text-(--inlay-panel-sidebar-active-foreground)"
                                data-slot="workspace-card"
                            >
                                I
                            </div>
                        ) : (
                            <div
                                className="rounded-(--inlay-radius) border border-(--inlay-panel-sidebar-border) bg-(--inlay-panel-sidebar-hover) px-3.5 py-[13px]"
                                data-slot="workspace-card"
                            >
                                <div className="flex items-center justify-between">
                                    <span className="text-[11px] text-(--inlay-panel-sidebar-muted)">
                                        Current workspace
                                    </span>
                                    <span
                                        aria-label="Connected"
                                        className="size-[7px] rounded-full bg-(--inlay-panel-success) shadow-[0_0_0_3px_var(--inlay-panel-success-surface)]"
                                    />
                                </div>
                                <p className="mt-[7px] text-[13px] font-semibold text-(--inlay-panel-sidebar-text)">
                                    {inlayPanel.brandName ?? 'Inlay'}
                                </p>
                                <p className="text-[11px] text-(--inlay-panel-sidebar-muted)">
                                    Production
                                </p>
                            </div>
                        ),
                    headerEnd: (
                        <div className="flex items-center gap-2">
                            <NotificationCenter
                                notifications={inlayNotifications?.all}
                                onMarkAllRead={markAllNotificationsRead}
                                onMarkRead={markNotificationRead}
                            />
                            <button
                                aria-label={`Use ${resolvedAppearance === 'dark' ? 'light' : 'dark'} theme`}
                                className="inline-flex size-10 items-center justify-center rounded-(--inlay-radius) border border-(--inlay-control-border) bg-(--inlay-surface) text-(--inlay-muted) transition hover:bg-(--inlay-hover) hover:text-(--inlay-text) focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(--inlay-accent)"
                                onClick={() =>
                                    updateAppearance(
                                        resolvedAppearance === 'dark'
                                            ? 'light'
                                            : 'dark',
                                    )
                                }
                                type="button"
                            >
                                {resolvedAppearance === 'dark' ? (
                                    <Sun
                                        aria-hidden="true"
                                        className="size-4"
                                        strokeWidth={1.8}
                                    />
                                ) : (
                                    <Moon
                                        aria-hidden="true"
                                        className="size-4"
                                        strokeWidth={1.8}
                                    />
                                )}
                            </button>
                            <div className="hidden items-center gap-3 border-l border-(--inlay-border) pl-3 sm:flex">
                                <div className="flex size-9 items-center justify-center rounded-full bg-(--inlay-accent) text-sm font-semibold text-(--inlay-accent-foreground)">
                                    {auth.user.name.slice(0, 1).toUpperCase()}
                                </div>
                                <div className="hidden min-w-0 text-left lg:block">
                                    <p className="max-w-44 truncate text-sm font-medium">
                                        {auth.user.name}
                                    </p>
                                    <p className="max-w-44 truncate text-xs text-(--inlay-muted)">
                                        {auth.user.email}
                                    </p>
                                </div>
                            </div>
                            <button
                                aria-label="Sign out"
                                className="inline-flex size-10 items-center justify-center rounded-(--inlay-radius) text-(--inlay-muted) transition hover:bg-(--inlay-hover) hover:text-(--inlay-text) focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(--inlay-accent)"
                                onClick={() =>
                                    router.post(`${inlayPanel.path}/logout`)
                                }
                                type="button"
                            >
                                <LogOut
                                    aria-hidden="true"
                                    className="size-4"
                                    strokeWidth={1.8}
                                />
                            </button>
                        </div>
                    ),
                }}
            >
                {children}
                <Notifications notifications={inlayNotifications?.toasts ?? []} />
            </Panel>
        </>
    );
}
