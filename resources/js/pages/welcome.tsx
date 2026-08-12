import { Head, Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    Braces,
    Github,
    LayoutDashboard,
    LogIn,
    Sparkles,
    Table2,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { InlayLogo } from '@/components/inlay-logo';

type DemoCard = {
    eyebrow: string;
    title: string;
    description: string;
    action: string;
    href: string;
    icon: LucideIcon;
    external?: boolean;
};

type Props = {
    demoCredentials: {
        email: string;
        password: string;
    };
};

const demoCards: DemoCard[] = [
    {
        eyebrow: 'Open source',
        title: 'GitHub',
        description:
            'Read the source, follow the roadmap, and see how the packages fit together.',
        action: 'View repository',
        href: 'https://github.com/InlayPHP/inlay',
        icon: Github,
        external: true,
    },
    {
        eyebrow: 'Schema-driven',
        title: 'Forms',
        description:
            'Build clear, validated Laravel forms with reusable fields and predictable layouts.',
        action: 'Open form demo',
        href: '/demo/forms',
        icon: Braces,
    },
    {
        eyebrow: 'Data workflows',
        title: 'Tables',
        description:
            'Explore searchable, filterable tables designed for real application data.',
        action: 'Open table demo',
        href: '/demo/tables',
        icon: Table2,
    },
    {
        eyebrow: 'Complete experience',
        title: 'Full feature demo',
        description:
            'See the complete administration experience: navigation, resources, actions, and themes.',
        action: 'Explore full demo',
        href: '/admin',
        icon: LayoutDashboard,
    },
];

function CardAction({
    children,
    className,
    demo,
}: {
    children: ReactNode;
    className?: string;
    demo: DemoCard;
}) {
    const sharedProps = {
        className,
        target: demo.external ? '_blank' : undefined,
        rel: demo.external ? 'noreferrer' : undefined,
    };

    if (demo.external) {
        return (
            <a href={demo.href} {...sharedProps}>
                {children}
            </a>
        );
    }

    return (
        <Link href={demo.href} {...sharedProps}>
            {children}
        </Link>
    );
}

export default function Welcome({ demoCredentials }: Props) {
    return (
        <>
            <Head title="Inlay demo" />
            <div className="min-h-dvh bg-neutral-50 text-neutral-950 antialiased dark:bg-neutral-950 dark:text-neutral-50">
                <div className="relative isolate min-h-dvh overflow-hidden">
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[34rem] bg-linear-to-br from-emerald-100/80 via-amber-50/60 to-transparent dark:from-emerald-950/35 dark:via-amber-950/20 dark:to-transparent"
                    />
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute top-24 right-[-8rem] -z-10 size-72 rounded-full bg-amber-300/20 blur-3xl dark:bg-amber-500/10"
                    />

                    <header className="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-5 py-6 sm:px-8 lg:px-10">
                        <Link
                            href="/"
                            aria-label="Homepage"
                            className="group inline-flex items-center gap-3"
                        >
                            <InlayLogo className="size-10 shrink-0 transition-transform duration-200 group-hover:-translate-y-0.5" />
                            <span className="text-lg font-semibold tracking-tight">
                                Inlay
                            </span>
                        </Link>

                        <div className="flex items-center gap-2">
                            <Link
                                href="/admin/login"
                                className="inline-flex min-h-10 items-center gap-2 rounded-xl border border-neutral-950/10 bg-white/70 py-2 pr-3 pl-2 text-base font-medium text-neutral-800 ring-1 ring-black/5 hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:text-sm dark:border-white/10 dark:bg-neutral-900/70 dark:text-neutral-100 dark:ring-white/5 dark:hover:bg-neutral-900"
                            >
                                <LogIn
                                    aria-hidden="true"
                                    className="size-4 shrink-0"
                                />
                                <span>Log in</span>
                            </Link>
                            <a
                                href="https://github.com/InlayPHP/inlay"
                                target="_blank"
                                rel="noreferrer"
                                aria-label="View Inlay on GitHub"
                                className="relative inline-flex size-10 items-center justify-center rounded-xl border border-neutral-950/10 bg-white/70 text-neutral-800 ring-1 ring-black/5 hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 dark:border-white/10 dark:bg-neutral-900/70 dark:text-neutral-100 dark:ring-white/5 dark:hover:bg-neutral-900"
                            >
                                <Github
                                    aria-hidden="true"
                                    className="size-4 shrink-0"
                                />
                                <span
                                    aria-hidden="true"
                                    className="pointer-events-none absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden"
                                />
                            </a>
                        </div>
                    </header>

                    <main className="mx-auto flex w-full max-w-7xl flex-col gap-16 px-5 pb-12 sm:px-8 sm:pb-16 lg:gap-20 lg:px-10 lg:pb-20">
                        <section className="flex flex-col items-center gap-7 pt-12 text-center sm:pt-16 lg:pt-20">
                            <div className="inline-flex min-h-8 items-center gap-2 rounded-full border border-emerald-700/15 bg-white/70 px-3 py-1.5 text-sm font-medium text-emerald-800 ring-1 ring-black/5 dark:border-emerald-300/15 dark:bg-neutral-900/70 dark:text-emerald-300 dark:ring-white/5">
                                <Sparkles
                                    aria-hidden="true"
                                    className="size-4 shrink-0"
                                />
                                <span>Laravel 13 · Inertia 3 · React</span>
                            </div>
                            <div className="flex flex-col items-center gap-5">
                                <h1 className="max-w-[18ch] text-5xl font-semibold tracking-tight text-balance sm:text-6xl">
                                    Laravel interfaces, thoughtfully composed.
                                </h1>
                                <p className="max-w-[58ch] text-lg text-pretty text-neutral-600 dark:text-neutral-300">
                                    Inlay brings reusable forms, tables, panels,
                                    and resources together into a calm, flexible
                                    admin foundation.
                                </p>
                            </div>
                            <div className="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                                <Link
                                    href="/admin"
                                    className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-emerald-700 hover:bg-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-emerald-500 dark:text-neutral-950 dark:ring-emerald-500 dark:hover:bg-emerald-400"
                                >
                                    <span>Explore the full demo</span>
                                    <ArrowUpRight
                                        aria-hidden="true"
                                        className="size-4 shrink-0"
                                    />
                                </Link>
                                <a
                                    href="https://github.com/InlayPHP/inlay"
                                    target="_blank"
                                    rel="noreferrer"
                                    className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-neutral-950/10 bg-white/70 px-4 py-2.5 text-sm font-medium text-neutral-800 ring-1 ring-black/5 hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 dark:border-white/10 dark:bg-neutral-900/70 dark:text-neutral-100 dark:ring-white/5 dark:hover:bg-neutral-900"
                                >
                                    <Github
                                        aria-hidden="true"
                                        className="size-4 shrink-0"
                                    />
                                    <span>Read the source</span>
                                </a>
                            </div>
                            <div className="grid w-full max-w-2xl gap-4 rounded-2xl border border-neutral-950/10 bg-white/70 p-4 text-left ring-1 ring-black/5 sm:grid-cols-[1fr_auto] sm:items-center sm:p-5 dark:border-white/10 dark:bg-neutral-900/70 dark:ring-white/5">
                                <div className="min-w-0">
                                    <p className="font-medium text-neutral-950 dark:text-white">
                                        Default demo account
                                    </p>
                                    <p className="text-base/7 text-pretty text-neutral-600 sm:text-sm/6 dark:text-neutral-300">
                                        The login form is prefilled so you can
                                        enter the workspace immediately.
                                    </p>
                                </div>
                                <dl className="grid min-w-0 gap-2 text-base/7 sm:text-sm/6">
                                    <div className="grid min-w-0 grid-cols-[4.5rem_1fr] gap-3">
                                        <dt className="font-medium text-neutral-950 dark:text-white">
                                            Email
                                        </dt>
                                        <dd className="min-w-0 break-all text-neutral-600 dark:text-neutral-300">
                                            <code>{demoCredentials.email}</code>
                                        </dd>
                                    </div>
                                    <div className="grid min-w-0 grid-cols-[4.5rem_1fr] gap-3">
                                        <dt className="font-medium text-neutral-950 dark:text-white">
                                            Password
                                        </dt>
                                        <dd className="min-w-0 break-all text-neutral-600 dark:text-neutral-300">
                                            <code>
                                                {demoCredentials.password}
                                            </code>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </section>

                        <section
                            aria-labelledby="demo-paths-heading"
                            className="flex flex-col gap-8"
                        >
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-6">
                                <div className="flex flex-col gap-2">
                                    <p className="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                        Choose a path
                                    </p>
                                    <h2
                                        id="demo-paths-heading"
                                        className="max-w-[35ch] text-3xl font-semibold tracking-tight text-balance"
                                    >
                                        Start with the part you want to see.
                                    </h2>
                                </div>
                                <p className="max-w-[42ch] text-base text-pretty text-neutral-600 dark:text-neutral-300">
                                    A focused look at the building blocks,
                                    followed by one complete application
                                    experience. CMS features are intentionally
                                    outside this demo.
                                </p>
                            </div>

                            <dl className="grid gap-4 md:grid-cols-2">
                                {demoCards.map((demo) => {
                                    const Icon = demo.icon;

                                    return (
                                        <div
                                            key={demo.title}
                                            className="group flex min-h-64 flex-col rounded-3xl border border-neutral-950/10 bg-white/80 p-6 ring-1 ring-black/5 dark:border-white/10 dark:bg-neutral-900/70 dark:ring-white/5"
                                        >
                                            <div className="flex items-start justify-between gap-4">
                                                <div className="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-neutral-950 text-emerald-300 dark:bg-white dark:text-emerald-700">
                                                    <Icon
                                                        aria-hidden="true"
                                                        className="size-5 shrink-0"
                                                    />
                                                </div>
                                                <CardAction
                                                    demo={demo}
                                                    className="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-neutral-950/10 text-neutral-500 hover:bg-neutral-100 hover:text-neutral-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 dark:border-white/10 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white"
                                                >
                                                    <ArrowUpRight
                                                        aria-hidden="true"
                                                        className="size-4 shrink-0"
                                                    />
                                                    <span className="sr-only">
                                                        {demo.action}
                                                    </span>
                                                </CardAction>
                                            </div>
                                            <dt className="mt-8 text-xl font-semibold tracking-tight">
                                                <CardAction
                                                    demo={demo}
                                                    className="rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-600"
                                                >
                                                    {demo.title}
                                                </CardAction>
                                            </dt>
                                            <dd className="mt-2 max-w-[42ch] text-base text-pretty text-neutral-600 dark:text-neutral-300">
                                                {demo.description}
                                            </dd>
                                            <CardAction
                                                demo={demo}
                                                className="mt-auto inline-flex items-center gap-2 pt-8 text-sm font-semibold text-emerald-700 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-600 dark:text-emerald-300 dark:hover:text-emerald-200"
                                            >
                                                <span>{demo.action}</span>
                                                <ArrowUpRight
                                                    aria-hidden="true"
                                                    className="size-4 shrink-0 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                                />
                                            </CardAction>
                                        </div>
                                    );
                                })}
                            </dl>
                        </section>

                        <section className="flex flex-col gap-6 rounded-3xl border border-neutral-950/10 bg-neutral-950 p-6 text-white ring-1 ring-black/5 sm:flex-row sm:items-center sm:justify-between sm:p-8 dark:border-white/10 dark:bg-neutral-900 dark:ring-white/5">
                            <div className="flex min-w-0 flex-col gap-2">
                                <p className="text-sm font-medium text-emerald-300">
                                    Built for Laravel teams
                                </p>
                                <h2 className="max-w-[32ch] text-2xl font-semibold tracking-tight text-balance">
                                    Start small, then compose the whole
                                    workspace.
                                </h2>
                                <p className="max-w-[58ch] text-base text-pretty text-neutral-300">
                                    The demo is a Laravel 13 application with
                                    Boost enabled, ready to grow alongside the
                                    Inlay packages.
                                </p>
                            </div>
                            <Link
                                href="/demo/full-feature"
                                className="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-neutral-950 hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300"
                            >
                                <span>See the complete path</span>
                                <ArrowUpRight
                                    aria-hidden="true"
                                    className="size-4 shrink-0"
                                />
                            </Link>
                        </section>
                    </main>

                    <footer className="mx-auto flex w-full max-w-7xl flex-col gap-3 border-t border-neutral-950/10 px-5 py-6 text-sm text-neutral-500 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10 dark:border-white/10 dark:text-neutral-400">
                        <p>Inlay is open source and built for Laravel.</p>
                        <a
                            href="https://cloud.laravel.com"
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex items-center gap-1.5 font-medium text-neutral-700 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-600 dark:text-neutral-200 dark:hover:text-emerald-300"
                        >
                            <span>Deploy with Laravel Cloud</span>
                            <ArrowUpRight
                                aria-hidden="true"
                                className="size-4 shrink-0"
                            />
                        </a>
                    </footer>
                </div>
            </div>
        </>
    );
}
