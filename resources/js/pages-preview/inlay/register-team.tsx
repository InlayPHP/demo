import { Head, usePage } from '@inertiajs/react'
import { Form } from '@inlayphp/forms-react'
import type { FormErrors, FormResource } from '@inlayphp/forms-react'
import InlayPanelLayout from '@/layouts/inlay-panel-layout'

type Props = { title?: string; errors?: FormErrors; form?: FormResource; inlayPanel: { id: string; theme?: Record<string, string | number>; darkTheme?: Record<string, string | number>; themeName?: string | null } }

export default function RegisterTeam() {
  const { title = 'New team', errors = {}, form, inlayPanel } = usePage<Props>().props
  return <InlayPanelLayout><Head title={title} /><main className="mx-auto w-full max-w-2xl"><h1 className="text-2xl font-semibold text-(--inlay-text)">{title}</h1><p className="mt-1 text-sm text-(--inlay-muted)">Create a workspace for your team.</p>{form ? <section className="mt-6 rounded-(--inlay-radius-lg) border border-(--inlay-border) bg-(--inlay-surface) p-(--inlay-space-card)"><Form errors={errors} resource={form} theme={{ contract: 'inlay.themes.v1', name: inlayPanel.themeName ?? inlayPanel.id, tokens: inlayPanel.theme ?? {}, darkTokens: inlayPanel.darkTheme ?? {} }} /></section> : null}</main></InlayPanelLayout>
}
