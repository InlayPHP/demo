import { Head, usePage } from '@inertiajs/react';
import { WidgetDashboard } from '@inlayphp/widgets-react';
import type { WidgetDashboardResource } from '@inlayphp/widgets-react';
import { inlayIcons } from '@/components/inlay-icons';
import InlayPanelLayout from '@/layouts/inlay-panel-layout';

export default function Dashboard() {
    const { inlayPanel, inlayWidgets } = usePage<{
        inlayPanel: {
            darkTheme?: Record<string, string | number>;
            id: string;
            theme?: Record<string, string | number>;
            themeName?: string | null;
        };
        inlayWidgets: WidgetDashboardResource;
    }>().props;
    const theme = {
        contract: 'inlay.themes.v1' as const,
        name: inlayPanel.themeName ?? inlayPanel.id,
        tokens: inlayPanel.theme ?? {},
        darkTokens: inlayPanel.darkTheme ?? {},
    };

    return (
        <InlayPanelLayout>
            <Head title="Dashboard" />
            <WidgetDashboard
                icons={inlayIcons}
                resource={inlayWidgets}
                theme={theme}
            />
        </InlayPanelLayout>
    );
}
