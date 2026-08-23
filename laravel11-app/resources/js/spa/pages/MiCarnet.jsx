import {useEffect, useState} from 'react'
import {useQuery} from '@tanstack/react-query'
import QRCode from 'qrcode'
import {ShieldCheckIcon, ExclamationTriangleIcon} from '@heroicons/react/24/solid'
import client from '../api/client'
import {Card, PageLoader} from '../components/Ui'

export default function MiCarnet() {
    const {data, isLoading} = useQuery({
        queryKey: ['mi-carnet'],
        queryFn: async () => (await client.get('/mi-carnet')).data,
    })
    const [qr, setQr] = useState(null)

    useEffect(() => {
        if (data?.urlVerificacion) {
            QRCode.toDataURL(data.urlVerificacion, {margin: 1, width: 240, color: {dark: '#023aab'}})
                .then(setQr)
                .catch(() => setQr(null))
        }
    }, [data?.urlVerificacion])

    if (isLoading) return <PageLoader />

    return (
        <div className="mx-auto max-w-md space-y-4">
            <h1 className="text-xl font-bold text-slate-900">Mi carnet de socio</h1>

            <Card className="overflow-hidden">
                <div className="bg-gradient-to-br from-brand-blue to-blue-800 p-5 text-white">
                    <div className="flex items-center gap-3">
                        <img src="/img/logo.png" alt="Logo" className="h-10 w-10 rounded-full border-2 border-white/50 object-cover" />
                        <div>
                            <p className="text-sm font-bold uppercase tracking-wide">
                                {import.meta.env.VITE_APP_NAME || 'Club'}
                            </p>
                            <p className="text-xs text-blue-100">Carnet de socio</p>
                        </div>
                    </div>
                </div>

                <div className="flex gap-4 p-5">
                    <img src={data?.foto} alt={data?.nombre} className="h-24 w-24 shrink-0 rounded-xl object-cover ring-2 ring-slate-100" />
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-lg font-bold text-slate-900">{data?.nombre}</p>
                        <p className="text-sm text-slate-500">{data?.cargo}</p>
                        <p className="mt-1 text-xs text-slate-400">RUT {data?.rut}</p>

                        <div className="mt-3 flex flex-wrap gap-2">
                            <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                {data?.estadoSocio}
                            </span>
                            {data?.cuotaAlDia ? (
                                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                    <ShieldCheckIcon className="h-3.5 w-3.5" /> Al día
                                </span>
                            ) : (
                                <span className="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                                    <ExclamationTriangleIcon className="h-3.5 w-3.5" /> Con cuotas pendientes
                                </span>
                            )}
                        </div>
                    </div>
                </div>

                <div className="flex flex-col items-center gap-2 border-t border-dashed border-slate-200 p-5">
                    {qr && <img src={qr} alt="Código QR de verificación" className="h-40 w-40" />}
                    <p className="text-center text-xs text-slate-400">
                        Escanea este código para verificar la vigencia de la membresía
                    </p>
                </div>
            </Card>
        </div>
    )
}
