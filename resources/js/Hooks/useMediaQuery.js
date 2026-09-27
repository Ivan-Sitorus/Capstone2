import { useEffect, useState } from 'react';

/**
 * Batas layar ponsel untuk area kasir.
 * Desktop (>= 768px) memakai sidebar tetap; di bawahnya memakai drawer/sheet.
 */
export const MOBILE_BREAKPOINT_QUERY = '(max-width: 767px)';

export default function useMediaQuery(query) {
    const [matches, setMatches] = useState(() => {
        if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
            return false;
        }
        return window.matchMedia(query).matches;
    });

    useEffect(() => {
        if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
            return undefined;
        }

        const mediaQueryList = window.matchMedia(query);
        const handleChange = (event) => setMatches(event.matches);

        setMatches(mediaQueryList.matches);

        if (typeof mediaQueryList.addEventListener === 'function') {
            mediaQueryList.addEventListener('change', handleChange);
            return () => mediaQueryList.removeEventListener('change', handleChange);
        }

        // Fallback browser lama (Safari < 14)
        mediaQueryList.addListener(handleChange);
        return () => mediaQueryList.removeListener(handleChange);
    }, [query]);

    return matches;
}

export function useIsMobile() {
    return useMediaQuery(MOBILE_BREAKPOINT_QUERY);
}
