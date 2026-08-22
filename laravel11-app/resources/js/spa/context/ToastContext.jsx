import {createContext, useCallback, useContext, useState} from 'react'
import clsx from 'clsx'

const ToastContext = createContext(null)
let idSeq = 0

const STYLES = {
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    error: 'border-rose-200 bg-rose-50 text-rose-800',
    info: 'border-blue-200 bg-blue-50 text-blue-800',
}

export function ToastProvider({children}) {
    const [toasts, setToasts] = useState([])

    const dismiss = useCallback((id) => {
        setToasts((prev) => prev.filter((t) => t.id !== id))
    }, [])

    const notify = useCallback((message, type = 'success') => {
        const id = ++idSeq
        setToasts((prev) => [...prev, {id, message, type}])
        setTimeout(() => dismiss(id), 4500)
    }, [dismiss])

    return (
        <ToastContext.Provider value={{notify}}>
            {children}
            <div className="fixed bottom-4 right-4 z-[100] flex w-80 flex-col gap-2">
                {toasts.map((t) => (
                    <div
                        key={t.id}
                        className={clsx('rounded-xl border px-4 py-3 text-sm shadow-lg', STYLES[t.type])}
                    >
                        {t.message}
                    </div>
                ))}
            </div>
        </ToastContext.Provider>
    )
}

export function useToast() {
    const ctx = useContext(ToastContext)
    if (!ctx) throw new Error('useToast debe usarse dentro de <ToastProvider>')
    return ctx
}

export function apiErrorMessage(error, fallback = 'Ocurrió un error inesperado.') {
    const data = error?.response?.data
    if (!data) return fallback
    if (data.message) return data.message
    if (data.errors) return Object.values(data.errors).flat().join(' ')
    return fallback
}
