// Shared TypeScript types across the application

// API Response types
export interface ApiResponse<T = unknown> {
    data: T;
    message?: string;
    success: boolean;
}

export interface ApiError {
    message: string;
    code?: string;
    details?: unknown;
    status?: number;
}

// Pagination types
export interface PaginationParams {
    page: number;
    limit: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    pagination: {
        page: number;
        limit: number;
        total: number;
        totalPages: number;
    };
}

// Common UI types
export interface LoadingState {
    isLoading: boolean;
    error?: string;
}

// Re-export domain types for convenience
export type { User, AuthResponse } from '../../features/authentication/types';
export type { Accommodation } from '../../features/accommodation-management/types';