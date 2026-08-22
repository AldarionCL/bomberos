import clsx from 'clsx'

export function Card({children, className, ...props}) {
    return (
        <div
            className={clsx(
                'rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/50',
                className
            )}
            {...props}
        >
            {children}
        </div>
    )
}

export function CardHeader({title, subtitle, action}) {
    return (
        <div className="flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h3 className="text-sm font-semibold text-slate-900">{title}</h3>
                {subtitle && <p className="mt-0.5 text-xs text-slate-500">{subtitle}</p>}
            </div>
            {action}
        </div>
    )
}

const VARIANTS = {
    primary: 'bg-brand-blue text-white hover:bg-blue-900 focus-visible:outline-brand-blue disabled:bg-slate-300',
    secondary: 'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 disabled:text-slate-400',
    success: 'bg-emerald-600 text-white hover:bg-emerald-700 disabled:bg-slate-300',
    danger: 'bg-rose-600 text-white hover:bg-rose-700 disabled:bg-slate-300',
    ghost: 'text-slate-600 hover:bg-slate-100 disabled:text-slate-300',
}

export function Button({variant = 'primary', className, loading, children, disabled, ...props}) {
    return (
        <button
            className={clsx(
                'inline-flex items-center justify-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed',
                VARIANTS[variant],
                className
            )}
            disabled={disabled || loading}
            {...props}
        >
            {loading && <Spinner className="h-4 w-4" />}
            {children}
        </button>
    )
}

export function Spinner({className}) {
    return (
        <svg className={clsx('animate-spin', className)} viewBox="0 0 24 24" fill="none">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
    )
}

export function PageLoader() {
    return (
        <div className="flex h-64 items-center justify-center">
            <Spinner className="h-8 w-8 text-brand-blue" />
        </div>
    )
}

export function EmptyState({icon: Icon, title, description, action}) {
    return (
        <div className="flex flex-col items-center justify-center gap-2 px-6 py-14 text-center">
            {Icon && <Icon className="h-10 w-10 text-slate-300" />}
            <p className="text-sm font-semibold text-slate-700">{title}</p>
            {description && <p className="max-w-sm text-sm text-slate-500">{description}</p>}
            {action}
        </div>
    )
}

export function Field({label, children, error, hint}) {
    return (
        <label className="block">
            {label && <span className="mb-1.5 block text-sm font-medium text-slate-700">{label}</span>}
            {children}
            {hint && !error && <span className="mt-1 block text-xs text-slate-500">{hint}</span>}
            {error && <span className="mt-1 block text-xs text-rose-600">{error}</span>}
        </label>
    )
}

export function Input({className, ...props}) {
    return (
        <input
            className={clsx(
                'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-blue',
                className
            )}
            {...props}
        />
    )
}

export function Select({className, children, ...props}) {
    return (
        <select
            className={clsx(
                'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand-blue',
                className
            )}
            {...props}
        >
            {children}
        </select>
    )
}

export function Textarea({className, ...props}) {
    return (
        <textarea
            className={clsx(
                'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-blue',
                className
            )}
            {...props}
        />
    )
}

export function Modal({open, onClose, title, children, footer, wide}) {
    if (!open) return null

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onClick={onClose} />
            <div
                className={clsx(
                    'relative z-10 w-full overflow-hidden rounded-2xl bg-white shadow-xl',
                    wide ? 'max-w-2xl' : 'max-w-md'
                )}
            >
                <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h3 className="text-base font-semibold text-slate-900">{title}</h3>
                    <button onClick={onClose} className="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        ✕
                    </button>
                </div>
                <div className="max-h-[70vh] overflow-y-auto px-5 py-4">{children}</div>
                {footer && <div className="flex justify-end gap-2 border-t border-slate-100 px-5 py-3">{footer}</div>}
            </div>
        </div>
    )
}
