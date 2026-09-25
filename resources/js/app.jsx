import './bootstrap';
import '../css/app.css';
import './i18n';

import { createRoot } from 'react-dom/client';
import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Toaster, toast } from 'react-hot-toast';
import { ThemeSwitcherProvider } from './Context/ThemeSwitcherContext';
import { OnlineStatusProvider } from './Context/OnlineStatusContext';

const appName = import.meta.env.VITE_APP_NAME || 'FAY COLLECTION';

// Inertia's progress bar shows that a navigation or action is in progress.
// These handlers ensure a failed request also gets a visible, actionable
// message instead of appearing to do nothing.
router.on('networkError', () => {
    toast.error('Unable to reach the server. Check your connection and try again.');
    return false;
});

router.on('httpException', () => {
    toast.error('The request could not be completed. Please try again.');
    return false;
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        if (import.meta.env.PROD) {
            navigator.serviceWorker
                .register(`/sw.js?v=${Date.now()}`)
                .catch(() => {
                    toast.error('Offline support is unavailable.');
                });
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
            )
            .catch(() => {
                // Service-worker cleanup is best effort during development.
            });
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
                    <Toaster position="top-right" />
                </OnlineStatusProvider>
            </ThemeSwitcherProvider>
        );
    },
    progress: {
        color: '#4B5563',
        delay: 0,
        showSpinner: true,
    },
});
