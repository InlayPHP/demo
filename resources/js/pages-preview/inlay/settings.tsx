import { Head, usePage } from '@inertiajs/react'
import InlayPanelLayout from '@/layouts/inlay-panel-layout'

type Props = { title?: string; teams?: Array<{ name?: string }>; inlayPanel: { id: string } }

export default function Settings() {
  const { title = 'Settings', teams = [] } = usePage<Props>().props
  return <InlayPanelLayout><Head title={title} /><main className="mx-auto w-full max-w-3xl"><h1 className="text-2xl font-semibold text-(--inlay-text)">{title}</h1><p className="mt-1 text-sm text-(--inlay-muted)">Manage your workspace settings.</p><section className="mt-6 rounded-(--inlay-radius-lg) border border-(--inlay-border) bg-(--inlay-surface) p-(--inlay-space-card)"><h2 className="font-semibold text-(--inlay-text)">Teams</h2>{teams.length ? <ul className="mt-3 grid gap-2">{teams.map((team, index) => <li className="rounded-(--inlay-radius) bg-(--inlay-surface-muted) px-3 py-2 text-sm text-(--inlay-text)" key={index}>{team.name ?? 'Team'}</li>)}</ul> : <p className="mt-3 text-sm text-(--inlay-muted)">No teams yet.</p>}</section></main></InlayPanelLayout>
}
