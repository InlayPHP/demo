import { Head, Link, usePage } from '@inertiajs/react';
import { Form } from '@inlayphp/forms-react';
import type { FormErrors, FormResource } from '@inlayphp/forms-react';
import { ArrowLeft, CheckCircle2 } from 'lucide-react';
import { standaloneRendererTheme } from '@/lib/inlay-demo-theme';

type PageProps = {
    form: FormResource;
    errors: FormErrors;
    flash: { success?: string | null };
};

export default function DemoForm() {
    const { errors, flash, form } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Forms demo" />
            <div
                className="min-h-dvh bg-neutral-50 text-neutral-950 antialiased dark:bg-neutral-950 dark:text-neutral-50"
                data-inlay-demo-theme
            >
                <div className="mx-auto flex min-h-dvh w-full max-w-5xl flex-col px-5 sm:px-8">
                    <header className="flex items-center justify-between gap-4 py-6">
                        <Link
                            href="/"
                            className="inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-neutral-700 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-600 dark:text-neutral-200 dark:hover:text-emerald-300"
                        >
                            <ArrowLeft aria-hidden="true" className="size-4" />
                            Back to Inlay
                        </Link>
                        <span className="text-sm text-neutral-500 dark:text-neutral-400">
                            @inlayphp/forms
                        </span>
                    </header>

                    <main className="flex flex-1 flex-col gap-8 py-10 sm:py-16">
                        <section className="flex max-w-3xl flex-col gap-4">
                            <p className="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                Real package demo
                            </p>
                            <h1 className="text-4xl font-semibold tracking-tight sm:text-5xl">
                                A form defined in PHP, rendered with React.
                            </h1>
                            <p className="text-lg text-pretty text-neutral-600 dark:text-neutral-300">
                                This page is served by an Inlay FormPage route.
                                The fields, action, and validation metadata come
                                from the installed Inlay package.
                            </p>
                        </section>

                        {flash.success ? (
                            <div className="flex items-center gap-3 rounded-2xl border border-emerald-700/15 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-300/15 dark:bg-emerald-400/10 dark:text-emerald-100">
                                <CheckCircle2
                                    aria-hidden="true"
                                    className="size-5 shrink-0 text-emerald-700 dark:text-emerald-300"
                                />
                                {flash.success}
                            </div>
                        ) : null}

                        <section className="rounded-3xl border border-neutral-950/10 bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-9 dark:border-white/10 dark:bg-neutral-900 dark:ring-white/5">
                            <Form
                                errors={errors}
                                resource={form}
                                theme={standaloneRendererTheme}
                            />
                        </section>
                    </main>
                </div>
            </div>
        </>
    );
}
