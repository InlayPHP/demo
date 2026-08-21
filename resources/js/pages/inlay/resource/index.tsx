import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    executeActionEndpoint,
    interpolateActionUrl,
    normalizeAction,
} from '@inlayphp/actions';
import type { FormErrors, FormResource } from '@inlayphp/forms-react';
import { RelationDialog, ResourcePage } from '@inlayphp/resources-react';
import type {
    ResourceBreadcrumb,
    ResourceSubNavigationItem,
    ResourceTabsResource,
} from '@inlayphp/resources-react';
import { Table } from '@inlayphp/tables-react';
import type {
    Action,
    BulkSelectionState,
    IconRenderer,
    TableRendererRegistries,
    TableResource,
    TableRow,
} from '@inlayphp/tables-react';
import type { WidgetDashboardResource } from '@inlayphp/widgets-react';
import { Plus, Upload, X } from 'lucide-react';
import { useMemo, useRef, useState } from 'react';
import type { ChangeEvent } from 'react';
import { inlayIcons } from '@/components/inlay-icons';
import InlayPanelLayout from '@/layouts/inlay-panel-layout';

type PageRoute = { url: string | null; operation: string };
type ResourceContract = {
    label: string;
    pages: Record<string, PageRoute>;
    pluralLabel: string;
    slug: string;
};
type PageProps = {
    breadcrumbs?: ResourceBreadcrumb[];
    createForm?: FormResource | null;
    errors?: FormErrors;
    heading: string;
    headerWidgets?: WidgetDashboardResource | null;
    importEndpoint?: string | null;
    importLabel?: string | null;
    inlayPanel: {
        darkTheme?: Record<string, string | number>;
        id: string;
        theme?: Record<string, string | number>;
        themeName?: string | null;
    };
    resource: ResourceContract;
    subNavigation?: ResourceSubNavigationItem[];
    subheading?: string | null;
    table: TableResource;
    tabs?: ResourceTabsResource | null;
};

/**
 * Manage-style list pages (Authors, Categories, Departments) publish a
 * `createForm` alongside the table. When that prop is present the page runs
 * in "manage mode": create/edit happen in modals, external link actions open
 * in a new tab, and a CSV import modal is offered when `importEndpoint` is
 * set. Resources with a regular create page keep the plain link behaviour.
 */
type ModalState =
    | { mode: 'create' }
    | { mode: 'edit'; record: TableRow }
    | { mode: 'view'; record: TableRow }
    | { mode: 'import' }
    | null;

// Delegate every table icon name to the shared host registry, so row actions,
// triggers, and badges resolve the same icons the rest of the panel draws.
const iconRegistries: TableRendererRegistries = {
    icon: {
        get: (name) => inlayIcons[name] as IconRenderer | undefined,
    },
};

