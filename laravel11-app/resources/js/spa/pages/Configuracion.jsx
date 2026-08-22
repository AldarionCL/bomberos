import {useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import {PlusIcon, TrashIcon, StarIcon} from '@heroicons/react/24/outline'
import {StarIcon as StarSolid} from '@heroicons/react/24/solid'
import client from '../api/client'
import {Button, Card, CardHeader, Field, Input, Modal, PageLoader, Select} from '../components/Ui'
import Badge from '../components/Badge'
import {formatMoney} from '../utils/format'
import {useToast, apiErrorMessage} from '../context/ToastContext'

function NuevoTipoModal({onClose}) {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [form, setForm] = useState({nombre: '', descripcion: '', tipoCobro: 'CUOTA'})

    const mutation = useMutation({
        mutationFn: () => client.post('/cuota-tipos', form),
        onSuccess: () => {
            notify('Tipo de cargo creado.')
            queryClient.invalidateQueries({queryKey: ['cuota-tipos-admin']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title="Nuevo tipo de cargo">
            <div className="space-y-4">
                <Field label="Nombre">
                    <Input value={form.nombre} onChange={(e) => setForm((f) => ({...f, nombre: e.target.value}))} />
                </Field>
                <Field label="Descripción">
                    <Input value={form.descripcion} onChange={(e) => setForm((f) => ({...f, descripcion: e.target.value}))} />
                </Field>
                <Field label="Tipo de cobro">
                    <Select value={form.tipoCobro} onChange={(e) => setForm((f) => ({...f, tipoCobro: e.target.value}))}>
                        <option value="CUOTA">CUOTA</option>
                        <option value="PAGO">PAGO</option>
                    </Select>
                </Field>
            </div>
            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button loading={mutation.isPending} disabled={!form.nombre} onClick={() => mutation.mutate()}>Crear</Button>
            </div>
        </Modal>
    )
}

function ValorCuotaMensual() {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [monto, setMonto] = useState('')

    const {data, isLoading} = useQuery({
        queryKey: ['cuota-mensual'],
        queryFn: async () => (await client.get('/configuracion/cuota-mensual')).data,
    })

    const mutation = useMutation({
        mutationFn: () => client.put('/configuracion/cuota-mensual', {monto}),
        onSuccess: () => {
            notify('Valor de la cuota mensual actualizado.')
            queryClient.invalidateQueries({queryKey: ['cuota-mensual']})
            setMonto('')
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    if (isLoading) return <PageLoader />

    return (
        <Card>
            <CardHeader title="Valor de la cuota mensual" subtitle={`Tipo asociado: ${data?.nombre ?? 'sin configurar'}`} />
            <div className="flex flex-wrap items-end gap-4 p-5">
                <div>
                    <p className="text-xs font-medium uppercase text-slate-400">Valor vigente</p>
                    <p className="text-2xl font-bold text-slate-900">{formatMoney(data?.monto)}</p>
                </div>
                <div className="flex items-end gap-2">
                    <Field label="Nuevo valor">
                        <Input type="number" min={1} value={monto} onChange={(e) => setMonto(e.target.value)} className="w-40" />
                    </Field>
                    <Button loading={mutation.isPending} disabled={!monto} onClick={() => mutation.mutate()}>
                        Actualizar
                    </Button>
                </div>
            </div>
            <p className="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">
                Este valor se aplica automáticamente cada mes a todos los socios activos: ya no es necesario generar cuotas manualmente.
            </p>
        </Card>
    )
}

export default function Configuracion() {
    const [nuevoOpen, setNuevoOpen] = useState(false)
    const queryClient = useQueryClient()
    const {notify} = useToast()

    const {data: tipos, isLoading} = useQuery({
        queryKey: ['cuota-tipos-admin'],
        queryFn: async () => (await client.get('/cuota-tipos')).data,
    })

    const marcarMensual = useMutation({
        mutationFn: (tipo) => client.put(`/cuota-tipos/${tipo.id}`, {
            nombre: tipo.nombre, descripcion: tipo.descripcion, tipoCobro: tipo.tipoCobro, activo: tipo.activo, esMensual: true,
        }),
        onSuccess: () => {
            notify('Cuota mensual reasignada.')
            queryClient.invalidateQueries({queryKey: ['cuota-tipos-admin']})
            queryClient.invalidateQueries({queryKey: ['cuota-mensual']})
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    const eliminar = useMutation({
        mutationFn: (id) => client.delete(`/cuota-tipos/${id}`),
        onSuccess: () => {
            notify('Tipo eliminado.')
            queryClient.invalidateQueries({queryKey: ['cuota-tipos-admin']})
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <div className="space-y-6">
            <h1 className="text-xl font-bold text-slate-900">Configuración</h1>

            <ValorCuotaMensual />

            <Card>
                <CardHeader
                    title="Tipos de cargo"
                    subtitle="La estrella indica el tipo usado para la cuota mensual automática"
                    action={<Button onClick={() => setNuevoOpen(true)}><PlusIcon className="h-4 w-4" /> Nuevo tipo</Button>}
                />
                {isLoading ? (
                    <PageLoader />
                ) : (
                    <div className="divide-y divide-slate-100">
                        {tipos?.map((t) => (
                            <div key={t.id} className="flex items-center justify-between gap-4 px-5 py-3">
                                <div className="flex items-center gap-3">
                                    <button
                                        title={t.esMensual ? 'Cuota mensual actual' : 'Marcar como cuota mensual'}
                                        onClick={() => !t.esMensual && marcarMensual.mutate(t)}
                                        className="text-amber-500 disabled:text-slate-300"
                                        disabled={t.esMensual}
                                    >
                                        {t.esMensual ? <StarSolid className="h-5 w-5" /> : <StarIcon className="h-5 w-5" />}
                                    </button>
                                    <div>
                                        <p className="font-medium text-slate-800">{t.nombre}</p>
                                        <p className="text-xs text-slate-400">{t.descripcion}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Badge tone={t.tipoCobro === 'CUOTA' ? 'Pendiente Aprobacion' : 'Aprobado'}>{t.tipoCobro}</Badge>
                                    {!t.esMensual && (
                                        <button
                                            onClick={() => confirm('¿Eliminar este tipo?') && eliminar.mutate(t.id)}
                                            className="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                                        >
                                            <TrashIcon className="h-4 w-4" />
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </Card>

            {nuevoOpen && <NuevoTipoModal onClose={() => setNuevoOpen(false)} />}
        </div>
    )
}
