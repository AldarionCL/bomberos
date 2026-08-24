import {useEffect, useMemo, useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import {useSearchParams} from 'react-router-dom'
import {BanknotesIcon, DocumentTextIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Button, Card, CardHeader, EmptyState, Field, Input, Modal, PageLoader} from '../components/Ui'
import Badge from '../components/Badge'
import HistorialPagosChart from '../components/HistorialPagosChart'
import {formatMoney, formatMonthLabel} from '../utils/format'
import {useToast, apiErrorMessage} from '../context/ToastContext'
import {useAuth} from '../context/AuthContext'

function irAWebpay(url, token) {
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = url
    const input = document.createElement('input')
    input.type = 'hidden'
    input.name = 'token_ws'
    input.value = token
    form.appendChild(input)
    document.body.appendChild(form)
    form.submit()
}

function esPagable(c) {
    return c.estado === 1 && c.pendiente > 0
}

function claveCuota(c) {
    return c.id ?? c.periodoKey
}

function PagarModal({cuota, onClose}) {
    const {user} = useAuth()
    const esTesoreria = user?.esAdministrador || user?.esTesorero
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [montoPagar, setMontoPagar] = useState(cuota?.pendiente ?? 0)
    const [nroDocumento, setNroDocumento] = useState('')
    const [fechaPago, setFechaPago] = useState(new Date().toISOString().slice(0, 10))
    const [archivo, setArchivo] = useState(null)
    const [marcarAprobada, setMarcarAprobada] = useState(false)

    const mutation = useMutation({
        mutationFn: async () => {
            const form = new FormData()
            form.append('montoPagar', montoPagar)
            form.append('nroDocumento', nroDocumento)
            form.append('fechaPago', fechaPago)
            form.append('documento', archivo)
            form.append('marcarAprobada', marcarAprobada ? '1' : '0')

            const url = cuota.virtual
                ? '/cuotas/pagar-periodo-mensual'
                : `/cuotas/${cuota.id}/pagar`

            if (cuota.virtual) {
                form.append('periodoKey', cuota.periodoKey)
            }

            return client.post(url, form)
        },
        onSuccess: () => {
            notify('Pago registrado correctamente. Quedará pendiente de aprobación.')
            queryClient.invalidateQueries({queryKey: ['cuotas-mias']})
            queryClient.invalidateQueries({queryKey: ['dashboard']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    const webpayMutation = useMutation({
        mutationFn: async () => {
            const payload = cuota.virtual ? {periodoKey: cuota.periodoKey} : {cuotaId: cuota.id}
            const {data} = await client.post('/webpay/iniciar', payload)
            return data
        },
        onSuccess: ({url, token}) => irAWebpay(url, token),
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title="Pagar cuota">
            <div className="space-y-4">
                <div className="rounded-lg bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                    <p>
                        Periodo: <strong>{formatMonthLabel(cuota.periodo?.slice(0, 7))}</strong>
                    </p>
                    <p>
                        Monto pendiente: <strong>{formatMoney(cuota.pendiente)}</strong>
                    </p>
                </div>

                <div className="rounded-lg border border-brand-100 bg-brand-50 p-3 dark:border-brand-800 dark:bg-brand-900/20">
                    <p className="text-sm font-medium text-brand-900 dark:text-brand-100">Pago en línea con tarjeta (Webpay)</p>
                    <p className="mt-0.5 text-xs text-brand-700 dark:text-brand-300">
                        Se aprueba automáticamente al confirmar el pago con Transbank.
                    </p>
                    <Button
                        className="mt-2"
                        onClick={() => webpayMutation.mutate()}
                        loading={webpayMutation.isPending}
                    >
                        Pagar {formatMoney(cuota.pendiente)} con Webpay
                    </Button>
                </div>

                <div className="relative py-1 text-center text-xs text-slate-400 dark:text-slate-500">
                    <span className="relative z-10 bg-white px-2 dark:bg-slate-800">o sube tu comprobante manualmente</span>
                    <div className="absolute inset-x-0 top-1/2 border-t border-slate-200 dark:border-slate-700" />
                </div>

                <Field label="Monto a pagar">
                    <Input
                        type="number"
                        min={1}
                        value={montoPagar}
                        onChange={(e) => setMontoPagar(e.target.value)}
                    />
                </Field>

                <div className="grid grid-cols-2 gap-3">
                    <Field label="N° de documento">
                        <Input value={nroDocumento} onChange={(e) => setNroDocumento(e.target.value)} />
                    </Field>
                    <Field label="Fecha de pago">
                        <Input type="date" value={fechaPago} onChange={(e) => setFechaPago(e.target.value)} />
                    </Field>
                </div>

                <Field label="Comprobante de pago (imagen o PDF)">
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        onChange={(e) => setArchivo(e.target.files?.[0] ?? null)}
                        className="block w-full text-sm text-slate-600 dark:text-slate-300"
                    />
                </Field>

                {esTesoreria && (
                    <label className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input
                            type="checkbox"
                            checked={marcarAprobada}
                            onChange={(e) => setMarcarAprobada(e.target.checked)}
                            className="rounded border-slate-300"
                        />
                        Marcar como aprobada automáticamente
                    </label>
                )}
            </div>

            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button
                    onClick={() => mutation.mutate()}
                    loading={mutation.isPending}
                    disabled={!archivo || !montoPagar}
                >
                    Confirmar pago
                </Button>
            </div>
        </Modal>
    )
}

function PagarMultiplesModal({cuotas, onClose}) {
    const {user} = useAuth()
    const esTesoreria = user?.esAdministrador || user?.esTesorero
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const totalPendiente = cuotas.reduce((acc, c) => acc + c.pendiente, 0)
    const [montoPagar, setMontoPagar] = useState(totalPendiente)
    const [nroDocumento, setNroDocumento] = useState('')
    const [fechaPago, setFechaPago] = useState(new Date().toISOString().slice(0, 10))
    const [archivo, setArchivo] = useState(null)
    const [marcarAprobada, setMarcarAprobada] = useState(false)

    const mutation = useMutation({
        mutationFn: async () => {
            const form = new FormData()
            cuotas.forEach((c) => {
                if (c.virtual) form.append('periodos[]', c.periodoKey)
                else form.append('ids[]', c.id)
            })
            form.append('montoPagar', montoPagar)
            form.append('nroDocumento', nroDocumento)
            form.append('fechaPago', fechaPago)
            form.append('documento', archivo)
            form.append('marcarAprobada', marcarAprobada ? '1' : '0')
            return client.post('/cuotas/pagar-multiples', form)
        },
        onSuccess: ({data}) => {
            const pagadas = data?.pagadas?.length ?? 0
            notify(`Comprobante registrado para ${pagadas} cuota(s). Quedarán pendientes de aprobación.`)
            queryClient.invalidateQueries({queryKey: ['cuotas-mias']})
            queryClient.invalidateQueries({queryKey: ['dashboard']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    const webpayMutation = useMutation({
        mutationFn: async () => {
            const payload = {
                cuotaIds: cuotas.filter((c) => !c.virtual).map((c) => c.id),
                periodoKeys: cuotas.filter((c) => c.virtual).map((c) => c.periodoKey),
            }
            const {data} = await client.post('/webpay/iniciar', payload)
            return data
        },
        onSuccess: ({url, token}) => irAWebpay(url, token),
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title={`Pagar ${cuotas.length} cuotas seleccionadas`} wide>
            <div className="space-y-4">
                <div className="max-h-40 overflow-y-auto rounded-lg bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                    {cuotas.map((c) => (
                        <div key={claveCuota(c)} className="flex justify-between gap-3 py-0.5">
                            <span>{formatMonthLabel(c.periodo?.slice(0, 7))} — {c.tipo}</span>
                            <span className="font-medium">{formatMoney(c.pendiente)}</span>
                        </div>
                    ))}
                    <div className="mt-1 flex justify-between gap-3 border-t border-slate-200 pt-1 font-semibold text-slate-800 dark:border-slate-700 dark:text-slate-100">
                        <span>Total</span>
                        <span>{formatMoney(totalPendiente)}</span>
                    </div>
                </div>

                <div className="rounded-lg border border-brand-100 bg-brand-50 p-3 dark:border-brand-800 dark:bg-brand-900/20">
                    <p className="text-sm font-medium text-brand-900 dark:text-brand-100">Pago en línea con tarjeta (Webpay)</p>
                    <p className="mt-0.5 text-xs text-brand-700 dark:text-brand-300">
                        Un solo cargo por el total; se aprueban automáticamente todas las cuotas al confirmar el pago con Transbank.
                    </p>
                    <Button
                        className="mt-2"
                        onClick={() => webpayMutation.mutate()}
                        loading={webpayMutation.isPending}
                    >
                        Pagar {formatMoney(totalPendiente)} con Webpay
                    </Button>
                </div>

                <div className="relative py-1 text-center text-xs text-slate-400 dark:text-slate-500">
                    <span className="relative z-10 bg-white px-2 dark:bg-slate-800">o sube tu comprobante manualmente</span>
                    <div className="absolute inset-x-0 top-1/2 border-t border-slate-200 dark:border-slate-700" />
                </div>

                <p className="text-xs text-slate-500 dark:text-slate-400">
                    Se genera un solo comprobante, asociado a todas las cuotas seleccionadas. Si pagas todas se aplican de la más atrasada a la más nueva; si el monto no alcanza para todas, se abonan completas empezando por la más atrasada.
                </p>

                <Field label="Monto a pagar">
                    <Input
                        type="number"
                        min={1}
                        value={montoPagar}
                        onChange={(e) => setMontoPagar(e.target.value)}
                    />
                </Field>

                <div className="grid grid-cols-2 gap-3">
                    <Field label="N° de documento">
                        <Input value={nroDocumento} onChange={(e) => setNroDocumento(e.target.value)} />
                    </Field>
                    <Field label="Fecha de pago">
                        <Input type="date" value={fechaPago} onChange={(e) => setFechaPago(e.target.value)} />
                    </Field>
                </div>

                <Field label="Comprobante de pago (imagen o PDF)">
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        onChange={(e) => setArchivo(e.target.files?.[0] ?? null)}
                        className="block w-full text-sm text-slate-600 dark:text-slate-300"
                    />
                </Field>

                {esTesoreria && (
                    <label className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input
                            type="checkbox"
                            checked={marcarAprobada}
                            onChange={(e) => setMarcarAprobada(e.target.checked)}
                            className="rounded border-slate-300"
                        />
                        Marcar como aprobadas automáticamente
                    </label>
                )}
            </div>

            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button
                    onClick={() => mutation.mutate()}
                    loading={mutation.isPending}
                    disabled={!archivo || !montoPagar}
                >
                    Confirmar pago
                </Button>
            </div>
        </Modal>
    )
}

const MENSAJES_WEBPAY = {
    exito: {tono: 'success', texto: 'Pago con Webpay aprobado correctamente.'},
    rechazado: {tono: 'error', texto: 'El pago con Webpay fue rechazado. Puedes intentarlo nuevamente.'},
    cancelado: {tono: 'error', texto: 'Cancelaste el pago con Webpay.'},
    error: {tono: 'error', texto: 'Ocurrió un problema al procesar el pago con Webpay. Intenta nuevamente.'},
}

export default function MisCuotas() {
    const [cuotaSeleccionada, setCuotaSeleccionada] = useState(null)
    const [seleccionadas, setSeleccionadas] = useState(new Set())
    const [pagoMultipleAbierto, setPagoMultipleAbierto] = useState(false)
    const [searchParams, setSearchParams] = useSearchParams()
    const {notify} = useToast()
    const queryClient = useQueryClient()

    const {data, isLoading} = useQuery({
        queryKey: ['cuotas-mias'],
        queryFn: async () => (await client.get('/cuotas/mias')).data,
    })

    useEffect(() => {
        const resultado = searchParams.get('webpay')
        if (!resultado) return
        const mensaje = MENSAJES_WEBPAY[resultado]
        if (mensaje) notify(mensaje.texto, mensaje.tono)
        queryClient.invalidateQueries({queryKey: ['cuotas-mias']})
        queryClient.invalidateQueries({queryKey: ['dashboard']})
        setSearchParams((prev) => {
            const next = new URLSearchParams(prev)
            next.delete('webpay')
            return next
        }, {replace: true})
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [])

    const cuotas = data?.cuotas ?? []
    const pagables = useMemo(() => cuotas.filter(esPagable), [cuotas])
    const cuotasSeleccionadas = useMemo(
        () => pagables.filter((c) => seleccionadas.has(claveCuota(c))),
        [pagables, seleccionadas]
    )
    const totalSeleccionado = cuotasSeleccionadas.reduce((acc, c) => acc + c.pendiente, 0)

    const alternarSeleccion = (id) => {
        setSeleccionadas((prev) => {
            const next = new Set(prev)
            if (next.has(id)) next.delete(id)
            else next.add(id)
            return next
        })
    }

    const alternarTodas = () => {
        setSeleccionadas((prev) => (prev.size === pagables.length ? new Set() : new Set(pagables.map(claveCuota))))
    }

    const cerrarPagoMultiple = () => {
        setPagoMultipleAbierto(false)
        setSeleccionadas(new Set())
    }

    if (isLoading) return <PageLoader />

    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-xl font-bold text-slate-900 dark:text-slate-100">Mis cuotas</h1>
                <div className="rounded-xl bg-white px-4 py-2 text-sm shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                    <span className="text-slate-500 dark:text-slate-400">Total pendiente: </span>
                    <span className="font-semibold text-slate-900 dark:text-slate-100">{formatMoney(data?.resumen?.totalPendiente)}</span>
                </div>
            </div>

            {cuotas.length > 0 && (
                <Card>
                    <CardHeader title="Estado de cuenta" subtitle="Cumplimiento de la cuota mensual en los últimos 12 meses" />
                    <HistorialPagosChart cuotas={cuotas} />
                </Card>
            )}

            {seleccionadas.size > 0 && (
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-brand-blue px-4 py-3 text-sm text-white shadow-sm">
                    <span>{seleccionadas.size} cuota(s) seleccionada(s) — {formatMoney(totalSeleccionado)}</span>
                    <div className="flex gap-2">
                        <Button variant="secondary" className="px-3 py-1.5 text-xs" onClick={() => setSeleccionadas(new Set())}>
                            Quitar selección
                        </Button>
                        <Button variant="success" className="px-3 py-1.5 text-xs" onClick={() => setPagoMultipleAbierto(true)}>
                            Pagar seleccionadas
                        </Button>
                    </div>
                </div>
            )}

            <Card>
                {cuotas.length === 0 ? (
                    <EmptyState icon={BanknotesIcon} title="No tienes cuotas registradas" />
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-100 text-xs uppercase text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                <tr>
                                    <th className="w-10 px-5 py-3">
                                        {pagables.length > 1 && (
                                            <input
                                                type="checkbox"
                                                checked={seleccionadas.size === pagables.length}
                                                onChange={alternarTodas}
                                                className="rounded border-slate-300"
                                                title="Seleccionar todas las pagables"
                                            />
                                        )}
                                    </th>
                                    <th className="px-5 py-3">Periodo</th>
                                    <th className="px-5 py-3">Tipo</th>
                                    <th className="px-5 py-3">Monto</th>
                                    <th className="px-5 py-3">Vencimiento</th>
                                    <th className="px-5 py-3">Estado</th>
                                    <th className="px-5 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                                {cuotas.map((c) => (
                                    <tr key={c.id ?? c.periodoKey} className="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                        <td className="px-5 py-3">
                                            {esPagable(c) && (
                                                <input
                                                    type="checkbox"
                                                    checked={seleccionadas.has(claveCuota(c))}
                                                    onChange={() => alternarSeleccion(claveCuota(c))}
                                                    className="rounded border-slate-300"
                                                />
                                            )}
                                        </td>
                                        <td className="px-5 py-3 font-medium text-slate-800 dark:text-slate-200">
                                            {formatMonthLabel(c.periodo?.slice(0, 7))}
                                        </td>
                                        <td className="px-5 py-3 text-slate-600 dark:text-slate-300">{c.tipo}</td>
                                        <td className="px-5 py-3 text-slate-800 dark:text-slate-200">{formatMoney(c.monto)}</td>
                                        <td className="px-5 py-3 text-slate-500 dark:text-slate-400">
                                            {formatMonthLabel(c.vencimiento?.slice(0, 7))}
                                        </td>
                                        <td className="px-5 py-3">
                                            <Badge tone={c.estadoLabel}>{c.estadoLabel}</Badge>
                                            {c.estadoLabel === 'Rechazado' && c.motivoRechazo && (
                                                <p className="mt-1 text-xs text-rose-500 dark:text-rose-400">{c.motivoRechazo}</p>
                                            )}
                                        </td>
                                        <td className="px-5 py-3 text-right">
                                            <div className="flex justify-end gap-2">
                                                {[1].includes(c.estado) && c.pendiente > 0 && (
                                                    <Button
                                                        variant="primary"
                                                        className="px-2.5 py-1.5 text-xs"
                                                        onClick={() => setCuotaSeleccionada(c)}
                                                    >
                                                        Pagar
                                                    </Button>
                                                )}
                                                {c.documento?.url && (
                                                    <a
                                                        href={c.documento.url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-blue hover:bg-brand-50 dark:hover:bg-brand-900/20"
                                                    >
                                                        <DocumentTextIcon className="h-4 w-4" />
                                                        Comprobante
                                                    </a>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>

            {cuotaSeleccionada && (
                <PagarModal cuota={cuotaSeleccionada} onClose={() => setCuotaSeleccionada(null)} />
            )}

            {pagoMultipleAbierto && (
                <PagarMultiplesModal cuotas={cuotasSeleccionadas} onClose={cerrarPagoMultiple} />
            )}
        </div>
    )
}
