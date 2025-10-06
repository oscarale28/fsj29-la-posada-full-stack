'use client';

import { useEffect } from 'react';
import Link from 'next/link';

export default function Error({
    error,
    reset,
}: {
    error: Error & { digest?: string };
    reset: () => void;
}) {
    useEffect(() => {
        // Log the error to an error reporting service
        console.error('Application error:', error);
    }, [error]);

    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-blue-50 flex items-center justify-center px-4">
            <div className="text-center max-w-2xl">
                {/* Error Icon */}
                <div className="mb-8">
                    <div className="text-9xl mb-6">⚠️</div>
                </div>

                {/* Message */}
                <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    ¡Algo salió mal!
                </h2>
                <p className="text-xl text-gray-600 mb-4">
                    Ocurrió un error inesperado. No te preocupes, estamos trabajando para solucionarlo.
                </p>

                {/* Error details (only in development) */}
                {process.env.NODE_ENV === 'development' && (
                    <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-8 text-left">
                        <p className="text-sm font-mono text-red-700">
                            {error.message}
                        </p>
                    </div>
                )}

                {/* Actions */}
                <div className="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                    <button
                        onClick={reset}
                        className="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-xl"
                    >
                        🔄 Intentar de Nuevo
                    </button>
                    <Link
                        href="/"
                        className="bg-white text-gray-900 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-gray-50 transition-all border-2 border-gray-300 inline-block"
                    >
                        🏠 Volver al Inicio
                    </Link>
                </div>

                {/* Help text */}
                <div className="mt-12 pt-8 border-t border-gray-200">
                    <p className="text-gray-600">
                        Si el problema persiste, por favor{' '}
                        <a href="mailto:info@laposada.sv" className="text-orange-600 hover:text-orange-700 font-medium hover:underline">
                            contactanos
                        </a>
                    </p>
                </div>
            </div>
        </div>
    );
}

