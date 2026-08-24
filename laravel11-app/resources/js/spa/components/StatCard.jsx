import clsx from 'clsx'

export default function StatCard({label, value, icon: Icon, tone = 'slate', hint}) {
    const tones = {
        slate: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        blue: 'bg-brand-50 text-brand-blue dark:bg-brand-500/10',
        green: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        amber: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        red: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
    }

    return (
        <div className="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/50 dark:border-slate-700 dark:bg-slate-800 dark:shadow-none">
            <div className="flex items-center justify-between">
                <p className="text-sm font-medium text-slate-500 dark:text-slate-400">{label}</p>
                {Icon && (
                    <span className={clsx('flex h-9 w-9 items-center justify-center rounded-full', tones[tone])}>
                        <Icon className="h-5 w-5" />
                    </span>
                )}
            </div>
            <p className="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{value}</p>
            {hint && <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">{hint}</p>}
        </div>
    )
}
