import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowUpRight,
    Braces,
    Github,
    Images,
    Settings2,
    Table2,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { InlayLogo } from '@/components/inlay-logo';
import InlayPanelLayout from '@/layouts/inlay-panel-layout';
import type { User } from '@/types';

const destinations: Array<{
    description: string;
    href: string;
    icon: LucideIcon;
    title: string;
    external?: boolean;
}> = [
    {
        title: 'Users',
        description:
            'Create, search, edit, and remove panel user accounts through one PHP resource.',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Forms',
        description:
            'Explore schema-driven fields, validation, layout, and submission.',
        href: '/demo/forms',
        icon: Braces,
        external: true,
    },
    {
        title: 'Tables',
        description:
            'Try server-side search, sorting, filtering, pagination, and actions.',
        href: '/demo/tables',
        icon: Table2,
        external: true,
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
    {
        title: 'Source code',
        description:
            'Inspect every PHP package, React adapter, test, and example.',
        href: 'https://github.com/InlayPHP/inlay',
        icon: Github,
        external: true,
    },
];

export default function Dashboard() {
    const { auth } = usePage<{ auth: { user: User } }>().props;

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
                            Manage users, account settings, and media from the
                            complete Inlay administration experience, or open a
                            standalone package demo below.
                        </p>
                    </div>
                    <span className="inline-flex w-fit items-center gap-2 rounded-full bg-(--inlay-success-surface) px-3 py-1.5 text-sm font-medium text-(--inlay-success)">
                        <span className="size-2 rounded-full bg-current" />
                        Demo online
                    </span>
                </header>

                <section className="grid gap-4 md:grid-cols-2">
                    {destinations.map(
                        ({
                            description,
                            external,
                            href,
                            icon: Icon,
                            title,
                        }) => {
                            const content = (
                                <>
                                    <div className="flex items-start justify-between gap-4">
                                        <span className="flex size-10 items-center justify-center rounded-(--inlay-radius) bg-(--inlay-accent)/10 text-(--inlay-accent)">
                                            <Icon
                                                aria-hidden="true"
                                                className="size-5"
                                                strokeWidth={1.8}
                                            />
                                        </span>
                                        <ArrowUpRight
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
                                </>
                            );
                            const className =
                                'block rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface) p-5 shadow-xs transition hover:border-(--inlay-accent)/35 hover:bg-(--inlay-hover) focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(--inlay-accent) sm:p-6';

                            return external ? (
                                <a
                                    className={className}
                                    href={href}
                                    key={title}
                                    rel="noreferrer"
                                    target="_blank"
                                >
                                    {content}
                                </a>
                            ) : (
                                <Link
                                    className={className}
                                    href={href}
                                    key={title}
                                >
                                    {content}
                                </Link>
                            );
                        },
                    )}
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
                                account settings, and resources are registered
                                from Laravel while React renders the package
                                contracts.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </InlayPanelLayout>
    );
}
