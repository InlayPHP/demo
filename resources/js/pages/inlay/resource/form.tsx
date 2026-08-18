import { Head, usePage } from '@inertiajs/react';
import { Form } from '@inlayphp/forms-react';
import type { FormErrors, FormResource } from '@inlayphp/forms-react';
import { ResourcePage } from '@inlayphp/resources-react';
import type {
    ResourceBreadcrumb,
    ResourceSubNavigationItem,
} from '@inlayphp/resources-react';
import InlayPanelLayout from '@/layouts/inlay-panel-layout';

type PageProps = {
    breadcrumbs?: ResourceBreadcrumb[];
    errors: FormErrors;
    form: FormResource;
    heading: string;
    inlayPanel: {
        darkTheme?: Record<string, string | number>;
        id: string;
        theme?: Record<string, string | number>;
        themeName?: string | null;
    };
    subNavigation?: ResourceSubNavigationItem[];
    subheading?: string | null;
};

export default function ResourceForm({
    breadcrumbs = [],
    form,
    heading,
    subheading,
    subNavigation = [],
}: PageProps) {
    const { errors, inlayPanel } = usePage<PageProps>().props;
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
                subheading={subheading}
            >
                <section className="rounded-(--inlay-radius) border border-(--inlay-border) bg-(--inlay-surface) p-5 sm:p-8">
                    <Form errors={errors} resource={form} theme={theme} />
                </section>
            </ResourcePage>
        </InlayPanelLayout>
    );
}
