import { Head, Link, usePage } from '@inertiajs/react';
import { WidgetDashboard } from '@inlayphp/widgets-react';
import type { WidgetDashboardResource } from '@inlayphp/widgets-react';
import { ArrowRight, Images, Newspaper, Settings2, Users } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { inlayIcons } from '@/components/inlay-icons';
import { InlayLogo } from '@/components/inlay-logo';
import InlayPanelLayout from '@/layouts/inlay-panel-layout';
import type { User } from '@/types';

const destinations: Array<{
    description: string;
    href: string;
    icon: LucideIcon;
    title: string;
}> = [
    {
        title: 'Users',
        description:
            'Create, search, edit, and remove panel user accounts through one PHP resource.',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Blog posts',
        description:
            'Create, edit, publish, and search content with a complete resource.',
        href: '/admin/blogs',
        icon: Newspaper,
    },
    {
        title: 'Media library',
        description:
            'Upload, organize, browse, and securely deliver application media.',
        href: '/admin/media',
        icon: Images,
    },
    {
        title: 'Account settings',
        description: 'See the panel-native profile and password experience.',
        href: '/admin/settings/account',
        icon: Settings2,
    },
];

export default function Dashboard() {
    const { auth, inlayWidgets } = usePage<{
        auth: { user: User };
        inlayWidgets: WidgetDashboardResource;
    }>().props;

    return (
        <InlayPanelLayout>
            <Head title="Inlay panel" />
            <div className="mx-auto max-w-6xl space-y-8">
                <header className="flex flex-col gap-5 border-b border-(--inlay-border) pb-7 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="font-medium text-(--inlay-accent)">
                            Full feature demo
                        </p>
                        <h1 className="mt-2 text-3xl font-semibold tracking-tight">
                            Welcome, {auth.user.name}
                        </h1>
                        <p className="mt-2 max-w-2xl text-sm leading-6 text-(--inlay-muted)">
                            Manage users, blog posts, account settings, and
                            media from one Laravel panel. The widgets below are
                            resolved on the server.
                        </p>
                    </div>
                    <span className="inline-flex w-fit items-center gap-2 rounded-full bg-(--inlay-success-surface) px-3 py-1.5 text-sm font-medium text-(--inlay-success)">
                        <span className="size-2 rounded-full bg-current" />
                        Demo online
                    </span>
                </header>

                <WidgetDashboard icons={inlayIcons} resource={inlayWidgets} />

                <section className="space-y-4">
                    <div>
                        <h2 className="text-lg font-semibold">
                            Manage the demo
                        </h2>
                        <p className="mt-1 text-sm text-(--inlay-muted)">
                            Resources and account tools use the same panel
                            contracts.
                        </p>
                    </div>
                    <div className="grid gap-4 md:grid-cols-2">
                        {destinations.map(
                            ({ description, href, icon: Icon, title }) => (
                                <Link
                                    className="block rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface) p-5 shadow-xs transition hover:border-(--inlay-accent)/35 hover:bg-(--inlay-hover) focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(--inlay-accent) sm:p-6"
                                    href={href}
                                    key={title}
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <span className="flex size-10 items-center justify-center rounded-(--inlay-radius) bg-(--inlay-accent)/10 text-(--inlay-accent)">
                                            <Icon
                                                aria-hidden="true"
                                                className="size-5"
                                                strokeWidth={1.8}
                                            />
                                        </span>
                                        <ArrowRight
                                            aria-hidden="true"
                                            className="size-4 text-(--inlay-muted)"
                                            strokeWidth={1.8}
                                        />
                                    </div>
                                    <div className="mt-8">
                                        <h2 className="text-lg font-semibold">
                                            {title}
                                        </h2>
                                        <p className="mt-2 text-sm leading-6 text-(--inlay-muted)">
                                            {description}
                                        </p>
                                    </div>
                                </Link>
                            ),
                        )}
                    </div>
                </section>

                <section className="rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface-muted) p-6 sm:p-8">
                    <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <InlayLogo className="size-12 shrink-0" />
                        <div>
                            <h2 className="text-lg font-semibold">
                                One PHP panel provider controls this experience
                            </h2>
                            <p className="mt-1 text-sm leading-6 text-(--inlay-muted)">
                                Brand, theme, navigation, authentication,
                                account settings, widgets, and resources are
                                registered from Laravel while React renders the
                                package contracts.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </InlayPanelLayout>
    );
}
