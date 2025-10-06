import Image from 'next/image';
import { AccommodationCardProps } from '../types';
import { normalizeAmenities } from '../utils/accommodation-helpers';

export default function AccommodationCard({
    accommodation,
    onSelect,
    onRemove,
    showSelectButton = false,
    showRemoveButton = false,
    isLoading = false
}: Readonly<AccommodationCardProps>) {
    const handleSelect = () => {
        if (onSelect && !isLoading) {
            onSelect(accommodation.id);
        }
    };

    const handleRemove = () => {
        if (onRemove && !isLoading) {
            onRemove(accommodation.id);
        }
    };

    // Normalize amenities to ensure it's always an array
    const amenities = normalizeAmenities(accommodation.amenities);

    return (
        <div className="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
            {accommodation.imageUrl ? (
                <div className="aspect-video w-full overflow-hidden relative bg-gradient-to-br from-orange-100 to-blue-100">
                    <Image
                        src={accommodation.imageUrl}
                        alt={accommodation.title}
                        fill
                        className="object-cover"
                    />
                </div>
            ) : (
                <div className="aspect-video w-full overflow-hidden relative bg-gradient-to-br from-orange-400 to-blue-500 flex items-center justify-center">
                    <span className="text-6xl">🏨</span>
                </div>
            )}

            <div className="p-5">
                <h3 className="text-xl font-bold text-gray-900 mb-2 line-clamp-1">
                    {accommodation.title}
                </h3>

                <div className="flex items-center text-gray-600 mb-3">
                    <span className="mr-1">📍</span>
                    <p className="text-sm font-medium">
                        {accommodation.location}
                    </p>
                </div>

                <p className="text-gray-600 text-sm mb-4 line-clamp-2">
                    {accommodation.description}
                </p>

                <div className="flex items-baseline justify-between mb-4">
                    <div>
                        <span className="text-2xl font-bold bg-gradient-to-r from-orange-600 to-orange-500 bg-clip-text text-transparent">
                            ${accommodation.price}
                        </span>
                        <span className="text-sm text-gray-500 ml-1">/ noche</span>
                    </div>
                </div>

                {amenities.length > 0 && (
                    <div className="mb-4">
                        <div className="flex flex-wrap gap-2">
                            {amenities.slice(0, 3).map((amenity) => (
                                <span
                                    key={amenity}
                                    className="inline-block bg-blue-50 text-blue-700 text-xs font-medium px-3 py-1 rounded-full"
                                >
                                    {amenity}
                                </span>
                            ))}
                            {amenities.length > 3 && (
                                <span className="inline-block bg-gray-100 text-gray-600 text-xs font-medium px-3 py-1 rounded-full">
                                    +{amenities.length - 3} más
                                </span>
                            )}
                        </div>
                    </div>
                )}

                <div className="flex gap-2">
                    {showSelectButton && (
                        <button
                            onClick={handleSelect}
                            disabled={isLoading}
                            className="flex-1 bg-gradient-to-r from-orange-500 to-orange-600 text-white px-4 py-2.5 rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg"
                        >
                            {isLoading ? 'Guardando...' : '💾 Guardar'}
                        </button>
                    )}

                    {showRemoveButton && (
                        <button
                            onClick={handleRemove}
                            disabled={isLoading}
                            className="flex-1 bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2.5 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg"
                        >
                            {isLoading ? 'Eliminando...' : '🗑️ Eliminar'}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}