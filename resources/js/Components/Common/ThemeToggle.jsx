import { useState, useEffect } from 'react';
import { Sun, Moon, Monitor } from 'lucide-react';

const THEME_KEY = 'w9-theme';
const MODES = ['light', 'dark', 'system'];

export default function ThemeToggle() {
  const [theme, setTheme] = useState(() => {
    if (typeof window === 'undefined') return 'system';
    return localStorage.getItem(THEME_KEY) || 'system';
  });

  useEffect(() => {
    const root = document.documentElement;
    const apply = (isDark) => root.classList.toggle('dark', isDark);

    const update = () => {
      localStorage.setItem(THEME_KEY, theme);
      if (theme === 'dark') apply(true);
      else if (theme === 'light') apply(false);
      else apply(window.matchMedia('(prefers-color-scheme: dark)').matches);
    };
    update();

    if (theme === 'system') {
      const mq = window.matchMedia('(prefers-color-scheme: dark)');
      const handler = (e) => apply(e.matches);
      mq.addEventListener('change', handler);
      return () => mq.removeEventListener('change', handler);
    }
  }, [theme]);

  const cycle = () => setTheme(prev => MODES[(MODES.indexOf(prev) + 1) % MODES.length]);

  const Icon = theme === 'light' ? Sun : theme === 'dark' ? Moon : Monitor;

  return (
    <button onClick={cycle} title={`Tema: ${theme}`}
      style={{
        width: 36, height: 36, borderRadius: 8, border: '1px solid #E2E8F0',
        background: 'white', cursor: 'pointer', display: 'inline-flex',
        alignItems: 'center', justifyContent: 'center', color: '#475569',
      }}>
      <Icon size={18} />
    </button>
  );
}
