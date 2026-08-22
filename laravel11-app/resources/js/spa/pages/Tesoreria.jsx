import {useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import clsx from 'clsx'
import {PlusIcon, DocumentTextIcon, CheckIcon, XMarkIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Button, Card, EmptyState, Field, Input, Modal, PageLoader, Select, Textarea} from '../components/Ui'
import Badge from '../components/Badge'
import {formatMoney, formatMonthLabel} from '../utils/format'
import {useToast, apiErrorMessage} from '../context/ToastContext'

const TABS = [
    {key: 'aprobacion', label: 'Por aprobar', estado: 5},
    {key: 'pendientes', label: 'Pendientes', estado: 1},
    {key: 'aprobadas', label: 'Aprobadas', estado: 2},
    {key: 'rechazadas', label: 'Rechazadas', estado: 3},
    {key: 'todas', label: 'Todas', estado: null},
]

function RechazarModal({cuota, onClose}) {
    const [motivo, setMotivo] = useState('')
    const queryClient = useQueryClient()
    const {notify} = useToast()

    const mutation = useMutation({
        mutationFn: () => client.post(`/cuotas/${cuota.id}/rechazar`, {motivoRechazo: motivo}),
        onSuccess: () => {
            notify('Pago rechazado. Se generó una nueva cuota pendiente.')
            queryClient.invalidateQueries({queryKey: ['cuotas-tesoreria']})
            queryClient.invalidateQueries({queryKey: ['dashboard']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title="Rechazar pago">
            <Field label="Motivo del rechazo">
                <Textarea rows={3} value={motivo} onChange={(e) => setMotivo(e.target.value)} autoFocus />
            </Field>
            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button variant="danger" loading={mutation.isPending} disabled={!motivo} onClick={() => mutation.mutate()}>
                    Rechazar
                </Button>
            </div>
        </Modal>
    )
}

function AsignarModal({onClose}) {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [idUser, setIdUser] = useState('')
    const [idCuotaTipo, setIdCuotaTipo] = useState('')
    const [monto, setMonto] = useState('')
    const [periodo, setPeriodo] = useState(new Date().toISOString().slice(0, 10))

    const {data: personas} = useQuery({
        queryKey: ['personas-select'],
        queryFn: async () => (await client.get('/personas', {params: {porPagina: 200}})).data,
    })
    const {data: tipos} = useQuery({
        queryKey: ['cuota-tipos-asignables'],
        queryFn: async () => (await client.get('/cuota-tipos', {params: {soloAsignables: 1}})).data,
    })

    const mutation = useMutation({
        mutationFn: () => client.post('/cuotas/asignar', {idUser, idCuotaTipo, monto, periodo}),
        onSuccess: () => {
            notify('Cargo asignado correctamente.')
            queryClient.invalidateQueries({queryKey: ['cuotas-tesoreria']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title="Asignar nuevo cargo a un socio" wide>
            <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Socio">
                    <Select value={idUser} onChange={(e) => setIdUser(e.target.value)}>
                        <option value="">Seleccione...</option>
                        {personas?.data?.map((p) => (
                            <option key={p.idUsuario} value={p.idUsuario}>{p.name}</option>
                        ))}
                    </Select>
                </Field>
                <Field label="Tipo de cargo" hint="Solo tipos distintos a la cuota mensual">
                    <Select value={idCuotaTipo} onChange={(e) => setIdCuotaTipo(e.target.value)}>
                        <option value="">Seleccione...</option>
                        {tipos?.map((t) => (
                            <option key={t.id} value={t.id}>{t.nombre}</option>
                        ))}
                    </Select>
                </Field>
                <Field label="Monto">
                    <Input type="number" min={1} value={monto} onChange={(e) => setMonto(e.target.value)} />
                </Field>
                <Field label="Periodo">
                    <Input type="date" value={periodo} onChange={(e) => setPeriodo(e.target.value)} />
                </Field>
            </div>
            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button
                    loading={mutation.isPending}
                    disabled={!idUser || !idCuotaTipo || !monto}
                    onClick={() => mutation.mutate()}
                >
                    Asignar
                </Button>
            </div>
        </Modal>
    )
}

export default function Tesoreria() {
    const [tab, setTab] = useState('aprobacion')
    const [asignarOpen, setAsignarOpen] = useState(false)
    const [rechazarCuota, setRechazarCuota] = useState(null)
    const queryClient = useQueryClient()
    const {notify} = useToast()

    const estadoTab = TABS.find((t) => t.key === tab)?.estado

    const {data, isLoading} = useQuery({
        queryKey: ['cuotas-tesoreria', estadoTab],
        queryFn: async () => (await client.get('/cuotas', {params: estadoTab ? {estado: estadoTab} : {}})).data,
    })

    const aprobar = useMutation({
        mutationFn: (id) => client.post(`/cuotas/${id}/aprobar`),
        onSuccess: () => {
            notify('Pago aprobado.')
            queryClient.invalidateQueries({queryKey: ['cuotas-tesoreria']})
            queryClient.invalidateQueries({queryKey: ['dashboard']})
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    const cuotas = data?.data ?? []

    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-xl font-bold text-slate-900">Tesorería</h1>
                <Button onClick={() => setAsignarOpen(true)}>
                    <PlusIcon className="h-4 w-4" />
                    Asignar cargo
                </Button>
            </div>

            <div className="flex gap-1 overflow-x-auto rounded-xl bg-slate-100 p-1">
                {TABS.map((t) => (
                    <button
                        key={t.key}
                        onClick={() => setTab(t.key)}
                        className={clsx(
                            'whitespace-nowrap rounded-lg px-3.5 py-1.5 text-sm font-medium transition',
                            tab === t.key ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'
                        )}
                    >
                        {t.label}
                    </button>
                ))}
            </div>

            <Card>
                {isLoading ? (
                    <PageLoader />
                ) : cuotas.length === 0 ? (
                    <EmptyState title="No hay cuotas en esta categoría" />
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-100 text-xs uppercase text-slate-500">
                                <tr>
                                    <th className="px-5 py-3">Socio</th>
                                    <th className="px-5 py-3">Tipo</th>
                                    <th className="px-5 py-3">Periodo</th>
                                    <th className="px-5 py-3">Monto</th>
                                    <th className="px-5 py-3">Estado</th>
                                    <th className="px-5 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {cuotas.map((c) => (
                                    <tr key={c.id} className="hover:bg-slate-50">
                                        <td className="px-5 py-3">
                                            <p className="font-medium text-slate-800">{c.persona?.nombre}</p>
                                            <p className="text-xs text-slate-400">{c.persona?.rut}</p>
                                        </td>
                                        <td className="px-5 py-3 text-slate-600">{c.tipo}</td>
                                        <td className="px-5 py-3 text-slate-600">{formatMonthLabel(c.periodo?.slice(0, 7))}</td>
                                        <td className="px-5 py-3 text-slate-800">{formatMoney(c.monto)}</td>
                                        <td className="px-5 py-3"><Badge tone={c.estadoLabel}>{c.estadoLabel}</Badge></td>
                                        <td className="px-5 py-3 text-right">
                                            <div className="flex justify-end gap-2">
                                                {c.estado === 5 && (
                                                    <>
                                                        <Button
                                                            variant="success"
                                                            className="px-2.5 py-1.5 text-xs"
                                                            loading={aprobar.isPending}
                                                            onClick={() => aprobar.mutate(c.id)}
                                                        >
                                                            <CheckIcon className="h-3.5 w-3.5" /> Aprobar
                                                        </Button>
                                                        <Button
                                                            variant="danger"
                                                            className="px-2.5 py-1.5 text-xs"
                                                            onClick={() => setRechazarCuota(c)}
                                                        >
                                                            <XMarkIcon className="h-3.5 w-3.5" /> Rechazar
                                                        </Button>
                                                    </>
                                                )}
                                                {c.documento?.url && (
                                                    <a
                                                        href={c.documento.url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-blue hover:bg-blue-50"
                                                    >
                                                        <DocumentTextIcon className="h-4 w-4" />
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

            {asignarOpen && <AsignarModal onClose={() => setAsignarOpen(false)} />}
            {rechazarCuota && <RechazarModal cuota={rechazarCuota} onClose={() => setRechazarCuota(null)} />}
        </div>
    )
}
