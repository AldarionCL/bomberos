import {useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import {PlusIcon, CalendarDaysIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Button, Card, EmptyState, Field, Input, Modal, PageLoader, Select, Textarea} from '../components/Ui'
import Badge from '../components/Badge'
import {formatDate} from '../utils/format'
import {useToast, apiErrorMessage} from '../context/ToastContext'
import {useAuth} from '../context/AuthContext'

function NuevaSolicitudModal({onClose, tipos, puedeSolicitarPorOtro}) {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [form, setForm] = useState({
        idTipo: tipos?.[0]?.id ?? 3,
        fechaDesde: '',
        fechaHasta: '',
        observaciones: '',
        asociadoA: '',
    })
    const {data: personas} = useQuery({
        queryKey: ['personas-select'],
        queryFn: async () => (await client.get('/personas', {params: {porPagina: 200}})).data,
        enabled: puedeSolicitarPorOtro,
    })

    const set = (key) => (e) => setForm((f) => ({...f, [key]: e.target.value}))

    const mutation = useMutation({
        mutationFn: () => client.post('/licencias', form),
        onSuccess: () => {
            notify('Solicitud enviada. Quedará pendiente de aprobación.')
            queryClient.invalidateQueries({queryKey: ['licencias-mias']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title="Nueva solicitud de licencia">
            <div className="space-y-4">
                {tipos?.length > 1 && (
                    <Field label="Tipo de solicitud">
                        <Select value={form.idTipo} onChange={set('idTipo')}>
                            {tipos.map((t) => <option key={t.id} value={t.id}>{t.Tipo}</option>)}
                        </Select>
                    </Field>
                )}
                {tipos?.[0]?.Descripcion && (
                    <p className="rounded-lg bg-slate-50 p-3 text-xs text-slate-500 dark:bg-slate-900 dark:text-slate-400">{tipos[0].Descripcion}</p>
                )}
                <div className="grid grid-cols-2 gap-3">
                    <Field label="Desde"><Input type="date" value={form.fechaDesde} onChange={set('fechaDesde')} /></Field>
                    <Field label="Hasta"><Input type="date" value={form.fechaHasta} onChange={set('fechaHasta')} /></Field>
                </div>
                {puedeSolicitarPorOtro && (
                    <Field label="Solicitar para (opcional)" hint="Déjalo en blanco para solicitarla para ti mismo">
                        <Select value={form.asociadoA} onChange={set('asociadoA')}>
                            <option value="">Yo mismo</option>
                            {personas?.data?.map((p) => <option key={p.idUsuario} value={p.idUsuario}>{p.name}</option>)}
                        </Select>
                    </Field>
                )}
                <Field label="Observaciones (opcional)">
                    <Textarea rows={3} value={form.observaciones} onChange={set('observaciones')} />
                </Field>
            </div>
            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button
                    loading={mutation.isPending}
                    disabled={!form.fechaDesde || !form.fechaHasta}
                    onClick={() => mutation.mutate()}
                >
                    Enviar solicitud
                </Button>
            </div>
        </Modal>
    )
}

export default function Licencias() {
    const {user} = useAuth()
    const [modalAbierto, setModalAbierto] = useState(false)

    const {data, isLoading} = useQuery({
        queryKey: ['licencias-mias'],
        queryFn: async () => (await client.get('/licencias/mias')).data,
    })

    const solicitudes = data?.solicitudes ?? []
    const disponibilidad = (data?.disponibilidad ?? []).filter((d) =>
        data?.tiposDisponibles?.some((t) => t.id === d.idTipo)
    )

    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-xl font-bold text-slate-900 dark:text-slate-100">Licencias y permisos</h1>
                <Button onClick={() => setModalAbierto(true)} disabled={!data?.tiposDisponibles?.length}>
                    <PlusIcon className="h-4 w-4" /> Nueva solicitud
                </Button>
            </div>

            {isLoading ? (
                <PageLoader />
            ) : (
                <>
                    {!data?.tiposDisponibles?.length && (
                        <Card>
                            <div className="p-5 text-sm text-slate-500 dark:text-slate-400">
                                Todavía no hay un aprobador configurado para solicitudes de licencia. Contacta a un administrador.
                            </div>
                        </Card>
                    )}

                    {disponibilidad.length > 0 && (
                        <div className="grid gap-4 sm:grid-cols-2">
                            {disponibilidad.map((d) => (
                                <Card key={d.idTipo} className="p-5">
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        {data.tiposDisponibles.find((t) => t.id === d.idTipo)?.Tipo}
                                    </p>
                                    <p className="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">
                                        {d.diasDisponibles} <span className="text-sm font-normal text-slate-400 dark:text-slate-500">de {d.diasTope} días disponibles</span>
                                    </p>
                                    <p className="text-xs text-slate-400 dark:text-slate-500">{d.diasUsados} días usados este año</p>
                                </Card>
                            ))}
                        </div>
                    )}

                    <Card>
                        {solicitudes.length === 0 ? (
                            <EmptyState icon={CalendarDaysIcon} title="No has hecho solicitudes de licencia" />
                        ) : (
                            <div className="divide-y divide-slate-100 dark:divide-slate-700">
                                {solicitudes.map((s) => (
                                    <div key={s.id} className="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                                        <div>
                                            <p className="font-medium text-slate-800 dark:text-slate-200">
                                                {formatDate(s.fechaDesde)} — {formatDate(s.fechaHasta)}
                                                <span className="ml-2 text-xs text-slate-400 dark:text-slate-500">({s.diasHabiles} días hábiles)</span>
                                            </p>
                                            <p className="text-xs text-slate-400 dark:text-slate-500">{s.tipo}{s.observaciones ? ` · ${s.observaciones}` : ''}</p>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <div className="flex gap-1">
                                                {s.aprobaciones.map((a, i) => (
                                                    <span key={i} title={`${a.aprobador}: ${a.estadoLabel}`}>
                                                        <Badge tone={a.estadoLabel === 'Aprobado' ? 'Aprobado' : a.estadoLabel === 'Rechazado' ? 'Rechazado' : 'Pendiente'}>
                                                            {a.aprobador}
                                                        </Badge>
                                                    </span>
                                                ))}
                                            </div>
                                            <Badge tone={s.estadoLabel}>{s.estadoLabel}</Badge>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </Card>
                </>
            )}

            {modalAbierto && (
                <NuevaSolicitudModal
                    onClose={() => setModalAbierto(false)}
                    tipos={data?.tiposDisponibles}
                    puedeSolicitarPorOtro={!!user?.permisos?.solicitarLicenciaParaOtros}
                />
            )}
        </div>
    )
}
