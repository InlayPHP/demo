import { Head, Link, useForm } from '@inertiajs/react';
import type { PanelResource } from '@inlayphp/panels-react';
import { recipeVariables } from '@inlayphp/theme';
import {
    buttonPrimaryClass,
    controlClass,
    labelClass,
} from '@inlayphp/ui-react';
import { ArrowLeft, LockKeyhole } from 'lucide-react';
import type { CSSProperties, FormEvent } from 'react';
import { InlayLogo } from '@/components/inlay-logo';

type LoginProps = {
    demoCredentials: {
        email: string;
        password: string;
    };
    inlayPanel: PanelResource;
};

function value(
    source: Record<string, string | number | boolean | null>,
    key: string,
    fallback: string,
) {
    const resolved = source[key];

    return typeof resolved === 'string' || typeof resolved === 'number'
        ? String(resolved)
        : fallback;
}

function themeStyle(inlayPanel: PanelResource): CSSProperties {
    const light = inlayPanel.theme;
    const dark = { ...light, ...(inlayPanel.darkTheme ?? {}) };
    const recipes = recipeVariables({
        contract: 'inlay.themes.v1',
        name: inlayPanel.themeName ?? 'default',
        tokens: light,
        darkTokens: inlayPanel.darkTheme ?? {},
    });
    const semanticTokens = new Set([
        'accent',
        'accent-foreground',
        'background',
        'surface',
        'surface-muted',
        'foreground',
        'muted',
        'border',
        'control-border',
        'hover',
        'badge',
        'danger',
        'danger-surface',
        'success',
        'success-surface',
        'warning',
        'warning-surface',
        'info',
        'info-surface',
        'overlay',
        'scrim',
    ]);
    const recipeOnly = Object.fromEntries(
        Object.entries(recipes).filter(
            ([name]) => !semanticTokens.has(name.replace('--inlay-', '')),
        ),
    );

    return {
        ...recipeOnly,
        '--inlay-light-accent': value(light, 'accent', '#047857'),
        '--inlay-light-accent-foreground': value(
            light,
            'accent-foreground',
            '#ffffff',
        ),
        '--inlay-light-background': value(light, 'background', '#f6f7fb'),
        '--inlay-light-surface': value(light, 'surface', '#ffffff'),
        '--inlay-light-surface-muted': value(light, 'surface-muted', '#f4f4f5'),
        '--inlay-light-text': value(light, 'foreground', '#18181b'),
        '--inlay-light-muted': value(light, 'muted', '#71717a'),
        '--inlay-light-border': value(light, 'border', 'rgb(24 24 27 / 0.12)'),
        '--inlay-light-control-border': value(
            light,
            'control-border',
            '#d4d4d8',
        ),
        '--inlay-light-hover': value(light, 'hover', '#f4f4f5'),
        '--inlay-light-danger': value(light, 'danger', '#dc2626'),
        '--inlay-dark-accent': value(dark, 'accent', '#34d399'),
        '--inlay-dark-accent-foreground': value(
            dark,
            'accent-foreground',
            '#052e16',
        ),
        '--inlay-dark-background': value(dark, 'background', '#09090b'),
        '--inlay-dark-surface': value(dark, 'surface', '#18181b'),
        '--inlay-dark-surface-muted': value(dark, 'surface-muted', '#27272a'),
        '--inlay-dark-text': value(dark, 'foreground', '#fafafa'),
        '--inlay-dark-muted': value(dark, 'muted', '#a1a1aa'),
        '--inlay-dark-border': value(dark, 'border', 'rgb(255 255 255 / 0.12)'),
        '--inlay-dark-control-border': value(
            dark,
            'control-border',
            'rgb(255 255 255 / 0.2)',
        ),
        '--inlay-dark-hover': value(dark, 'hover', '#27272a'),
        '--inlay-dark-danger': value(dark, 'danger', '#f87171'),
        '--inlay-radius': value(light, 'radius', '0.75rem'),
        '--inlay-control-height': value(light, 'control-height', '2.5rem'),
        '--inlay-button-height': value(light, 'button-height', '2.5rem'),
        '--inlay-font-family': value(
            light,
            'font-family',
            'ui-sans-serif, system-ui, sans-serif',
        ),
    } as CSSProperties;
}

