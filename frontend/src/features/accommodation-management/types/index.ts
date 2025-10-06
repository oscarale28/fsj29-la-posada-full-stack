// Accommodation domain types
export interface Accommodation {
    id: string;
    title: string;
    description: string;
    price: number;
    location: string;
    imageUrl?: string;
    amenities: string[];
    createdAt: string;
    updatedAt: string;
}

export interface AccommodationCardProps {
    accommodation: Accommodation;
    onSelect?: (accommodationId: string) => void;
    onRemove?: (accommodationId: string) => void;
    showSelectButton?: boolean;
    showRemoveButton?: boolean;
    isLoading?: boolean;
}

export interface AccommodationGridProps {
    accommodations: Accommodation[];
    onSelect?: (accommodationId: string) => void;
    onRemove?: (accommodationId: string) => void;
    showSelectButton?: boolean;
    showRemoveButton?: boolean;
    isLoading?: boolean;
    loadingAccommodationId?: string;
    emptyMessage?: string;
    title?: string;
}

export interface AccommodationFilterData {
    search?: string;
    location?: string;
    minPrice?: number;
    maxPrice?: number;
    amenities?: string[];
}

// Re-export validation types
export type { AccommodationFormData } from '../validations';