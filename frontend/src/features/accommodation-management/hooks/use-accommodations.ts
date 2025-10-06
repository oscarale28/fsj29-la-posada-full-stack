import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Accommodation, AccommodationFormData } from '../types';
import { PaginatedResponse, PaginationParams } from '@/shared/types';
import { apiClient } from '@/shared/utils/api-client';
import { AccommodationFilterData } from '../validations';

// Query keys
export const accommodationKeys = {
    all: ['accommodations'] as const,
    lists: () => [...accommodationKeys.all, 'list'] as const,
    list: (filters?: AccommodationFilterData & PaginationParams) =>
        [...accommodationKeys.lists(), filters] as const,
    details: () => [...accommodationKeys.all, 'detail'] as const,
    detail: (id: string) => [...accommodationKeys.details(), id] as const,
};

// Accommodation API functions
const accommodationApi = {
    getAccommodations: async (
        params?: AccommodationFilterData & PaginationParams
    ): Promise<PaginatedResponse<Accommodation>> => {
        const searchParams = new URLSearchParams();

        if (params) {
            Object.entries(params).forEach(([key, value]) => {
                if (value !== undefined && value !== null) {
                    if (Array.isArray(value)) {
                        value.forEach(item => searchParams.append(key, item.toString()));
                    } else {
                        searchParams.append(key, value.toString());
                    }
                }
            });
        }

        const response = await apiClient.get<any>(
            `/accommodations?${searchParams.toString()}`
        );
        // Backend returns the data directly, not wrapped in {data: ...}
        return response;
    },

    getAccommodation: async (id: string): Promise<Accommodation> => {
        const response = await apiClient.get<any>(`/accommodations/${id}`);
        return response;
    },

    createAccommodation: async (data: AccommodationFormData): Promise<Accommodation> => {
        const response = await apiClient.post<any>('/accommodations', data);
        return response;
    },

    updateAccommodation: async (id: string, data: Partial<AccommodationFormData>): Promise<Accommodation> => {
        const response = await apiClient.put<any>(`/accommodations/${id}`, data);
        return response;
    },

    deleteAccommodation: async (id: string): Promise<void> => {
        await apiClient.delete(`/accommodations/${id}`);
    },
};

// Custom hooks
export function useAccommodations(
    filters?: AccommodationFilterData & PaginationParams
) {
    return useQuery({
        queryKey: accommodationKeys.list(filters),
        queryFn: () => accommodationApi.getAccommodations(filters),
        staleTime: 5 * 60 * 1000, // 5 minutes
    });
}

export function useAccommodation(id: string) {
    return useQuery({
        queryKey: accommodationKeys.detail(id),
        queryFn: () => accommodationApi.getAccommodation(id),
        enabled: !!id,
    });
}

export function useCreateAccommodation() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: accommodationApi.createAccommodation,
        onSuccess: () => {
            // Invalidate and refetch accommodations list
            queryClient.invalidateQueries({ queryKey: accommodationKeys.lists() });
        },
    });
}

export function useUpdateAccommodation() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({ id, data }: { id: string; data: Partial<AccommodationFormData> }) =>
            accommodationApi.updateAccommodation(id, data),
        onSuccess: (updatedAccommodation) => {
            // Update the specific accommodation in cache
            queryClient.setQueryData(
                accommodationKeys.detail(updatedAccommodation.id),
                updatedAccommodation
            );

            // Invalidate lists to ensure consistency
            queryClient.invalidateQueries({ queryKey: accommodationKeys.lists() });
        },
    });
}

export function useDeleteAccommodation() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: accommodationApi.deleteAccommodation,
        onSuccess: (_, deletedId) => {
            // Remove from cache
            queryClient.removeQueries({ queryKey: accommodationKeys.detail(deletedId) });

            // Invalidate lists
            queryClient.invalidateQueries({ queryKey: accommodationKeys.lists() });
        },
    });
}