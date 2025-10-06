'use client';

import { useState } from 'react';
import AccommodationGrid from './accommodation-grid';
import { useAccommodations, useDeleteAccommodation } from '../hooks/use-accommodations';
import { AccommodationFilterData } from '../types';
import { PaginationParams } from '@/shared/types';

export default function AccommodationListPage() {
    const [filters, setFilters] = useState<AccommodationFilterData & PaginationParams>({
        page: 1,
        limit: 12,
    });

    const { data, isLoading, error } = useAccommodations(filters);
    const deleteAccommodationMutation = useDeleteAccommodation();

    const handleRemoveAccommodation = async (accommodationId: string) => {
        if (window.confirm('Are you sure you want to remove this accommodation?')) {
            await deleteAccommodationMutation.mutateAsync(accommodationId);
        }
    };

    if (error) {
        return (
            <div className="container mx-auto px-4 py-8">
                <div className="text-center">
                    <p className="text-red-600">Error loading accommodations: {error.message}</p>
                </div>
            </div>
        );
    }

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-900 mb-4">
                    Available Accommodations
                </h1>

                {/* Filter controls can be added here */}
            </div>

            {isLoading ? (
                <div className="flex justify-center items-center py-12">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                </div>
            ) : (
                <AccommodationGrid
                    accommodations={data?.data || []}
                    onRemove={handleRemoveAccommodation}
                    showRemoveButton={true}
                    isLoading={deleteAccommodationMutation.isPending}
                    loadingAccommodationId={deleteAccommodationMutation.variables}
                    emptyMessage="No accommodations found. Try adjusting your filters."
                />
            )}

            {/* Pagination controls can be added here */}
            {data?.pagination && data.pagination.totalPages > 1 && (
                <div className="mt-8 flex justify-center">
                    <div className="flex space-x-2">
                        {Array.from({ length: data.pagination.totalPages }, (_, i) => i + 1).map((page) => (
                            <button
                                key={page}
                                onClick={() => setFilters((prev: AccommodationFilterData & PaginationParams) => ({ ...prev, page }))}
                                className={`px-3 py-2 rounded-md ${page === filters.page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                    }`}
                            >
                                {page}
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}