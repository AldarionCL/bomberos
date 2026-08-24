import {useParams} from 'react-router-dom'
import {useQuery} from '@tanstack/react-query'
import {CheckCircleIcon, XCircleIcon} from '@heroicons/react/24/solid'
import client from '../api/client'
import {PageLoader} from '../components/Ui'
import {useSiteConfig} from '../context/SiteConfigContext'

export default function VerificarCarnet() {
    const {token} = useParams()
    const {logo} = useSiteConfig()

    const {data, isLoading, isError} = useQuery({
        queryKey: ['verificar', token],
        queryFn: async () => (await client.get(`/verificar/${token}`)).data,
        retry: false,
    })

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4 dark:bg-slate-900">
            <div className="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl dark:bg-slate-800">
                <img src={logo} alt="Logo" className="mx-auto h-14 w-14 rounded-full object-cover" />
                <p className="mt-2 text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">
                    Verificación de socio
                </p>

                {isLoading && <PageLoader />}

                {isError && (
                    <div className="mt-4 flex flex-col items-center gap-2 text-rose-600 dark:text-rose-400">
                        <XCircleIcon className="h-14 w-14" />
                        <p className="font-semibold">Carnet no válido</p>
                        <p className="text-sm text-slate-500 dark:text-slate-400">Este código no corresponde a ningún socio vigente.</p>
                    </div>
                )}

                {data && (
                    <div className="mt-4">
                        <img src={data.foto} alt={data.nombre} className="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-slate-100 dark:ring-slate-700" />
                        <p className="mt-3 text-lg font-bold text-slate-900 dark:text-slate-100">{data.nombre}</p>
                        <p className="text-sm text-slate-500 dark:text-slate-400">{data.cargo} · RUT {data.rut}</p>

                        <div className="mt-4 flex flex-col items-center gap-2">
                            {data.activo && data.cuotaAlDia ? (
                                <div className="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    <CheckCircleIcon className="h-6 w-6" />
                                    <span className="font-semibold">Socio vigente y al día</span>
                                </div>
                            ) : (
                                <div className="flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-3 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                    <XCircleIcon className="h-6 w-6" />
                                    <span className="font-semibold">
                                        {!data.activo ? 'Socio no activo' : 'Con cuotas pendientes'}
                                    </span>
                                </div>
                            )}
                            <p className="text-xs text-slate-400 dark:text-slate-500">Estado: {data.estadoSocio}</p>
                        </div>

                        <p className="mt-4 text-xs text-slate-400 dark:text-slate-500">Verificado el {data.verificadoEn}</p>
                    </div>
                )}
            </div>
        </div>
    )
}
