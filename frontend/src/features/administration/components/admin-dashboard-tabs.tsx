'use client';

import { useState } from 'react';
import AdminAccommodationForm from './admin-accommodation-form';
import AccommodationGrid from '@/features/accommodation-management/components/accommodation-grid';
import { Accommodation } from '@/features/accommodation-management/types';

interface AdminDashboardTabsProps {
    readonly allAccommodations: Accommodation[];
}

type TabType = 'add' | 'view';

export default function AdminDashboardTabs({ allAccommodations }: AdminDashboardTabsProps) {
    const [activeTab, setActiveTab] = useState<TabType>('view');

    // For now, we're showing all accommodations. Search/filter functionality can be added later if needed.
    const filteredAccommodations = allAccommodations;

    return (
        <div>
            {/* Tabs Navigation */}
            <div className="mb-8 border-b-2 border-gray-200">
                <div className="flex gap-1">
                    <button
                        onClick={() => setActiveTab('view')}
                        className={`px-6 py-3 font-semibold text-base transition-all relative ${activeTab === 'view'
                            ? 'text-blue-600'
                            : 'text-gray-600 hover:text-blue-500'
                            }`}
                    >
                        <span className="flex items-center gap-2">
                            🏠 Ver Todos los Hospedajes
                            {' '}
                            <span className="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-bold">
                                {allAccommodations.length}
                            </span>
                        </span>
                        {activeTab === 'view' && (
                            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500 to-blue-600" />
                        )}
                    </button>

                    <button
                        onClick={() => setActiveTab('add')}
                        className={`px-6 py-3 font-semibold text-base transition-all relative ${activeTab === 'add'
                            ? 'text-green-600'
                            : 'text-gray-600 hover:text-green-500'
                            }`}
                    >
                        <span className="flex items-center gap-2">
                            ➕ Agregar Hospedaje
                        </span>
                        {activeTab === 'add' && (
                            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-green-500 to-green-600" />
                        )}
                    </button>
                </div>
            </div>

            {/* Tab Content */}
            <div className="mt-6">
                {activeTab === 'add' ? (
                    <div>
                        {/* Add Accommodation Form */}
                        <AdminAccommodationForm />
                    </div>
                ) : (
                    <div>
                        {/* Header */}
                        <div className="mb-6">
                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                Catálogo de Hospedajes
                            </h2>
                            <p className="text-gray-600">
                                Administrá todos los Hospedajes disponibles en La Posada
                            </p>
                        </div>

                        {/* Accommodations Grid */}
                        {filteredAccommodations.length === 0 ? (
                            <div className="text-center py-20 bg-white rounded-2xl shadow-sm">
                                <div className="text-8xl mb-6">🔍</div>
                                <h3 className="text-2xl font-bold text-gray-800 mb-3">
                                    No se encontraron Hospedajes
                                </h3>
                                <p className="text-gray-600 mb-8 text-lg">
                                    {allAccommodations.length === 0
                                        ? 'Todavía no hay Hospedajes en el catálogo. ¡Agregá el primero!'
                                        : 'Intentá cambiar los filtros de búsqueda'}
                                </p>
                                {allAccommodations.length === 0 && (
                                    <button
                                        onClick={() => setActiveTab('add')}
                                        className="inline-block bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 transition-all shadow-lg"
                                    >
                                        Agregar Primer Hospedaje
                                    </button>
                                )}
                            </div>
                        ) : (
                            <div>
                                <AccommodationGrid
                                    accommodations={filteredAccommodations}
                                    showSelectButton={false}
                                    showRemoveButton={false}
                                    emptyMessage="No se encontraron Hospedajes."
                                />
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

