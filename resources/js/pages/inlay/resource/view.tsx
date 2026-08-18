import { Head, usePage } from '@inertiajs/react';
import { Infolist } from '@inlayphp/infolists-react';
import type { InfolistResource } from '@inlayphp/infolists-react';
import { ResourcePage } from '@inlayphp/resources-react';
import type {
    ResourceBreadcrumb,
    ResourceSubNavigationItem,
} from '@inlayphp/resources-react';
import InlayPanelLayout from '@/layouts/inlay-panel-layout';

type PageProps = {
    breadcrumbs?: ResourceBreadcrumb[];
    heading: string;
    infolist: InfolistResource;
    inlayPanel: {
        darkTheme?: Record<string, string | number>;
        id: string;
        theme?: Record<string, string | number>;
        themeName?: string | null;
    };
    subNavigation?: ResourceSubNavigationItem[];
    subheading?: string | null;
};

export default function ResourceView({
    breadcrumbs = [],
    heading,
    infolist,
    subheading,
    subNavigation = [],
}: PageProps) {
    const { inlayPanel } = usePage<PageProps>().props;
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
                breadcrumbs={breadcrumbs}
                className="mx-auto w-full max-w-5xl"
                heading={heading}
                subNavigation={subNavigation}
                subheading={
                    subheading ??
                    'Read-only detail view rendered by the shared Inlay infolist contract.'
                }
            >
                <section className="rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface) p-5 sm:p-8">
                    <Infolist resource={infolist} theme={theme} />
                </section>
            </ResourcePage>
        </InlayPanelLayout>
    );
}
