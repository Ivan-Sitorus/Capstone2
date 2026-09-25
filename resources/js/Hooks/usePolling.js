import { useEffect, useRef } from 'react';

export default function usePolling(reload, intervalMs = 30000, { immediate = false, enabled = true } = {}) {
    const reloadRef = useRef(reload);

    useEffect(() => {
        reloadRef.current = reload;
    });

    useEffect(() => {
        if (!enabled) return;

        const run = () => {
            if (document.visibilityState === 'hidden') return;
            reloadRef.current();
        };

        if (immediate) run();

        const id = setInterval(run, intervalMs);
        const onVisible = () => {
            if (document.visibilityState === 'visible') run();
        };

        document.addEventListener('visibilitychange', onVisible);

        return () => {
            clearInterval(id);
            document.removeEventListener('visibilitychange', onVisible);
        };
    }, [enabled]);
}
