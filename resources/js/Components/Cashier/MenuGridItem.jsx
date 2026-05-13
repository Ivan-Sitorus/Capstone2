import { formatRupiah } from '@/helpers';

export default function MenuGridItem({ menu, onAdd }) {
    return (
        <div
            onClick={() => onAdd(menu)}
            className="bg-white border border-border rounded-lg p-5 cursor-pointer flex flex-col gap-1.5 text-center transition-all duration-150 hover:shadow-md hover:-translate-y-0.5"
        >
            <div className="text-xs uppercase text-muted-foreground tracking-wide font-semibold">
                {menu.category?.name}
            </div>
            <div className="text-base font-bold text-foreground leading-tight">
                {menu.name}
            </div>
            <div className="text-sm font-bold text-primary">
                {formatRupiah(menu.price)}
            </div>
        </div>
    );
}
