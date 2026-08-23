import {Field, Input, Select, Textarea} from './Ui'

export const NIVEL_ESTUDIO = [
    ['basica', 'Básica'],
    ['media', 'Media'],
    ['tecnica', 'Técnica'],
    ['universitaria', 'Universitaria'],
]
export const ESTADO_CIVIL = ['Soltero', 'Casado', 'Divorciado', 'Viudo']
export const GRUPO_SANGUINEO = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']

export const PERSONA_TABS_BASE = [
    {key: 'general', label: 'Datos generales'},
    {key: 'personal', label: 'Datos personales'},
    {key: 'tallas', label: 'Tallas de ropa'},
    {key: 'observaciones', label: 'Observaciones'},
]

export function DatosGeneralesFields({form, set, catalogos, puedeGestionar, esAdministrador}) {
    return (
        <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Rut"><Input value={form.Rut} onChange={set('Rut')} /></Field>
            <Field label="Teléfono"><Input value={form.Telefono} onChange={set('Telefono')} /></Field>
            <Field label="Teléfono de emergencia"><Input value={form.TelefonoEmergencia} onChange={set('TelefonoEmergencia')} /></Field>
            <Field label="Fecha de nacimiento"><Input type="date" value={form.FechaNacimiento} onChange={set('FechaNacimiento')} /></Field>
            <Field label="Nacionalidad"><Input value={form.Nacionalidad} onChange={set('Nacionalidad')} /></Field>
            <Field label="Cargo">
                <Select value={form.idCargo} onChange={set('idCargo')} disabled={!puedeGestionar}>
                    {catalogos?.cargos?.map((c) => <option key={c.id} value={c.id}>{c.Cargo}</option>)}
                </Select>
            </Field>
            <Field label="Estado">
                <Select value={form.idEstado} onChange={set('idEstado')} disabled={!puedeGestionar}>
                    {catalogos?.estados?.map((e) => <option key={e.id} value={e.id}>{e.Estado}</option>)}
                </Select>
            </Field>
            <Field label="Fecha de ingreso">
                <Input type="date" value={form.FechaReclutamiento} onChange={set('FechaReclutamiento')} disabled={!puedeGestionar} />
            </Field>
            {esAdministrador && (
                <Field label="Activo">
                    <Select value={form.Activo ? '1' : '0'} onChange={(e) => set('Activo')({target: {value: e.target.value === '1'}})}>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </Select>
                </Field>
            )}
        </div>
    )
}

export function DatosPersonalesFields({form, set}) {
    return (
        <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Dirección"><Input value={form.Direccion} onChange={set('Direccion')} /></Field>
            <Field label="Comuna"><Input value={form.Comuna} onChange={set('Comuna')} /></Field>
            <Field label="Nivel de estudio">
                <Select value={form.NivelEstudio} onChange={set('NivelEstudio')}>
                    <option value="">Seleccione...</option>
                    {NIVEL_ESTUDIO.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                </Select>
            </Field>
            <Field label="Ocupación"><Input value={form.Ocupacion} onChange={set('Ocupacion')} /></Field>
            <Field label="Lugar de ocupación"><Input value={form.LugarOcupacion} onChange={set('LugarOcupacion')} /></Field>
            <Field label="Estado civil">
                <Select value={form.EstadoCivil} onChange={set('EstadoCivil')}>
                    <option value="">Seleccione...</option>
                    {ESTADO_CIVIL.map((v) => <option key={v} value={v}>{v}</option>)}
                </Select>
            </Field>
            <Field label="Grupo sanguíneo">
                <Select value={form.GrupoSanguineo} onChange={set('GrupoSanguineo')}>
                    <option value="">Seleccione...</option>
                    {GRUPO_SANGUINEO.map((v) => <option key={v} value={v}>{v}</option>)}
                </Select>
            </Field>
        </div>
    )
}

export function TallasFields({form, set}) {
    return (
        <div className="grid gap-4 sm:grid-cols-3">
            <Field label="Zapatos"><Input value={form.TallaZapatos} onChange={set('TallaZapatos')} /></Field>
            <Field label="Pantalón"><Input value={form.TallaPantalon} onChange={set('TallaPantalon')} /></Field>
            <Field label="Camisa"><Input value={form.TallaCamisa} onChange={set('TallaCamisa')} /></Field>
            <Field label="Chaqueta"><Input value={form.TallaChaqueta} onChange={set('TallaChaqueta')} /></Field>
            <Field label="Sombrero"><Input value={form.TallaSombrero} onChange={set('TallaSombrero')} /></Field>
        </div>
    )
}

export function ObservacionesField({form, set}) {
    return <Textarea rows={6} value={form.Observaciones} onChange={set('Observaciones')} />
}

export function personaFormInicial(persona) {
    return {
        Rut: persona?.Rut ?? '',
        Telefono: persona?.Telefono ?? '',
        TelefonoEmergencia: persona?.TelefonoEmergencia ?? '',
        FechaNacimiento: persona?.FechaNacimiento ?? '',
        Nacionalidad: persona?.Nacionalidad ?? '',
        idCargo: persona?.idCargo ?? '',
        idEstado: persona?.idEstado ?? '',
        FechaReclutamiento: persona?.FechaReclutamiento ?? '',
        Activo: persona?.Activo ?? true,
        Direccion: persona?.Direccion ?? '',
        Comuna: persona?.Comuna ?? '',
        NivelEstudio: persona?.NivelEstudio ?? '',
        Ocupacion: persona?.Ocupacion ?? '',
        LugarOcupacion: persona?.LugarOcupacion ?? '',
        EstadoCivil: persona?.EstadoCivil ?? '',
        GrupoSanguineo: persona?.GrupoSanguineo ?? '',
        TallaZapatos: persona?.TallaZapatos ?? '',
        TallaPantalon: persona?.TallaPantalon ?? '',
        TallaCamisa: persona?.TallaCamisa ?? '',
        TallaChaqueta: persona?.TallaChaqueta ?? '',
        TallaSombrero: persona?.TallaSombrero ?? '',
        Observaciones: persona?.Observaciones ?? '',
    }
}
