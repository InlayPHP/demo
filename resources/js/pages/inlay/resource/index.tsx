import { Head, Link, router, usePage } from '@inertiajs/react';
import { ResourcePage } from '@inlayphp/resources-react';
import type {
    ResourceBreadcrumb,
    ResourceSubNavigationItem,
    ResourceTabsResource,
} from '@inlayphp/resources-react';
import { Table } from '@inlayphp/tables-react';
import type { TableResource } from '@inlayphp/tables-react';
import { Plus } from 'lucide-react';
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
    heading: string;
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

export default function ResourceIndex({
    breadcrumbs = [],
    heading,
    resource,
    subheading,
    subNavigation = [],
    table,
    tabs = null,
}: PageProps) {
    const { inlayPanel } = usePage<PageProps>().props;
    const createUrl = resource.pages.create?.url;
    const theme = {
        contract: 'inlay.themes.v1' as const,
        name: inlayPanel.themeName ?? inlayPanel.id,
        tokens: inlayPanel.theme ?? {},
        darkTokens: inlayPanel.darkTheme ?? {},
    };

    return (
        <InlayPanelLayout>
            <Head title={heading} />
            <ResourcePage
                actions={
                    createUrl ? (
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
                heading={heading}
                subNavigation={subNavigation}
                subheading={
                    subheading ??
                    `Explore the ${resource.pluralLabel.toLowerCase()} resource. Every field, filter, action, and validation rule is defined in Laravel.`
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
                    <Table resource={table} theme={theme} />
                </section>
            </ResourcePage>
        </InlayPanelLayout>
    );
}
