'use client';

import { useActionState } from 'react';
import { useRouter } from 'next/navigation';
import { createAccommodationAction, ActionResult } from '../actions/admin-actions';

export default function AdminAccommodationForm() {
    const router = useRouter();
    const [state, formAction, isPending] = useActionState<ActionResult | null, FormData>(
        createAccommodationAction,
        null
    );

    return (
        <div className="bg-card rounded-2xl shadow-xl p-8 border-t-4 border-primary">
            <div className="mb-8">
                <h2 className="text-3xl font-bold text-card-foreground mb-2">
                    Agregar Nuevo Hospedaje
                </h2>
                <p className="text-muted-foreground">
                    Completá el formulario para agregar un nuevo hospedaje al catálogo
                </p>
            </div>

            {state?.success && (
                <div className="mb-6 p-4 bg-primary/10 border-l-4 border-primary rounded-r-lg">
                    <div className="flex items-center">
                        <span className="text-primary mr-2 text-2xl">✓</span>
                        <div>
                            <p className="text-primary font-medium">{state.message}</p>
                        </div>
                    </div>
                </div>
            )}

            {state?.error && !state?.fieldErrors && (
                <div className="mb-6 p-4 bg-destructive/10 border-l-4 border-destructive rounded-r-lg">
                    <div className="flex items-center">
                        <span className="text-destructive mr-2">⚠️</span>
                        <p className="text-destructive font-medium">{state.error}</p>
                    </div>
                </div>
            )}

            <form action={formAction} className="space-y-6">
                {/* Title */}
                <div>
                    <label htmlFor="title" className="block text-sm font-semibold text-foreground mb-2">
                        Nombre del Hospedaje *
                    </label>
                    <input
                        id="title"
                        name="title"
                        type="text"
                        className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all bg-background text-foreground"
                        placeholder="Ej: Hotel Colonial San Salvador"
                        disabled={isPending}
                        required
                    />
                    {state?.fieldErrors?.title && (
                        <div className="mt-2">
                            {state.fieldErrors.title.map((error, index) => (
                                <p key={index} className="text-sm text-destructive flex items-center">
                                    <span className="mr-1">•</span> {error}
                                </p>
                            ))}
                        </div>
                    )}
                </div>

                {/* Description */}
                <div>
                    <label htmlFor="description" className="block text-sm font-semibold text-foreground mb-2">
                        Descripción *
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows={4}
                        className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all resize-none bg-background text-foreground"
                        placeholder="Describe el hospedaje, sus características principales y lo que lo hace especial..."
                        disabled={isPending}
                        required
                    />
                    {state?.fieldErrors?.description && (
                        <div className="mt-2">
                            {state.fieldErrors.description.map((error, index) => (
                                <p key={index} className="text-sm text-destructive flex items-center">
                                    <span className="mr-1">•</span> {error}
                                </p>
                            ))}
                        </div>
                    )}
                </div>

                {/* Price and Location in Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Price */}
                    <div>
                        <label htmlFor="price" className="block text-sm font-semibold text-foreground mb-2">
                            Precio por Noche (USD) *
                        </label>
                        <input
                            id="price"
                            name="price"
                            type="number"
                            step="0.01"
                            min="0"
                            className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all bg-background text-foreground"
                            placeholder="50.00"
                            disabled={isPending}
                            required
                        />
                        {state?.fieldErrors?.price && (
                            <div className="mt-2">
                                {state.fieldErrors.price.map((error, index) => (
                                    <p key={index} className="text-sm text-destructive flex items-center">
                                        <span className="mr-1">•</span> {error}
                                    </p>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Location */}
                    <div>
                        <label htmlFor="location" className="block text-sm font-semibold text-foreground mb-2">
                            Ubicación *
                        </label>
                        <input
                            id="location"
                            name="location"
                            type="text"
                            className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all bg-background text-foreground"
                            placeholder="San Salvador, El Salvador"
                            disabled={isPending}
                            required
                        />
                        {state?.fieldErrors?.location && (
                            <div className="mt-2">
                                {state.fieldErrors.location.map((error, index) => (
                                    <p key={index} className="text-sm text-destructive flex items-center">
                                        <span className="mr-1">•</span> {error}
                                    </p>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Image URL */}
                <div>
                    <label htmlFor="imageUrl" className="block text-sm font-semibold text-foreground mb-2">
                        URL de Imagen
                    </label>
                    <input
                        id="imageUrl"
                        name="imageUrl"
                        type="url"
                        className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all bg-background text-foreground"
                        placeholder="https://ejemplo.com/imagen.jpg"
                        disabled={isPending}
                    />
                    <p className="text-sm text-muted-foreground mt-1">Opcional: URL de la imagen principal del hospedaje</p>
                </div>

                {/* Amenities */}
                <div>
                    <label htmlFor="amenities" className="block text-sm font-semibold text-foreground mb-2">
                        Comodidades
                    </label>
                    <input
                        id="amenities"
                        name="amenities"
                        type="text"
                        className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all bg-background text-foreground"
                        placeholder="wifi, piscina, estacionamiento, aire acondicionado"
                        disabled={isPending}
                    />
                    <p className="text-sm text-muted-foreground mt-1">Separadas por comas</p>
                </div>

                {/* Submit Buttons */}
                <div className="flex gap-4 pt-4">
                    <button
                        type="submit"
                        disabled={isPending}
                        className="flex-1 bg-primary text-primary-foreground py-3 px-6 rounded-lg font-bold text-lg hover:bg-primary/90 focus:outline-none focus:ring-4 focus:ring-ring disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl"
                    >
                        {isPending ? (
                            <span className="flex items-center justify-center">
                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creando hospedaje...
                            </span>
                        ) : (
                            '✓ Crear Hospedaje'
                        )}
                    </button>

                    <button
                        type="button"
                        onClick={() => router.push('/')}
                        disabled={isPending}
                        className="px-6 py-3 border-2 border-border text-foreground rounded-lg font-semibold hover:bg-muted/50 transition-colors disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    );
}

