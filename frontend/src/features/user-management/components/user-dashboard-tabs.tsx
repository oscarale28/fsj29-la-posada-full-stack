'use client';

import { useState, useTransition } from 'react';
import AccommodationGrid from '@/features/accommodation-management/components/accommodation-grid';
import { Accommodation } from '@/features/accommodation-management/types';
import { addAccommodationToUserAction, removeAccommodationFromUserAction } from '../actions/user-actions';

interface UserDashboardTabsProps {
    readonly userAccommodations: Accommodation[];
    readonly availableAccommodations: Accommodation[];
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
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-destructive text-destructive-foreground'
                    }`}>
                    <div className="flex items-center gap-2">
                        <span className="text-xl">{message.type === 'success' ? '✓' : '⚠️'}</span>
                        <p className="font-medium">{message.text}</p>
                    </div>
                </div>
            )}

            {/* Tabs Navigation */}
            <div className="mb-8 border-b-2 border-border">
                <div className="flex gap-1">
                    <button
                        onClick={() => setActiveTab('available')}
                        className={`px-6 py-3 font-semibold text-base transition-all relative ${activeTab === 'available'
                            ? 'text-primary'
                            : 'text-muted-foreground hover:text-primary'
                            }`}
                    >
                        <span className="flex items-center gap-2">
                            🏠 Hospedajes Disponibles
                            {' '}
                            <span className="bg-primary/10 text-primary text-xs px-2 py-0.5 rounded-full font-bold border border-primary/20">
                                {availableToSave.length}
                            </span>
                        </span>
                        {activeTab === 'available' && (
                            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-primary" />
                        )}
                    </button>

                    <button
                        onClick={() => setActiveTab('saved')}
                        className={`px-6 py-3 font-semibold text-base transition-all relative ${activeTab === 'saved'
                            ? 'text-secondary'
                            : 'text-muted-foreground hover:text-secondary'
                            }`}
                    >
                        <span className="flex items-center gap-2">
                            📍 Mis Hospedajes Guardados
                            {' '}
                            <span className="bg-secondary/10 text-secondary text-xs px-2 py-0.5 rounded-full font-bold border border-secondary/20">
                                {userAccommodations.length}
                            </span>
                        </span>
                        {activeTab === 'saved' && (
                            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-secondary" />
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
                            <h2 className="text-2xl font-bold text-foreground mb-2">
                                Explorá y Guardá Hospedajes
                            </h2>
                            <p className="text-muted-foreground">
                                Descubrí todos los Hospedajes disponibles y guardalos para acceder rápidamente después
                            </p>
                        </div>

                        {/* Available Accommodations Grid */}
                        {availableToSave.length === 0 ? (
                            <div className="text-center py-20 bg-card rounded-2xl shadow-sm border border-border">
                                <div className="text-8xl mb-6">🏡</div>
                                <h3 className="text-2xl font-bold text-card-foreground mb-3">
                                    Todavía no hay Hospedajes disponibles
                                </h3>
                                <p className="text-muted-foreground mb-8 text-lg">
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
                            <h2 className="text-2xl font-bold text-foreground mb-2">
                                Mis Hospedajes Guardados
                            </h2>
                            <p className="text-muted-foreground">
                                Aquí están todos los Hospedajes que guardaste, {userName}
                            </p>
                        </div>

                        {/* User Accommodations Grid */}
                        {userAccommodations.length === 0 ? (
                            <div className="text-center py-16 bg-gradient-to-br from-secondary/10 to-accent/10 rounded-2xl shadow-sm border-2 border-secondary/20">
                                <div className="text-8xl mb-6">📍</div>
                                <h3 className="text-xl font-semibold text-card-foreground mb-3">
                                    No tenés Hospedajes guardados todavía
                                </h3>
                                <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                                    Empezá a explorar y guardá los Hospedajes que más te gusten para encontrarlos fácilmente después
                                </p>
                                <button
                                    onClick={() => setActiveTab('available')}
                                    className="inline-block bg-primary text-primary-foreground px-6 py-3 rounded-lg font-semibold hover:bg-primary/90 transition-all shadow-lg"
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

