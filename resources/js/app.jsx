import './bootstrap';
import '../css/app.css';
import './i18n';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ThemeSwitcherProvider } from './Context/ThemeSwitcherContext';
import { OnlineStatusProvider } from './Context/OnlineStatusContext';

const appName = import.meta.env.VITE_APP_NAME || 'FAY COLLECTION';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        if (import.meta.env.PROD) {
            navigator.serviceWorker.register('/sw.js');
            return;
        }

        // A cache-first service worker can keep serving an outdated page or
        // bundle while Vite is providing newer modules during development.
        // Remove registrations and this app's old caches so local changes are
        // always loaded from the development server.
        navigator.serviceWorker
            .getRegistrations()
            .then((registrations) =>
                Promise.all(registrations.map((registration) => registration.unregister()))
            )
            .then(() => caches.keys())
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key.startsWith('pos-cache-'))
                        .map((key) => caches.delete(key))
                )
            );
    });
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <ThemeSwitcherProvider>
                <OnlineStatusProvider>
                    <App {...props} />
                </OnlineStatusProvider>
            </ThemeSwitcherProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
