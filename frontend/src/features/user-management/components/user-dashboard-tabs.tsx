'use client';

import { useState, useTransition } from 'react';
import AccommodationGrid from '@/features/accommodation-management/components/accommodation-grid';
import { addAccommodationToUserAction, removeAccommodationFromUserAction } from '../actions/user-actions';

interface UserDashboardTabsProps {
    readonly userAccommodations: any[];
    readonly availableAccommodations: any[];
    readonly userName: string;
}

type TabType = 'available' | 'saved';

export default function UserDashboardTabs({
    userAccommodations,
    availableAccommodations,
    userName
}: UserDashboardTabsProps) {
    const [activeTab, setActiveTab] = useState<TabType>('available');
    const [isPending, startTransition] = useTransition();
    const [loadingId, setLoadingId] = useState<number | null>(null);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

    // Filter out accommodations that are already saved
    const savedIds = new Set(userAccommodations.map(acc => acc.id));
    const availableToSave = availableAccommodations.filter(acc => !savedIds.has(acc.id));

    const handleSave = async (accommodationId: string) => {
        setLoadingId(Number(accommodationId));
        setMessage(null);

        startTransition(async () => {
            const result = await addAccommodationToUserAction(Number(accommodationId));

            if (result.success) {
                setMessage({ type: 'success', text: result.message || '¡Hospedaje guardado en tu lista!' });
            } else {
                setMessage({ type: 'error', text: result.error || 'Error al guardar' });
            }

            setLoadingId(null);

            // Clear message after 3 seconds
            setTimeout(() => setMessage(null), 3000);
        });
    };

    const handleRemove = async (accommodationId: string) => {
        const confirmed = window.confirm('¿Estás seguro de que querés eliminar este hospedaje de tu lista?');

        if (!confirmed) return;

        setLoadingId(Number(accommodationId));
        setMessage(null);

        startTransition(async () => {
            const result = await removeAccommodationFromUserAction(Number(accommodationId));

            if (result.success) {
                setMessage({ type: 'success', text: result.message || '¡Hospedaje eliminado de tu lista!' });
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

            {/* Tabs Navigation */}
            <div className="mb-8 border-b-2 border-gray-200">
                <div className="flex gap-1">
                    <button
                        onClick={() => setActiveTab('available')}
                        className={`px-6 py-3 font-semibold text-base transition-all relative ${activeTab === 'available'
                            ? 'text-orange-600'
                            : 'text-gray-600 hover:text-orange-500'
                            }`}
                    >
                        <span className="flex items-center gap-2">
                            🏠 Hospedajes Disponibles
                            {' '}
                            <span className="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded-full font-bold">
                                {availableToSave.length}
                            </span>
                        </span>
                        {activeTab === 'available' && (
                            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-orange-500 to-orange-600" />
                        )}
                    </button>

                    <button
                        onClick={() => setActiveTab('saved')}
                        className={`px-6 py-3 font-semibold text-base transition-all relative ${activeTab === 'saved'
                            ? 'text-blue-600'
                            : 'text-gray-600 hover:text-blue-500'
                            }`}
                    >
                        <span className="flex items-center gap-2">
                            📍 Mis Hospedajes Guardados
                            {' '}
                            <span className="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-bold">
                                {userAccommodations.length}
                            </span>
                        </span>
                        {activeTab === 'saved' && (
                            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500 to-blue-600" />
                        )}
                    </button>
                </div>
            </div>

            {/* Tab Content */}
            <div className="mt-6">
                {activeTab === 'available' ? (
                    <div>
                        {/* Header */}
                        <div className="mb-6">
                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                Explorá y Guardá Hospedajes
                            </h2>
                            <p className="text-gray-600">
                                Descubrí todos los Hospedajes disponibles y guardalos para acceder rápidamente después
                            </p>
                        </div>

                        {/* Available Accommodations Grid */}
                        {availableToSave.length === 0 ? (
                            <div className="text-center py-20 bg-white rounded-2xl shadow-sm">
                                <div className="text-8xl mb-6">🏡</div>
                                <h3 className="text-2xl font-bold text-gray-800 mb-3">
                                    Todavía no hay Hospedajes disponibles
                                </h3>
                                <p className="text-gray-600 mb-8 text-lg">
                                    Volvé pronto para ver nuevos hospedajes
                                </p>
                            </div>
                        ) : (
                            <AccommodationGrid
                                accommodations={availableToSave}
                                onSelect={handleSave}
                                showSelectButton={true}
                                isLoading={isPending}
                                loadingAccommodationId={loadingId?.toString()}
                                emptyMessage="No hay Hospedajes disponibles en este momento."
                            />
                        )}
                    </div>
                ) : (
                    <div>
                        {/* Header */}
                        <div className="mb-6">
                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                Mis Hospedajes Guardados
                            </h2>
                            <p className="text-gray-600">
                                Aquí están todos los Hospedajes que guardaste, {userName}
                            </p>
                        </div>

                        {/* User Accommodations Grid */}
                        {userAccommodations.length === 0 ? (
                            <div className="text-center py-16 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-sm border-2 border-blue-200">
                                <div className="text-8xl mb-6">📍</div>
                                <h3 className="text-xl font-semibold text-gray-800 mb-3">
                                    No tenés Hospedajes guardados todavía
                                </h3>
                                <p className="text-gray-600 mb-6 max-w-md mx-auto">
                                    Empezá a explorar y guardá los Hospedajes que más te gusten para encontrarlos fácilmente después
                                </p>
                                <button
                                    onClick={() => setActiveTab('available')}
                                    className="inline-block bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg"
                                >
                                    Explorar Hospedajes Disponibles
                                </button>
                            </div>
                        ) : (
                            <AccommodationGrid
                                accommodations={userAccommodations}
                                onRemove={handleRemove}
                                showRemoveButton={true}
                                isLoading={isPending}
                                loadingAccommodationId={loadingId?.toString()}
                                emptyMessage="No se encontraron Hospedajes."
                            />
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

