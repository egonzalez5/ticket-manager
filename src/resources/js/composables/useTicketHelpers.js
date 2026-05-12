// ── Shared pure helpers for ticket components ─────────────────────────────────

export const STATUS_MAP = {
    open:        { label: 'Open',        badge: 'badge-blue',    dot: 'bg-blue-500'    },
    in_progress: { label: 'In Progress', badge: 'badge-amber',   dot: 'bg-amber-500'   },
    pending:     { label: 'Pending',     badge: 'badge-violet',  dot: 'bg-violet-500'  },
    resolved:    { label: 'Resolved',    badge: 'badge-emerald', dot: 'bg-emerald-500' },
    closed:      { label: 'Closed',      badge: 'badge-gray',    dot: 'bg-gray-400'    },
}

export const PRIORITY_MAP = {
    3: { label: 'High',   badge: 'badge-red',     icon: '↑' },
    2: { label: 'Medium', badge: 'badge-orange',  icon: '→' },
    1: { label: 'Low',    badge: 'badge-emerald', icon: '↓' },
}

export const AVATAR_COLORS = [
    'bg-indigo-500', 'bg-violet-500', 'bg-blue-500',   'bg-emerald-500',
    'bg-amber-500',  'bg-rose-500',   'bg-teal-500',   'bg-cyan-500',
    'bg-pink-500',   'bg-sky-500',    'bg-orange-500', 'bg-lime-600',
]

export const initials = (name) => {
    if (!name) return '?'
    return name
        .trim()
        .split(/\s+/)
        .filter(w => w.length > 0)
        .map(w => w[0])
        .join('')
        .slice(0, 2)
        .toUpperCase() || '?'
}

export const relDate = (iso) => {
    if (!iso) return '—'
    const secs = Math.floor((Date.now() - new Date(iso)) / 1000)
    if (secs < 60)    return 'just now'
    if (secs < 3600)  return `${Math.floor(secs / 60)}m ago`
    if (secs < 86400) return `${Math.floor(secs / 3600)}h ago`
    const days = Math.floor(secs / 86400)
    if (days < 7) return `${days}d ago`
    return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

export const absDate = (iso) => {
    if (!iso) return '—'
    return new Date(iso).toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

export const formatMinutes = (mins) => {
    if (!mins) return '—'
    const h = Math.floor(mins / 60)
    const m = mins % 60
    if (!h) return `${m}m`
    return m ? `${h}h ${m}m` : `${h}h`
}

export const formatSize = (bytes) => {
    if (!bytes) return ''
    if (bytes < 1024)    return `${bytes} B`
    if (bytes < 1048576) return `${Math.round(bytes / 1024)} KB`
    return `${(bytes / 1048576).toFixed(1)} MB`
}

export const fileExt = (name) => (name?.split('.').pop() ?? '').toUpperCase()

export const avatarColor = (id) => AVATAR_COLORS[(id ?? 0) % AVATAR_COLORS.length]

export const statusFor = (s) =>
    STATUS_MAP[s] ?? { label: s ?? '—', badge: 'badge-gray', dot: 'bg-gray-400' }

export const priorityFor = (l) =>
    PRIORITY_MAP[l] ?? { label: '—', badge: 'badge-gray', icon: '•' }

export const isAgentUser = (item) =>
    ['admin', 'agent'].includes(item.user?.role?.slug)
