import { formatRupiah } from '@/helpers';

/**
 * Renders a rupiah amount so tables/widgets never re-format currency by hand.
 * @param {number|string} value - amount in IDR
 * @param {string} [className] - optional class for the wrapping span
 */
export default function Money({ value, className }) {
    return <span className={className}>{formatRupiah(value)}</span>;
}
