import {useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import {PlusIcon, MagnifyingGlassIcon, TrashIcon, ArrowDownTrayIcon, PencilIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Button, Card, EmptyState, Field, Input, Modal, PageLoader, Select, Textarea} from '../components/Ui'
import Badge from '../components/Badge'
import FileIcon from '../components/FileIcon'
import {formatDate} from '../utils/format'
import {useToast, apiErrorMessage} from '../context/ToastContext'
import {useAuth} from '../context/AuthContext'

function DocumentoModal({documento, onClose, catalogos}) {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [form, setForm] = useState({
        nombre: documento?.nombre ?? '',
        nroDocumento: documento?.nroDocumento ?? '',
        idTipo: documento?.idTipo ?? '',
        descripcion: documento?.descripcion ?? '',
        asociadoA: '',
        publicarNoticia: false,
    })
    const [archivo, setArchivo] = useState(null)

    const {data: personas} = useQuery({
        queryKey: ['personas-select'],
        queryFn: async () => (await client.get('/personas', {params: {porPagina: 200}})).data,
        enabled: !documento,
    })

    const set = (key) => (e) => setForm((f) => ({...f, [key]: e.target.value}))

    const mutation = useMutation({
        mutationFn: () => {
            const data = new FormData()
            data.append('nombre', form.nombre)
            data.append('nroDocumento', form.nroDocumento)
            data.append('idTipo', form.idTipo)
            data.append('descripcion', form.descripcion)
            if (archivo) data.append('archivo', archivo)
            if (!documento) {
                data.append('asociadoA', form.asociadoA)
                data.append('publicarNoticia', form.publicarNoticia ? '1' : '0')
                return client.post('/documentos', data)
            }
            data.append('_method', 'PUT')
            return client.post(`/documentos/${documento.id}`, data)
        },
        onSuccess: () => {
            notify(documento ? 'Documento actualizado.' : 'Documento publicado.')
            queryClient.invalidateQueries({queryKey: ['documentos']})
            if (form.publicarNoticia) queryClient.invalidateQueries({queryKey: ['noticias']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title={documento ? 'Editar documento' : 'Nuevo documento'} wide>
            <div className="space-y-4">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Nombre"><Input value={form.nombre} onChange={set('nombre')} /></Field>
                    <Field label="N° de documento"><Input value={form.nroDocumento} onChange={set('nroDocumento')} /></Field>
                </div>
                <Field label="Tipo de documento">
                    <Select value={form.idTipo} onChange={set('idTipo')}>
                        <option value="">Seleccione...</option>
                        {catalogos?.tiposDocumento?.map((t) => (
                            <option key={t.id} value={t.id}>{t.Tipo} {t.Clasificacion === 'privado' ? '(privado)' : ''}</option>
                        ))}
                    </Select>
                </Field>
                <Field label="Descripción">
                    <Textarea rows={2} value={form.descripcion} onChange={set('descripcion')} />
                </Field>
                <Field label={documento ? 'Reemplazar archivo (opcional)' : 'Archivo'}>
                    <input type="file" onChange={(e) => setArchivo(e.target.files?.[0] ?? null)} className="block w-full text-sm text-slate-600" />
                </Field>

                {!documento && (
                    <>
                        <Field label="Asociar a un socio (opcional)">
                            <Select value={form.asociadoA} onChange={set('asociadoA')}>
                                <option value="">Sin asociar</option>
                                {personas?.data?.map((p) => (
                                    <option key={p.idUsuario} value={p.idUsuario}>{p.name}</option>
                                ))}
                            </Select>
                        </Field>
                        <label className="flex items-center gap-2 text-sm text-slate-600">
                            <input
                                type="checkbox"
                                checked={form.publicarNoticia}
                                onChange={(e) => setForm((f) => ({...f, publicarNoticia: e.target.checked}))}
                                className="rounded border-slate-300"
                            />
                            Generar una publicación en Noticias sobre este documento
                        </label>
                    </>
                )}
            </div>
            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button
                    loading={mutation.isPending}
                    disabled={!form.nombre || !form.idTipo || (!documento && !archivo)}
                    onClick={() => mutation.mutate()}
                >
                    {documento ? 'Guardar cambios' : 'Publicar'}
                </Button>
            </div>
        </Modal>
    )
}

export default function Documentos() {
    const {user} = useAuth()
    const puedeGestionar = user?.permisos?.gestionarDocumentos
    const [busqueda, setBusqueda] = useState('')
    const [idTipo, setIdTipo] = useState('')
    const [nuevoOpen, setNuevoOpen] = useState(false)
    const [editando, setEditando] = useState(null)
    const queryClient = useQueryClient()
    const {notify} = useToast()

    const {data, isLoading} = useQuery({
        queryKey: ['documentos', busqueda, idTipo],
        queryFn: async () => (await client.get('/documentos', {params: {busqueda, idTipo, porPagina: 30}})).data,
    })

    const {data: catalogos} = useQuery({
        queryKey: ['catalogos'],
        queryFn: async () => (await client.get('/catalogos')).data,
    })

    const eliminar = useMutation({
        mutationFn: (id) => client.delete(`/documentos/${id}`),
        onSuccess: () => {
            notify('Documento eliminado.')
            queryClient.invalidateQueries({queryKey: ['documentos']})
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    const documentos = data?.data ?? []

    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-xl font-bold text-slate-900">Documentos</h1>
                {puedeGestionar && (
                    <Button onClick={() => setNuevoOpen(true)}>
                        <PlusIcon className="h-4 w-4" /> Nuevo documento
                    </Button>
                )}
            </div>

            <div className="flex flex-wrap gap-3">
                <div className="relative max-w-sm flex-1">
                    <MagnifyingGlassIcon className="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input className="pl-9" placeholder="Buscar por nombre..." value={busqueda} onChange={(e) => setBusqueda(e.target.value)} />
                </div>
                <Select value={idTipo} onChange={(e) => setIdTipo(e.target.value)} className="w-56">
                    <option value="">Todos los tipos</option>
                    {catalogos?.tiposDocumento?.map((t) => (
                        <option key={t.id} value={t.id}>{t.Tipo}</option>
                    ))}
                </Select>
            </div>

            <Card>
                {isLoading ? (
                    <PageLoader />
                ) : documentos.length === 0 ? (
                    <EmptyState title="No hay documentos" />
                ) : (
                    <div className="divide-y divide-slate-100">
                        {documentos.map((d) => (
                            <div key={d.id} className="flex items-center justify-between gap-4 px-5 py-4">
                                <div className="flex items-center gap-3">
                                    <FileIcon extension={d.extension} />
                                    <div>
                                        <p className="font-medium text-slate-800">{d.nombre}</p>
                                        <p className="text-xs text-slate-400">
                                            {d.tipo} · {formatDate(d.creadoEn)}
                                            {d.asociadoA && ` · ${d.asociadoA.nombre}`}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    {user?.esAdministrador && d.clasificacion && (
                                        <Badge tone={d.clasificacion === 'privado' ? 'Rechazado' : 'Aprobado'}>
                                            {d.clasificacion}
                                        </Badge>
                                    )}
                                    {d.url && (
                                        <a
                                            href={d.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-brand-blue"
                                            title="Descargar"
                                        >
                                            <ArrowDownTrayIcon className="h-4 w-4" />
                                        </a>
                                    )}
                                    {puedeGestionar && (
                                        <button onClick={() => setEditando(d)} className="rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Editar">
                                            <PencilIcon className="h-4 w-4" />
                                        </button>
                                    )}
                                    {user?.esAdministrador && (
                                        <button
                                            onClick={() => confirm('¿Eliminar este documento?') && eliminar.mutate(d.id)}
                                            className="rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600"
                                            title="Eliminar"
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

            {nuevoOpen && <DocumentoModal onClose={() => setNuevoOpen(false)} catalogos={catalogos} />}
            {editando && <DocumentoModal documento={editando} onClose={() => setEditando(null)} catalogos={catalogos} />}
        </div>
    )
}
