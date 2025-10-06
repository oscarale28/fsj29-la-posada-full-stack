'use client';

import { useState, useTransition } from 'react';
import AccommodationGrid from '@/features/accommodation-management/components/accommodation-grid';
import { removeAccommodationFromUserAction } from '../actions/user-actions';

interface UserAccommodationsManagerProps {
    accommodations: any[];
    userName: string;
}

export default function UserAccommodationsManager({ accommodations, userName }: UserAccommodationsManagerProps) {
    const [isPending, startTransition] = useTransition();
    const [loadingId, setLoadingId] = useState<number | null>(null);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

    const handleRemove = async (accommodationId: number) => {
        const confirmed = window.confirm('¿Estás seguro de que querés eliminar este Hospedaje de tu lista?');

        if (!confirmed) return;

        setLoadingId(accommodationId);
        setMessage(null);

        startTransition(async () => {
            const result = await removeAccommodationFromUserAction(accommodationId);

            if (result.success) {
                setMessage({ type: 'success', text: result.message || '¡Hospedaje eliminado!' });
            } else {
                setMessage({ type: 'error', text: result.error || 'Error al eliminar' });
            }

            setLoadingId(null);

            // Clear message after 3 seconds
            setTimeout(() => setMessage(null), 3000);
        });
    };

    return (
        <div>
            {/* Message Toast */}
            {message && (
                <div className={`fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-xl animate-in slide-in-from-top-5 ${message.type === 'success'
                    ? 'bg-green-500 text-white'
                    : 'bg-red-500 text-white'
                    }`}>
                    <div className="flex items-center gap-2">
                        <span className="text-xl">{message.type === 'success' ? '✓' : '⚠️'}</span>
                        <p className="font-medium">{message.text}</p>
                    </div>
                </div>
            )}

            {/* Header */}
            <div className="mb-8">
                <h2 className="text-2xl font-bold text-gray-900 mb-2">
                    Mis Hospedajes Favoritos
                </h2>
                <p className="text-gray-600">
                    Aquí están todos los Hospedajes que has guardado, {userName}
                </p>
            </div>

            {/* Accommodations Grid */}
            {accommodations.length === 0 ? (
                <div className="text-center py-16 bg-white rounded-2xl shadow-sm border-2 border-dashed border-gray-300">
                    <div className="text-8xl mb-6">📍</div>
                    <h3 className="text-xl font-semibold text-gray-800 mb-3">
                        No tenés Hospedajes guardados todavía
                    </h3>
                    <p className="text-gray-600 mb-6 max-w-md mx-auto">
                        Empezá a explorar y guardá los Hospedajes que más te gusten para encontrarlos fácilmente después
                    </p>
                    <a
                        href="/"
                        className="inline-block bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg"
                    >
                        Explorar Hospedajes
                    </a>
                </div>
            ) : (
                <AccommodationGrid
                    accommodations={accommodations}
                    onRemove={handleRemove}
                    showRemoveButton={true}
                    isLoading={isPending}
                    loadingAccommodationId={loadingId}
                    emptyMessage="No se encontraron Hospedajes."
                />
            )}
        </div>
    );
}

