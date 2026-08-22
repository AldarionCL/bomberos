import clsx from 'clsx'

export default function StatCard({label, value, icon: Icon, tone = 'slate', hint}) {
    const tones = {
        slate: 'bg-slate-100 text-slate-600',
        blue: 'bg-blue-50 text-brand-blue',
        green: 'bg-emerald-50 text-emerald-600',
        amber: 'bg-amber-50 text-amber-600',
        red: 'bg-rose-50 text-rose-600',
    }

    return (
        <div className="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/50">
            <div className="flex items-center justify-between">
                <p className="text-sm font-medium text-slate-500">{label}</p>
                {Icon && (
                    <span className={clsx('flex h-9 w-9 items-center justify-center rounded-full', tones[tone])}>
                        <Icon className="h-5 w-5" />
                    </span>
                )}
            </div>
            <p className="mt-2 text-2xl font-bold tracking-tight text-slate-900">{value}</p>
            {hint && <p className="mt-1 text-xs text-slate-500">{hint}</p>}
        </div>
    )
}
