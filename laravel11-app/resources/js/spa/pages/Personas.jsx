import {useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import {PlusIcon, MagnifyingGlassIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Button, Card, EmptyState, Field, Input, Modal, PageLoader, Select} from '../components/Ui'
import Badge from '../components/Badge'
import {formatDate} from '../utils/format'
import {useToast, apiErrorMessage} from '../context/ToastContext'
import {useAuth} from '../context/AuthContext'

function NuevoSocioModal({onClose, catalogos}) {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [form, setForm] = useState({
        name: '', email: '', password: '', idRole: 2, Rut: '', Telefono: '', idCargo: '', FechaReclutamiento: new Date().toISOString().slice(0, 10),
    })

    const set = (key) => (e) => setForm((f) => ({...f, [key]: e.target.value}))

    const mutation = useMutation({
        mutationFn: () => client.post('/personas', form),
        onSuccess: () => {
            notify('Socio creado correctamente.')
            queryClient.invalidateQueries({queryKey: ['personas']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title="Nuevo socio" wide>
            <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Nombre completo"><Input value={form.name} onChange={set('name')} /></Field>
                <Field label="Correo"><Input type="email" value={form.email} onChange={set('email')} /></Field>
                <Field label="Contraseña"><Input type="password" value={form.password} onChange={set('password')} /></Field>
                <Field label="Rol del sistema">
                    <Select value={form.idRole} onChange={set('idRole')}>
                        {catalogos?.roles?.map((r) => <option key={r.id} value={r.id}>{r.Rol}</option>)}
                    </Select>
                </Field>
                <Field label="Rut"><Input value={form.Rut} onChange={set('Rut')} /></Field>
                <Field label="Teléfono"><Input value={form.Telefono} onChange={set('Telefono')} /></Field>
                <Field label="Cargo">
                    <Select value={form.idCargo} onChange={set('idCargo')}>
                        <option value="">Seleccione...</option>
                        {catalogos?.cargos?.map((c) => <option key={c.id} value={c.id}>{c.Cargo}</option>)}
                    </Select>
                </Field>
                <Field label="Fecha de ingreso">
                    <Input type="date" value={form.FechaReclutamiento} onChange={set('FechaReclutamiento')} />
                </Field>
            </div>
            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button loading={mutation.isPending} onClick={() => mutation.mutate()}>Crear socio</Button>
            </div>
        </Modal>
    )
}

function EditarSocioModal({persona, onClose, catalogos}) {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [form, setForm] = useState({
        idCargo: persona.persona?.idCargo ?? '',
        idEstado: persona.persona?.idEstado ?? '',
        Activo: persona.persona?.Activo ?? true,
        Telefono: persona.persona?.Telefono ?? '',
    })

    const set = (key) => (e) => setForm((f) => ({...f, [key]: e.target.value}))

    const mutation = useMutation({
        mutationFn: () => client.put(`/personas/${persona.persona.id}`, form),
        onSuccess: () => {
            notify('Socio actualizado.')
            queryClient.invalidateQueries({queryKey: ['personas']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title={`Editar a ${persona.name}`} wide>
            <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Cargo">
                    <Select value={form.idCargo} onChange={set('idCargo')}>
                        {catalogos?.cargos?.map((c) => <option key={c.id} value={c.id}>{c.Cargo}</option>)}
                    </Select>
                </Field>
                <Field label="Estado">
                    <Select value={form.idEstado} onChange={set('idEstado')}>
                        {catalogos?.estados?.map((e) => <option key={e.id} value={e.id}>{e.Estado}</option>)}
                    </Select>
                </Field>
                <Field label="Teléfono"><Input value={form.Telefono} onChange={set('Telefono')} /></Field>
                <Field label="Activo">
                    <Select value={form.Activo ? '1' : '0'} onChange={(e) => setForm((f) => ({...f, Activo: e.target.value === '1'}))}>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </Select>
                </Field>
            </div>
            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button loading={mutation.isPending} onClick={() => mutation.mutate()}>Guardar</Button>
            </div>
        </Modal>
    )
}

export default function Personas() {
    const {user} = useAuth()
    const [busqueda, setBusqueda] = useState('')
    const [nuevoOpen, setNuevoOpen] = useState(false)
    const [editando, setEditando] = useState(null)

    const {data, isLoading} = useQuery({
        queryKey: ['personas', busqueda],
        queryFn: async () => (await client.get('/personas', {params: {busqueda, porPagina: 50}})).data,
    })

    const {data: catalogos} = useQuery({
        queryKey: ['catalogos'],
        queryFn: async () => (await client.get('/catalogos')).data,
    })

    const personas = data?.data ?? []

    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-xl font-bold text-slate-900">Socios</h1>
                {user?.esAdministrador && (
                    <Button onClick={() => setNuevoOpen(true)}>
                        <PlusIcon className="h-4 w-4" /> Nuevo socio
                    </Button>
                )}
            </div>

            <div className="relative max-w-sm">
                <MagnifyingGlassIcon className="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                <Input className="pl-9" placeholder="Buscar por nombre o correo..." value={busqueda} onChange={(e) => setBusqueda(e.target.value)} />
            </div>

            <Card>
                {isLoading ? (
                    <PageLoader />
                ) : personas.length === 0 ? (
                    <EmptyState title="No se encontraron socios" />
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-100 text-xs uppercase text-slate-500">
                                <tr>
                                    <th className="px-5 py-3">Socio</th>
                                    <th className="px-5 py-3">Rut</th>
                                    <th className="px-5 py-3">Cargo</th>
                                    <th className="px-5 py-3">Ingreso</th>
                                    <th className="px-5 py-3">Estado</th>
                                    {user?.esAdministrador && <th className="px-5 py-3 text-right">Acciones</th>}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {personas.map((p) => (
                                    <tr key={p.idUsuario} className="hover:bg-slate-50">
                                        <td className="px-5 py-3">
                                            <p className="font-medium text-slate-800">{p.name}</p>
                                            <p className="text-xs text-slate-400">{p.email}</p>
                                        </td>
                                        <td className="px-5 py-3 text-slate-600">{p.persona?.Rut}</td>
                                        <td className="px-5 py-3 text-slate-600">{p.persona?.cargo}</td>
                                        <td className="px-5 py-3 text-slate-500">{formatDate(p.persona?.FechaReclutamiento)}</td>
                                        <td className="px-5 py-3">
                                            <Badge tone={p.persona?.Activo ? 'Aprobado' : 'Cancelado'}>
                                                {p.persona?.Activo ? 'Activo' : 'Inactivo'}
                                            </Badge>
                                        </td>
                                        {user?.esAdministrador && (
                                            <td className="px-5 py-3 text-right">
                                                <Button variant="secondary" className="px-2.5 py-1.5 text-xs" onClick={() => setEditando(p)}>
                                                    Editar
                                                </Button>
                                            </td>
                                        )}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>

            {nuevoOpen && <NuevoSocioModal onClose={() => setNuevoOpen(false)} catalogos={catalogos} />}
            {editando && <EditarSocioModal persona={editando} onClose={() => setEditando(null)} catalogos={catalogos} />}
        </div>
    )
}
