import { Head, Link, usePage } from '@inertiajs/react';
import type { PanelResource } from '@inlayphp/panels-react';
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

type PageProps = {
    inlayPanel: PanelResource;
    breadcrumbs?: ResourceBreadcrumb[];
    heading: string;
    subheading?: string | null;
    subNavigation?: ResourceSubNavigationItem[];
    table: TableResource;
    tabs?: ResourceTabsResource | null;
};

export default function UsersIndex({
    breadcrumbs = [],
    heading,
    subheading,
    subNavigation = [],
    table,
    tabs = null,
}: PageProps) {
    const { inlayPanel } = usePage<PageProps>().props;
    const theme = {
        contract: 'inlay.themes.v1' as const,
        name: inlayPanel.themeName ?? inlayPanel.id,
        tokens: inlayPanel.theme,
        darkTokens: inlayPanel.darkTheme ?? {},
    };

    return (
        <InlayPanelLayout>
            <Head title={heading} />
            <ResourcePage
                actions={
                    <Link
                        className="inline-flex min-h-(--inlay-button-height) items-center justify-center gap-2 rounded-(--inlay-radius) bg-(--inlay-accent) px-4 py-2 text-sm font-semibold text-(--inlay-accent-foreground) transition hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(--inlay-accent)"
                        href="/admin/users/create"
                    >
                        <Plus aria-hidden="true" className="size-4" />
                        Create user
                    </Link>
                }
                breadcrumbs={breadcrumbs}
                className="mx-auto w-full max-w-7xl"
                heading={heading}
                subNavigation={subNavigation}
                subheading={
                    subheading ??
                    'Create and maintain the accounts that can use this application.'
                }
                tabs={tabs}
            >
                <section className="min-w-0 rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface) p-4 sm:p-6">
                    <Table resource={table} theme={theme} />
                </section>
            </ResourcePage>
        </InlayPanelLayout>
    );
}