export default function Login({ demoCredentials, inlayPanel }: LoginProps) {
    const form = useForm({
        email: demoCredentials.email,
        password: demoCredentials.password,
        remember: false,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(`${inlayPanel.path}/login`);
    }

    return (
        <>
            <Head title="Sign in to Inlay" />
            <main
                className="min-h-dvh bg-(--inlay-background) font-[family-name:var(--inlay-font-family)] text-(--inlay-text) antialiased [--inlay-accent-foreground:var(--inlay-light-accent-foreground)] [--inlay-accent:var(--inlay-light-accent)] [--inlay-background:var(--inlay-light-background)] [--inlay-border:var(--inlay-light-border)] [--inlay-control-border:var(--inlay-light-control-border)] [--inlay-danger:var(--inlay-light-danger)] [--inlay-hover:var(--inlay-light-hover)] [--inlay-muted:var(--inlay-light-muted)] [--inlay-surface-muted:var(--inlay-light-surface-muted)] [--inlay-surface:var(--inlay-light-surface)] dark:[--inlay-accent-foreground:var(--inlay-dark-accent-foreground)] dark:[--inlay-accent:var(--inlay-dark-accent)] dark:[--inlay-background:var(--inlay-dark-background)] dark:[--inlay-border:var(--inlay-dark-border)] dark:[--inlay-control-border:var(--inlay-dark-control-border)] dark:[--inlay-danger:var(--inlay-dark-danger)] dark:[--inlay-hover:var(--inlay-dark-hover)] dark:[--inlay-muted:var(--inlay-dark-muted)] dark:[--inlay-surface-muted:var(--inlay-dark-surface-muted)] dark:[--inlay-surface:var(--inlay-dark-surface)] dark:[--inlay-text:var(--inlay-dark-text)]"
                style={themeStyle(inlayPanel)}
            >
                <div className="grid min-h-dvh lg:grid-cols-[minmax(0,1.1fr)_minmax(28rem,0.9fr)]">
                    <section className="relative hidden overflow-hidden border-r border-(--inlay-border) bg-(--inlay-surface-muted) p-12 lg:flex lg:flex-col lg:justify-between xl:p-16">
                        <div className="absolute -top-32 -left-24 size-96 rounded-full bg-(--inlay-accent)/12 blur-3xl" />
                        <Link
                            className="relative inline-flex w-fit items-center gap-3 text-lg font-semibold"
                            href="/"
                        >
                            <InlayLogo className="size-11 shrink-0" />
                            Inlay
                        </Link>
                        <div className="relative max-w-xl">
                            <p className="font-medium text-(--inlay-accent)">
                                Laravel-native administration
                            </p>
                            <h1 className="mt-4 text-4xl font-semibold tracking-tight text-balance xl:text-5xl">
                                Build the interface from PHP. Keep the frontend
                                cohesive.
                            </h1>
                            <p className="mt-5 max-w-[54ch] text-lg leading-8 text-(--inlay-muted)">
                                This login, panel shell, navigation, theme, and
                                account experience are all provided through the
                                Inlay package contracts.
                            </p>
                        </div>
                        <p className="relative text-sm text-(--inlay-muted)">
                            Laravel 13 · Inertia 3 · React
                        </p>
                    </section>

                    <section className="flex min-h-dvh items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
                        <div className="w-full max-w-md">
                            <Link
                                className="mb-10 inline-flex items-center gap-2 text-sm font-medium text-(--inlay-muted) transition hover:text-(--inlay-text) focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-(--inlay-accent) lg:hidden"
                                href="/"
                            >
                                <ArrowLeft
                                    aria-hidden="true"
                                    className="size-4"
                                    strokeWidth={1.8}
                                />
                                Back to demo
                            </Link>

                            <div className="mb-8 flex items-start gap-4">
                                <span className="flex size-11 shrink-0 items-center justify-center rounded-(--inlay-radius) bg-(--inlay-accent)/10 text-(--inlay-accent)">
                                    <LockKeyhole
                                        aria-hidden="true"
                                        className="size-5"
                                        strokeWidth={1.8}
                                    />
                                </span>
                                <div>
                                    <h2 className="text-2xl font-semibold tracking-tight">
                                        Sign in to {inlayPanel.brandName}
                                    </h2>
                                    <p className="mt-1 text-sm leading-6 text-(--inlay-muted)">
                                        The demo account is ready to use.
                                    </p>
                                </div>
                            </div>

                            <form className="space-y-5" onSubmit={submit}>
                                <Field
                                    error={form.errors.email}
                                    label="Email address"
                                >
                                    <input
                                        aria-invalid={Boolean(
                                            form.errors.email,
                                        )}
                                        autoComplete="email"
                                        autoFocus
                                        className={controlClass}
                                        onChange={(event) =>
                                            form.setData(
                                                'email',
                                                event.target.value,
                                            )
                                        }
                                        type="email"
                                        value={form.data.email}
                                    />
                                </Field>
                                <Field
                                    error={form.errors.password}
                                    label="Password"
                                >
                                    <input
                                        aria-invalid={Boolean(
                                            form.errors.password,
                                        )}
                                        autoComplete="current-password"
                                        className={controlClass}
                                        onChange={(event) =>
                                            form.setData(
                                                'password',
                                                event.target.value,
                                            )
                                        }
                                        type="password"
                                        value={form.data.password}
                                    />
                                </Field>
                                <label className="flex min-h-10 items-center gap-3 text-sm text-(--inlay-muted)">
                                    <input
                                        checked={form.data.remember}
                                        className="size-4 rounded border-(--inlay-control-border) accent-(--inlay-accent)"
                                        onChange={(event) =>
                                            form.setData(
                                                'remember',
                                                event.target.checked,
                                            )
                                        }
                                        type="checkbox"
                                    />
                                    Remember me
                                </label>
                                <button
                                    className={`${buttonPrimaryClass} w-full`}
                                    disabled={form.processing}
                                    type="submit"
                                >
                                    {form.processing
                                        ? 'Signing in…'
                                        : 'Enter the Inlay panel'}
                                </button>
                            </form>

                            <div className="mt-8 rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface-muted) p-4">
                                <p className="text-sm font-medium">
                                    Default demo credentials
                                </p>
                                <dl className="mt-3 grid gap-2 text-sm text-(--inlay-muted)">
                                    <div className="grid grid-cols-[5rem_1fr] gap-3">
                                        <dt>Email</dt>
                                        <dd className="min-w-0 font-mono break-all">
                                            {demoCredentials.email}
                                        </dd>
                                    </div>
                                    <div className="grid grid-cols-[5rem_1fr] gap-3">
                                        <dt>Password</dt>
                                        <dd className="min-w-0 font-mono break-all">
                                            {demoCredentials.password}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}

function Field({
    children,
    error,
    label,
}: {
    children: React.ReactNode;
    error?: string;
    label: string;
}) {
    return (
        <label className="block space-y-2">
            <span className={labelClass}>{label}</span>
            {children}
            {error ? (
                <span className="block text-sm text-(--inlay-danger)">
                    {error}
                </span>
            ) : null}
        </label>
    );
}
