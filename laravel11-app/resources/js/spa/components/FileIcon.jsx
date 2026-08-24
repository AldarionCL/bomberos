import {
    DocumentIcon,
    DocumentTextIcon,
    PhotoIcon,
    TableCellsIcon,
} from '@heroicons/react/24/outline'

const POR_EXTENSION = {
    pdf: {icon: DocumentTextIcon, tone: 'text-rose-500 bg-rose-50 dark:bg-rose-500/10'},
    doc: {icon: DocumentTextIcon, tone: 'text-blue-500 bg-blue-50 dark:bg-blue-500/10'},
    docx: {icon: DocumentTextIcon, tone: 'text-blue-500 bg-blue-50 dark:bg-blue-500/10'},
    xls: {icon: TableCellsIcon, tone: 'text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10'},
    xlsx: {icon: TableCellsIcon, tone: 'text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10'},
    csv: {icon: TableCellsIcon, tone: 'text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10'},
    png: {icon: PhotoIcon, tone: 'text-violet-500 bg-violet-50 dark:bg-violet-500/10'},
    jpg: {icon: PhotoIcon, tone: 'text-violet-500 bg-violet-50 dark:bg-violet-500/10'},
    jpeg: {icon: PhotoIcon, tone: 'text-violet-500 bg-violet-50 dark:bg-violet-500/10'},
}

export default function FileIcon({extension, className = 'h-5 w-5'}) {
    const {icon: Icon, tone} = POR_EXTENSION[extension?.toLowerCase()] ?? {icon: DocumentIcon, tone: 'text-slate-500 bg-slate-100 dark:bg-slate-700'}

    return (
        <span className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${tone}`}>
            <Icon className={className} />
        </span>
    )
}
