import {useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import {BanknotesIcon, DocumentTextIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Button, Card, CardHeader, EmptyState, Field, Input, Modal, PageLoader} from '../components/Ui'
import Badge from '../components/Badge'
import {formatMoney, formatMonthLabel} from '../utils/format'
import {useToast, apiErrorMessage} from '../context/ToastContext'
import {useAuth} from '../context/AuthContext'

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

    return (
        <Modal open onClose={onClose} title="Pagar cuota">
            <div className="space-y-4">
                <div className="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                    <p>
                        Periodo: <strong>{formatMonthLabel(cuota.periodo?.slice(0, 7))}</strong>
                    </p>
                    <p>
                        Monto pendiente: <strong>{formatMoney(cuota.pendiente)}</strong>
                    </p>
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
                        className="block w-full text-sm text-slate-600"
                    />
                </Field>

                {esTesoreria && (
                    <label className="flex items-center gap-2 text-sm text-slate-600">
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

export default function MisCuotas() {
    const [cuotaSeleccionada, setCuotaSeleccionada] = useState(null)

    const {data, isLoading} = useQuery({
        queryKey: ['cuotas-mias'],
        queryFn: async () => (await client.get('/cuotas/mias')).data,
    })

    if (isLoading) return <PageLoader />

    const cuotas = data?.cuotas ?? []

    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-xl font-bold text-slate-900">Mis cuotas</h1>
                <div className="rounded-xl bg-white px-4 py-2 text-sm shadow-sm ring-1 ring-slate-200">
                    <span className="text-slate-500">Total pendiente: </span>
                    <span className="font-semibold text-slate-900">{formatMoney(data?.resumen?.totalPendiente)}</span>
                </div>
            </div>

            <Card>
                {cuotas.length === 0 ? (
                    <EmptyState icon={BanknotesIcon} title="No tienes cuotas registradas" />
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-100 text-xs uppercase text-slate-500">
                                <tr>
                                    <th className="px-5 py-3">Periodo</th>
                                    <th className="px-5 py-3">Tipo</th>
                                    <th className="px-5 py-3">Monto</th>
                                    <th className="px-5 py-3">Vencimiento</th>
                                    <th className="px-5 py-3">Estado</th>
                                    <th className="px-5 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {cuotas.map((c) => (
                                    <tr key={c.id ?? c.periodoKey} className="hover:bg-slate-50">
                                        <td className="px-5 py-3 font-medium text-slate-800">
                                            {formatMonthLabel(c.periodo?.slice(0, 7))}
                                        </td>
                                        <td className="px-5 py-3 text-slate-600">{c.tipo}</td>
                                        <td className="px-5 py-3 text-slate-800">{formatMoney(c.monto)}</td>
                                        <td className="px-5 py-3 text-slate-500">
                                            {formatMonthLabel(c.vencimiento?.slice(0, 7))}
                                        </td>
                                        <td className="px-5 py-3">
                                            <Badge tone={c.estadoLabel}>{c.estadoLabel}</Badge>
                                            {c.estadoLabel === 'Rechazado' && c.motivoRechazo && (
                                                <p className="mt-1 text-xs text-rose-500">{c.motivoRechazo}</p>
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
                                                        className="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-blue hover:bg-blue-50"
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
        </div>
    )
}
