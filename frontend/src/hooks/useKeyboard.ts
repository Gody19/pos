import { useEffect } from 'react';

type Handler = (event: KeyboardEvent) => void;

export function useKeyboard(handlers: Record<string, Handler>, deps: unknown[] = []) {
  useEffect(() => {
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape' || event.key === 'F1' || event.key === 'F2' || event.key === 'F4' || event.key === 'F8') {
        const handler = handlers[event.key];
        if (handler) {
          event.preventDefault();
          handler(event);
        }
      }
    };

    window.addEventListener('keydown', onKeyDown);

    return () => window.removeEventListener('keydown', onKeyDown);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, deps);
}