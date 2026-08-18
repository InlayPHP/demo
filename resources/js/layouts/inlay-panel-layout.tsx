import { router, usePage } from '@inertiajs/react';
import { Notifications } from '@inlayphp/notifications-react';
import { Panel } from '@inlayphp/panels-react';
import type { PanelResource } from '@inlayphp/panels-react';
import { LogOut, Moon, Sun } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { inlayIcons } from '@/components/inlay-icons';
import { useAppearance } from '@/hooks/use-appearance';
import type { User } from '@/types';

type PanelPageProps = {
    auth: { user: User };
    inlayPanel: PanelResource;
    inlayPage?: Record<string, unknown>;
    inlayNotifications?: unknown;
    page?: Record<string, unknown>;
    resource?: Record<string, unknown>;
};

export default function InlayPanelLayout({ children }: PropsWithChildren) {
    const { auth, inlayPanel, inlayPage, inlayNotifications, page, resource } =
        usePage<PanelPageProps>().props;
    const { resolvedAppearance, updateAppearance } = useAppearance();

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
                            <strong className="font-semibold text-(--inlay-text)">
                                Administration
                            </strong>
                        </nav>
                    ),
                    headerEnd: (
                        <div className="flex items-center gap-2">
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
            </Panel>
            <Notifications notifications={inlayNotifications} />
        </>
    );
}
