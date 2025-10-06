import { AccommodationGridProps } from '../types';
import AccommodationCard from './accommodation-card';

export default function AccommodationGrid({
    accommodations,
    onSelect,
    onRemove,
    showSelectButton = false,
    showRemoveButton = false,
    isLoading = false,
    loadingAccommodationId,
    emptyMessage = "No accommodations available.",
    title
}: AccommodationGridProps) {
    if (accommodations.length === 0) {
        return (
            <div className="text-center py-12">
                <div className="text-gray-500 text-lg mb-2">
                    {emptyMessage}
                </div>
                <p className="text-gray-400 text-sm">
                    Check back later for new accommodations.
                </p>
            </div>
        );
    }

    return (
        <div className="w-full">
            {title && (
                <h2 className="text-2xl font-bold text-gray-900 mb-6">
                    {title}
                </h2>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {accommodations.map((accommodation) => (
                    <AccommodationCard
                        key={accommodation.id}
                        accommodation={accommodation}
                        onSelect={onSelect}
                        onRemove={onRemove}
                        showSelectButton={showSelectButton}
                        showRemoveButton={showRemoveButton}
                        isLoading={isLoading && loadingAccommodationId === accommodation.id}
                    />
                ))}
            </div>
        </div>
    );
}