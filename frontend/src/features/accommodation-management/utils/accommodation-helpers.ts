/**
 * Utility functions for accommodation management
 */

/**
 * Ensure amenities is always an array
 * Handles cases where amenities might be a JSON string or already an array
 */
export function normalizeAmenities(amenities: any): string[] {
    // If it's already an array, return it
    if (Array.isArray(amenities)) {
        return amenities;
    }

    // If it's a string, try to parse it as JSON
    if (typeof amenities === 'string') {
        try {
            const parsed = JSON.parse(amenities);
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            // If it's not valid JSON, return empty array
            return [];
        }
    }

    // If it's null, undefined, or anything else, return empty array
    return [];
}

/**
 * Check if accommodation has amenities
 */
export function hasAmenities(amenities: any): boolean {
    const normalized = normalizeAmenities(amenities);
    return normalized.length > 0;
}

