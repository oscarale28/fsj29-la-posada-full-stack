'use server';

/**
 * Server actions for accommodation management
 * Centralized functions for fetching accommodations from the API
 */

interface GetAccommodationsParams {
    limit?: number;
    page?: number;
    search?: string;
    location?: string;
}

/**
 * Fetch all accommodations from the API
 * @param params - Optional parameters for filtering and pagination
 * @returns Array of accommodations
 */
export async function getAllAccommodations(params?: GetAccommodationsParams) {
    try {
        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

        // Build query string
        const queryParams = new URLSearchParams();
        if (params?.limit) queryParams.append('limit', params.limit.toString());
        if (params?.page) queryParams.append('page', params.page.toString());
        if (params?.search) queryParams.append('search', params.search);
        if (params?.location) queryParams.append('location', params.location);

        const queryString = queryParams.toString();
        const urlPath = queryString ? `?${queryString}` : '';
        const url = `${apiUrl}/accommodations${urlPath}`;

        const response = await fetch(url, {
            cache: 'no-store',
        });

        if (!response.ok) {
            throw new Error('Failed to fetch accommodations');
        }

        const data = await response.json();
        return data.accommodations || [];
    } catch (error) {
        console.error('Error fetching accommodations:', error);
        return [];
    }
}

/**
 * Fetch a single accommodation by ID
 * @param id - Accommodation ID
 * @returns Accommodation object or null
 */
export async function getAccommodationById(id: string | number) {
    try {
        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
        const response = await fetch(`${apiUrl}/accommodations/${id}`, {
            cache: 'no-store',
        });

        if (!response.ok) {
            throw new Error('Failed to fetch accommodation');
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching accommodation:', error);
        return null;
    }
}

