import './bootstrap'

import React from 'react'
import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { router } from '@inertiajs/react'
import LoadingScreen from './Components/LoadingScreen'

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
  setup({ el, App, props }) {
    const Root = () => {
        const [isNavigating, setIsNavigating] = React.useState(false);

        React.useEffect(() => {
            const startHandler = () => setIsNavigating(true);
            const finishHandler = () => setIsNavigating(false);
            
            router.on('start', startHandler);
            router.on('finish', finishHandler);
            
            return () => {
                // cleanup
            };
        }, []);

        return (
            <>
                <LoadingScreen show={isNavigating} />
                <App {...props} />
            </>
        );
    }
    if (!el) return
    createRoot(el).render(<Root />)
  },
})

