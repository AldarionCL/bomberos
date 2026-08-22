import clsx from 'clsx'

const COLORS = {
    Pendiente: 'bg-amber-100 text-amber-800 ring-amber-600/20',
    'Pendiente Aprobacion': 'bg-blue-100 text-blue-700 ring-blue-600/20',
    'Pendiente Aprobación': 'bg-blue-100 text-blue-700 ring-blue-600/20',
    Aprobado: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
    Rechazado: 'bg-rose-100 text-rose-700 ring-rose-600/20',
    Cancelado: 'bg-slate-200 text-slate-700 ring-slate-500/20',
}

export default function Badge({children, tone}) {
    const cls = COLORS[tone] ?? COLORS[children] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20'

    return (
        <span className={clsx('inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset', cls)}>
            {children}
        </span>
    )
}
