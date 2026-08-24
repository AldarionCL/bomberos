import {useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import {BellIcon, CheckIcon} from '@heroicons/react/24/outline'
import clsx from 'clsx'
import client from '../api/client'
import {formatRelativo} from '../utils/format'

const COLOR_DOT = {
    success: 'bg-emerald-500',
    warning: 'bg-amber-500',
    danger: 'bg-rose-500',
    info: 'bg-blue-500',
}

export default function NotificacionesBell() {
    const [open, setOpen] = useState(false)
    const queryClient = useQueryClient()

    const {data: contador} = useQuery({
        queryKey: ['notificaciones-contador'],
        queryFn: async () => (await client.get('/notificaciones/contador')).data,
        refetchInterval: 60_000,
    })

    const {data, isLoading} = useQuery({
        queryKey: ['notificaciones'],
        queryFn: async () => (await client.get('/notificaciones')).data,
        enabled: open,
    })

    const marcarLeida = useMutation({
        mutationFn: (id) => client.post(`/notificaciones/${id}/leer`),
        onSuccess: () => {
            queryClient.invalidateQueries({queryKey: ['notificaciones']})
            queryClient.invalidateQueries({queryKey: ['notificaciones-contador']})
        },
    })

    const marcarTodas = useMutation({
        mutationFn: () => client.post('/notificaciones/leer-todas'),
        onSuccess: () => {
            queryClient.invalidateQueries({queryKey: ['notificaciones']})
            queryClient.invalidateQueries({queryKey: ['notificaciones-contador']})
        },
    })

    const noLeidas = contador?.noLeidas ?? 0
    const notificaciones = data?.data ?? []

    return (
        <div className="relative">
            <button
                onClick={() => setOpen((o) => !o)}
                className="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                title="Notificaciones"
            >
                <BellIcon className="h-5 w-5" />
                {noLeidas > 0 && (
                    <span className="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                        {noLeidas > 9 ? '9+' : noLeidas}
                    </span>
                )}
            </button>

            {open && (
                <>
                    <div className="fixed inset-0 z-40" onClick={() => setOpen(false)} />
                    <div className="absolute right-0 z-50 mt-2 w-80 max-w-[90vw] rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800">
                        <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-700">
                            <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">Notificaciones</p>
                            {noLeidas > 0 && (
                                <button
                                    onClick={() => marcarTodas.mutate()}
                                    className="flex items-center gap-1 text-xs font-medium text-brand-blue hover:underline"
                                >
                                    <CheckIcon className="h-3.5 w-3.5" /> Marcar todas leídas
                                </button>
                            )}
                        </div>

                        <div className="max-h-96 overflow-y-auto">
                            {isLoading ? (
                                <p className="px-4 py-6 text-center text-sm text-slate-400 dark:text-slate-500">Cargando...</p>
                            ) : notificaciones.length === 0 ? (
                                <p className="px-4 py-6 text-center text-sm text-slate-400 dark:text-slate-500">No tienes notificaciones.</p>
                            ) : (
                                <div className="divide-y divide-slate-100 dark:divide-slate-700">
                                    {notificaciones.map((n) => (
                                        <div
                                            key={n.id}
                                            onClick={() => !n.leida && marcarLeida.mutate(n.id)}
                                            className={clsx(
                                                'flex gap-2.5 px-4 py-3 text-sm',
                                                !n.leida && 'cursor-pointer bg-brand-50/60 hover:bg-brand-50 dark:bg-brand-900/10 dark:hover:bg-brand-900/20'
                                            )}
                                        >
                                            <span className={clsx('mt-1.5 h-2 w-2 shrink-0 rounded-full', COLOR_DOT[n.color] ?? 'bg-slate-300 dark:bg-slate-600')} />
                                            <div className="min-w-0 flex-1">
                                                <p className="font-medium text-slate-800 dark:text-slate-200">{n.titulo}</p>
                                                {n.cuerpo && <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{n.cuerpo}</p>}
                                                {n.acciones?.map((a, i) => (
                                                    <a
                                                        key={i}
                                                        href={a.url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        onClick={(e) => e.stopPropagation()}
                                                        className="mt-1 inline-block text-xs font-semibold text-brand-blue hover:underline"
                                                    >
                                                        {a.label} →
                                                    </a>
                                                ))}
                                                <p className="mt-1 text-[11px] text-slate-400 dark:text-slate-500">{formatRelativo(n.creadaEn)}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </>
            )}
        </div>
    )
}