export default function ResourceIndex({
    breadcrumbs = [],
    createForm = null,
    heading,
    headerWidgets = null,
    importEndpoint = null,
    importLabel = 'Import',
    resource,
    subheading,
    subNavigation = [],
    table,
    tabs = null,
}: PageProps) {
    const { errors, inlayPanel } = usePage<PageProps>().props;
    const createUrl = resource.pages.create?.url;
    const manageMode = createForm !== null;
    const [modal, setModal] = useState<ModalState>(null);
    const [processing, setProcessing] = useState(false);
    const fileInput = useRef<HTMLInputElement>(null);

    const theme = {
        contract: 'inlay.themes.v1' as const,
        name: inlayPanel.themeName ?? inlayPanel.id,
        tokens: inlayPanel.theme ?? {},
        darkTokens: inlayPanel.darkTheme ?? {},
    };

    const baseUrl = createForm?.action?.replace(/\/+$/, '') ?? '';

    const dialogForm = useMemo<FormResource | null>(() => {
        if (
            !createForm ||
            !modal ||
            modal.mode === 'view' ||
            modal.mode === 'import'
        ) {
            return null;
        }

        if (modal.mode === 'create') {
            return {
                ...createForm,
                name: `${createForm.name}.create`,
                submitLabel: `Create ${resource.label}`,
            };
        }

        return {
            ...createForm,
            name: `${createForm.name}.edit`,
            action: `${baseUrl}/${modal.record.id}`,
            method: 'patch' as const,
            data: { ...createForm.data, ...modal.record },
            submitLabel: `Save ${resource.label}`,
        };
    }, [baseUrl, createForm, modal, resource.label]);

    const submit = async (data: Record<string, unknown>) => {
        if (!dialogForm?.action) {
            return;
        }

        setProcessing(true);

        try {
            router.visit(dialogForm.action, {
                method: dialogForm.method,
                data: data as never,
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            });
        } catch {
            setProcessing(false);
        }
    };

    const submitImport = () => {
        const file = fileInput.current?.files?.[0];

        if (!file || !importEndpoint) {
            return;
        }

        setProcessing(true);
        router.post(
            importEndpoint,
            { file },
            {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    const handleAction = async (
        action: Action,
        rows: TableRow[],
        selection?: BulkSelectionState,
    ) => {
        const row = rows[0] ?? {};

        if (action.name === 'edit') {
            setModal({ mode: 'edit', record: row });

            return;
        }

        if (action.name === 'view') {
            setModal({ mode: 'view', record: row });

            return;
        }

        if (action.name === 'view_github' || action.name === 'view_twitter') {
            const url = interpolateActionUrl(action.url, row);

            if (url) {
                window.open(url, '_blank', 'noopener,noreferrer');
            }

            return;
        }

        if (action.lifecycle && action.url) {
            const data = action.bulk
                ? selection
                    ? { selection }
                    : {
                          records: rows.map(
                              (candidate) => candidate[table.primaryKey],
                          ),
                      }
                : (action.data ?? {});
            await executeActionEndpoint({
                action: normalizeAction(action),
                url: interpolateActionUrl(action.url, row) ?? '',
                input: { parameters: row, records: rows, data },
            });

            return;
        }

        if (action.url) {
            const url = interpolateActionUrl(action.url, row);

            if (url) {
                router.visit(url, {
                    method: action.method as never,
                    data: (action.data ?? {}) as never,
                });
            }
        }
    };

    const modalHeading =
        modal?.mode === 'edit'
            ? `Edit ${resource.label}`
            : modal?.mode === 'view'
              ? resource.label
              : modal?.mode === 'create'
                ? `Create ${resource.label}`
                : importLabel;

    return (
        <InlayPanelLayout>
            <Head title={heading} />
            <ResourcePage
                actions={
                    manageMode ? (
                        <div className="flex flex-wrap items-center gap-2">
                            {importEndpoint ? (
                                <button
                                    className="inline-flex min-h-(--inlay-button-height) items-center justify-center gap-2 rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface) px-4 py-2 text-sm font-semibold text-(--inlay-text) transition hover:bg-(--inlay-surface-muted)"
                                    onClick={() => setModal({ mode: 'import' })}
                                    type="button"
                                >
                                    <Upload
                                        aria-hidden="true"
                                        className="size-4"
                                    />
                                    {importLabel}
                                </button>
                            ) : null}
                            <button
                                className="inline-flex min-h-(--inlay-button-height) items-center justify-center gap-2 rounded-(--inlay-radius) bg-(--inlay-accent) px-4 py-2 text-sm font-semibold text-(--inlay-accent-foreground) transition hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(--inlay-accent)"
                                onClick={() => setModal({ mode: 'create' })}
                                type="button"
                            >
                                <Plus aria-hidden="true" className="size-4" />
                                Create {resource.label}
                            </button>
                        </div>
                    ) : createUrl ? (
                        <Link
                            className="inline-flex min-h-(--inlay-button-height) items-center justify-center gap-2 rounded-(--inlay-radius) bg-(--inlay-accent) px-4 py-2 text-sm font-semibold text-(--inlay-accent-foreground) transition hover:opacity-90"
                            href={createUrl}
                        >
                            <Plus aria-hidden="true" className="size-4" />{' '}
                            Create {resource.label.toLowerCase()}
                        </Link>
                    ) : null
                }
                breadcrumbs={breadcrumbs}
                className="mx-auto w-full max-w-[1600px]"
                headerWidgets={headerWidgets}
                heading={heading}
                subNavigation={subNavigation}
                subheading={
                    subheading ??
                    (manageMode
                        ? `Manage ${resource.pluralLabel.toLowerCase()} without leaving the list. Create and edit happen in modals.`
                        : `Explore the ${resource.pluralLabel.toLowerCase()} resource. Every field, filter, action, and validation rule is defined in Laravel.`)
                }
                tabs={tabs}
                onTabSelect={(tab) =>
                    router.get(
                        window.location.pathname,
                        { tab },
                        { preserveState: true, replace: true },
                    )
                }
            >
                <section className="min-w-0 rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface) p-4 sm:p-6">
                    <Table
                        onAction={manageMode ? handleAction : undefined}
                        registries={iconRegistries}
                        resource={table}
                        theme={theme}
                    />
                </section>
            </ResourcePage>

            {manageMode &&
            (modal?.mode === 'create' || modal?.mode === 'edit') ? (
                <RelationDialog
                    errors={errors}
                    form={dialogForm ?? createForm}
                    heading={modalHeading ?? ''}
                    name={createForm?.name ?? 'manage'}
                    onClose={() => setModal(null)}
                    onSubmit={submit}
                    processing={processing}
                />
            ) : null}

            {manageMode && modal?.mode === 'view' ? (
                <ViewRecordModal
                    heading={modalHeading ?? ''}
                    onClose={() => setModal(null)}
                    record={modal.record}
                    resource={resource}
                />
            ) : null}

            {manageMode && modal?.mode === 'import' ? (
                <ImportModal
                    heading={modalHeading ?? ''}
                    onClose={() => setModal(null)}
                    onSubmit={submitImport}
                    processing={processing}
                    ref={fileInput}
                />
            ) : null}
        </InlayPanelLayout>
    );
}

function ViewRecordModal({
    heading,
    record,
    onClose,
    resource,
}: {
    heading: string;
    record: TableRow;
    onClose: () => void;
    resource: ResourceContract;
}) {
    const rows: Array<{
        label: string;
        value: string | null;
        placeholder?: string;
    }> = [
        { label: 'Name', value: String(record.name ?? '') },
        { label: 'Slug', value: String(record.slug ?? '') },
        {
            label: 'Description',
            value: record.description ? String(record.description) : null,
            placeholder: 'No description',
        },
        {
            label: 'Visibility',
            value: record.is_visible ? 'Visible' : 'Hidden',
        },
        {
            label: 'Last modified at',
            value: record.updated_at ? String(record.updated_at) : null,
        },
    ];

    return (
        <div
            className="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-(--inlay-overlay) p-4"
            data-slot="record-modal-backdrop"
            onMouseDown={(event) => {
                if (event.target === event.currentTarget) {
                    onClose();
                }
            }}
        >
            <section
                aria-modal="true"
                className="w-full max-w-lg rounded-(--inlay-radius) bg-(--inlay-surface) p-5 text-(--inlay-text) shadow-xl ring-1 ring-(--inlay-border) sm:p-6"
                role="dialog"
            >
                <header className="flex items-start justify-between gap-4 border-b border-(--inlay-border) pb-4">
                    <div>
                        <h3 className="text-xl font-semibold tracking-tight">
                            {heading}
                        </h3>
                        <p className="text-base/7 text-(--inlay-muted) sm:text-sm/6">
                            {resource.label} details
                        </p>
                    </div>
                    <button
                        aria-label="Close"
                        className="relative rounded-(--inlay-radius) p-2 text-(--inlay-muted) hover:bg-(--inlay-surface-muted) hover:text-(--inlay-text)"
                        onClick={onClose}
                        type="button"
                    >
                        <X aria-hidden="true" className="size-4" />
                    </button>
                </header>
                <dl className="grid gap-4 pt-5 sm:grid-cols-[minmax(0,9rem)_1fr] sm:gap-x-4 sm:gap-y-3">
                    {rows.map((row) => (
                        <div className="contents" key={row.label}>
                            <dt className="text-sm font-medium text-(--inlay-muted)">
                                {row.label}
                            </dt>
                            <dd className="text-sm text-(--inlay-text)">
                                {row.value ?? (
                                    <span className="text-(--inlay-muted)">
                                        {row.placeholder}
                                    </span>
                                )}
                            </dd>
                        </div>
                    ))}
                </dl>
            </section>
        </div>
    );
}

const ImportModal = ({
    heading,
    onClose,
    onSubmit,
    processing,
    ref: fileInput,
}: {
    heading: string;
    onClose: () => void;
    onSubmit: () => void;
    processing: boolean;
    ref: React.RefObject<HTMLInputElement | null>;
}) => {
    const [fileName, setFileName] = useState<string | null>(null);

    return (
        <div
            className="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-(--inlay-overlay) p-4"
            data-slot="import-modal-backdrop"
            onMouseDown={(event) => {
                if (event.target === event.currentTarget && !processing) {
                    onClose();
                }
            }}
        >
            <section
                aria-modal="true"
                className="w-full max-w-xl rounded-(--inlay-radius) bg-(--inlay-surface) p-5 text-(--inlay-text) shadow-xl ring-1 ring-(--inlay-border) sm:p-6"
                role="dialog"
            >
                <header className="flex items-start justify-between gap-4 border-b border-(--inlay-border) pb-4">
                    <div>
                        <h3 className="text-xl font-semibold tracking-tight">
                            {heading}
                        </h3>
                        <p className="text-base/7 text-(--inlay-muted) sm:text-sm/6">
                            Upload a CSV with columns name, slug, description,
                            is_visible, seo_title, and seo_description.
                        </p>
                    </div>
                    <button
                        aria-label="Close"
                        className="relative rounded-(--inlay-radius) p-2 text-(--inlay-muted) hover:bg-(--inlay-surface-muted) hover:text-(--inlay-text)"
                        disabled={processing}
                        onClick={onClose}
                        type="button"
                    >
                        <X aria-hidden="true" className="size-4" />
                    </button>
                </header>
                <div className="pt-5">
                    <label
                        className="flex min-h-24 cursor-pointer items-center justify-center gap-3 rounded-(--inlay-radius) border border-dashed border-(--inlay-control-border) px-4 py-6 text-sm text-(--inlay-muted) hover:bg-(--inlay-surface-muted)"
                        htmlFor="blog-import-file"
                    >
                        <Upload aria-hidden="true" className="size-5" />
                        {fileName ?? 'Choose a CSV file…'}
                        <input
                            accept=".csv,text/csv"
                            className="sr-only"
                            disabled={processing}
                            id="blog-import-file"
                            onChange={(event: ChangeEvent<HTMLInputElement>) =>
                                setFileName(
                                    event.target.files?.[0]?.name ?? null,
                                )
                            }
                            ref={fileInput}
                            type="file"
                        />
                    </label>
                    <div className="mt-7 flex justify-end gap-3 border-t border-(--inlay-border) pt-5">
                        <button
                            className="inline-flex min-h-(--inlay-button-height) items-center justify-center rounded-(--inlay-radius) px-4 py-2 text-sm font-semibold text-(--inlay-muted) transition hover:bg-(--inlay-surface-muted)"
                            disabled={processing}
                            onClick={onClose}
                            type="button"
                        >
                            Cancel
                        </button>
                        <button
                            className="inline-flex min-h-(--inlay-button-height) items-center justify-center gap-2 rounded-(--inlay-radius) bg-(--inlay-accent) px-4 py-2 text-sm font-semibold text-(--inlay-accent-foreground) transition hover:opacity-90"
                            disabled={processing || !fileName}
                            onClick={onSubmit}
                            type="button"
                        >
                            {processing ? 'Importing…' : 'Import CSV'}
                        </button>
                    </div>
                </div>
            </section>
        </div>
    );
};
