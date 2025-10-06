import { ApiError } from '../types';
import { cookies } from 'next/headers';

class ApiClient {
    private readonly baseURL: string;

    constructor(baseURL: string = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000') {
        this.baseURL = baseURL;
    }

    private async request<T>(
        endpoint: string,
        options: RequestInit = {}
    ): Promise<T> {
        const url = `${this.baseURL}${endpoint}`;

        const config: RequestInit = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
            ...options,
        };

        // Add auth token if available (server-side)
        if (typeof window === 'undefined') {
            try {
                const cookieStore = await cookies();
                const token = cookieStore.get('auth_token');
                if (token) {
                    config.headers = {
                        ...config.headers,
                        Authorization: `Bearer ${token.value}`,
                    };
                }
            } catch (error) {
                // Cookies might not be available in all contexts
                console.warn('Could not access cookies for auth token:', error);
            }
        } else {
            // Client-side fallback
            const token = localStorage.getItem('auth_token');
            if (token) {
                config.headers = {
                    ...config.headers,
                    Authorization: `Bearer ${token}`,
                };
            }
        }

        try {
            const response = await fetch(url, config);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                const error: ApiError = {
                    message: errorData.message || `HTTP ${response.status}: ${response.statusText}`,
                    code: errorData.code || response.status.toString(),
                    details: errorData.details,
                    status: response.status,
                };
                throw error;
            }

            const data = await response.json();
            console.log('API Client raw response:', data);
            return data as T;
        } catch (error) {
            if (error instanceof Error && !(error as ApiError).code) {
                const apiError: ApiError = {
                    message: error.message,
                    code: 'NETWORK_ERROR',
                };
                throw apiError;
            }
            throw error;
        }
    }

    async get<T>(endpoint: string, options?: RequestInit): Promise<T> {
        return this.request<T>(endpoint, { ...options, method: 'GET' });
    }

    async post<T>(endpoint: string, data?: unknown, options?: RequestInit): Promise<T> {
        return this.request<T>(endpoint, {
            ...options,
            method: 'POST',
            body: data ? JSON.stringify(data) : undefined,
        });
    }

    async put<T>(endpoint: string, data?: unknown, options?: RequestInit): Promise<T> {
        return this.request<T>(endpoint, {
            ...options,
            method: 'PUT',
            body: data ? JSON.stringify(data) : undefined,
        });
    }

    async delete<T>(endpoint: string, options?: RequestInit): Promise<T> {
        return this.request<T>(endpoint, { ...options, method: 'DELETE' });
    }
}

export const apiClient = new ApiClient();
export default apiClient;