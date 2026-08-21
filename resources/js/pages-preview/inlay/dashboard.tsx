import { Head, usePage } from '@inertiajs/react'
import { WidgetDashboard } from '@inlayphp/widgets-react'
import type { WidgetDashboardResource } from '@inlayphp/widgets-react'
import InlayPanelLayout from '@/layouts/inlay-panel-layout'

type Props = { inlayPanel: { id: string; theme?: Record<string, string | number>; darkTheme?: Record<string, string | number>; themeName?: string | null }; inlayWidgets?: WidgetDashboardResource }

export default function Dashboard() {
  const { inlayPanel, inlayWidgets } = usePage<Props>().props
  const resource = inlayWidgets ?? { contract: 'inlay.widget-dashboard.v1' as const, columns: 12, widgets: [] }
  return <InlayPanelLayout><Head title="Dashboard" /><WidgetDashboard resource={resource} theme={{ contract: 'inlay.themes.v1', name: inlayPanel.themeName ?? inlayPanel.id, tokens: inlayPanel.theme ?? {}, darkTokens: inlayPanel.darkTheme ?? {} }} /></InlayPanelLayout>
}
