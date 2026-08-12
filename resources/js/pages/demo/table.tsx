import { Head, Link, usePage } from '@inertiajs/react';
import { Table } from '@inlayphp/tables-react';
import type { TableResource } from '@inlayphp/tables-react';
import { ArrowLeft } from 'lucide-react';
import { standaloneRendererTheme } from '@/lib/inlay-demo-theme';

type PageProps = {
    table: TableResource;
};

export default function DemoTable() {
    const { table } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Tables demo" />
            <div
                className="min-h-dvh bg-neutral-50 text-neutral-950 antialiased dark:bg-neutral-950 dark:text-neutral-50"
                data-inlay-demo-theme
            >
                <div className="mx-auto flex min-h-dvh w-full max-w-7xl flex-col px-5 sm:px-8 lg:px-10">
                    <header className="flex items-center justify-between gap-4 py-6">
                        <Link
                            href="/"
                            className="inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-neutral-700 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-600 dark:text-neutral-200 dark:hover:text-emerald-300"
                        >
                            <ArrowLeft aria-hidden="true" className="size-4" />
                            Back to Inlay
                        </Link>
                        <span className="text-sm text-neutral-500 dark:text-neutral-400">
                            @inlayphp/tables
                        </span>
                    </header>

                    <main className="flex flex-1 flex-col gap-8 py-10 sm:py-16">
                        <section className="flex max-w-3xl flex-col gap-4">
                            <p className="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                Real package demo
                            </p>
                            <h1 className="text-4xl font-semibold tracking-tight sm:text-5xl">
                                Server-side data workflows, rendered in React.
                            </h1>
                            <p className="text-lg text-pretty text-neutral-600 dark:text-neutral-300">
                                Search, sort, paginate, and manage the user
                                query through a standalone Inlay TablePage.
                            </p>
                        </section>

                        <section className="min-w-0 rounded-3xl border border-neutral-950/10 bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-7 dark:border-white/10 dark:bg-neutral-900 dark:ring-white/5">
                            <Table
                                resource={table}
                                theme={standaloneRendererTheme}
                            />
                        </section>
                    </main>
                </div>
            </div>
        </>
    );
}
