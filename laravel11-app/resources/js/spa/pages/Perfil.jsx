import {useState} from 'react'
import {useMutation} from '@tanstack/react-query'
import client from '../api/client'
import {Button, Card, CardHeader, Field, Input} from '../components/Ui'
import {useAuth} from '../context/AuthContext'
import {useToast, apiErrorMessage} from '../context/ToastContext'
import {formatDate} from '../utils/format'

export default function Perfil() {
    const {user, setUser} = useAuth()
    const {notify} = useToast()
    const [form, setForm] = useState({
        Telefono: user?.persona?.Telefono ?? '',
        TelefonoEmergencia: user?.persona?.TelefonoEmergencia ?? '',
        Direccion: user?.persona?.Direccion ?? '',
        Comuna: user?.persona?.Comuna ?? '',
    })

    const set = (key) => (e) => setForm((f) => ({...f, [key]: e.target.value}))

    const mutation = useMutation({
        mutationFn: () => client.put('/me', form),
        onSuccess: ({data}) => {
            setUser(data)
            notify('Perfil actualizado.')
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <div className="max-w-2xl space-y-6">
            <h1 className="text-xl font-bold text-slate-900">Mi perfil</h1>

            <Card>
                <CardHeader title={user?.name} subtitle={user?.email} />
                <div className="grid gap-4 p-5 sm:grid-cols-2">
                    <div>
                        <p className="text-xs font-medium uppercase text-slate-400">Rut</p>
                        <p className="text-sm text-slate-700">{user?.persona?.Rut ?? '—'}</p>
                    </div>
                    <div>
                        <p className="text-xs font-medium uppercase text-slate-400">Cargo</p>
                        <p className="text-sm text-slate-700">{user?.cargo ?? '—'}</p>
                    </div>
                    <div>
                        <p className="text-xs font-medium uppercase text-slate-400">Fecha de ingreso</p>
                        <p className="text-sm text-slate-700">{formatDate(user?.persona?.FechaReclutamiento)}</p>
                    </div>
                    <div>
                        <p className="text-xs font-medium uppercase text-slate-400">Estado</p>
                        <p className="text-sm text-slate-700">{user?.persona?.estado ?? '—'}</p>
                    </div>
                </div>
            </Card>

            <Card>
                <CardHeader title="Datos de contacto" />
                <div className="grid gap-4 p-5 sm:grid-cols-2">
                    <Field label="Teléfono">
                        <Input value={form.Telefono} onChange={set('Telefono')} placeholder="+56 9 1234 5678" />
                    </Field>
                    <Field label="Teléfono de emergencia">
                        <Input value={form.TelefonoEmergencia} onChange={set('TelefonoEmergencia')} placeholder="+56 9 1234 5678" />
                    </Field>
                    <Field label="Dirección">
                        <Input value={form.Direccion} onChange={set('Direccion')} />
                    </Field>
                    <Field label="Comuna">
                        <Input value={form.Comuna} onChange={set('Comuna')} />
                    </Field>
                </div>
                <div className="flex justify-end border-t border-slate-100 px-5 py-3">
                    <Button loading={mutation.isPending} onClick={() => mutation.mutate()}>Guardar cambios</Button>
                </div>
            </Card>
        </div>
    )
}
