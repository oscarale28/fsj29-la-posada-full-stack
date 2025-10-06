'use server';

import { cookies } from 'next/headers';
import { revalidatePath } from 'next/cache';

export interface ActionResult {
    success: boolean;
    error?: string;
    message?: string;
    fieldErrors?: Record<string, string[]>;
}

export async function createAccommodationAction(prevState: ActionResult | null, formData: FormData): Promise<ActionResult> {
    try {
        const cookieStore = await cookies();
        const authToken = cookieStore.get('auth_token');

        if (!authToken) {
            return {
                success: false,
                error: 'Necesitás estar autenticado para realizar esta acción'
            };
        }

        // Extract and validate form data
        const title = formData.get('title') as string;
        const description = formData.get('description') as string;
        const price = parseFloat(formData.get('price') as string);
        const location = formData.get('location') as string;
        const imageUrl = formData.get('imageUrl') as string;
        const amenitiesString = formData.get('amenities') as string;

        // Parse amenities (comma-separated)
        const amenities = amenitiesString
            ? amenitiesString.split(',').map(a => a.trim()).filter(a => a.length > 0)
            : [];

        // Validate required fields
        const fieldErrors: Record<string, string[]> = {};

        if (!title || title.trim().length === 0) {
            fieldErrors.title = ['El título es requerido'];
        }

        if (!description || description.trim().length === 0) {
            fieldErrors.description = ['La descripción es requerida'];
        }

        if (isNaN(price) || price < 0) {
            fieldErrors.price = ['El precio debe ser un número positivo'];
        }

        if (!location || location.trim().length === 0) {
            fieldErrors.location = ['La ubicación es requerida para el hospedaje'];
        }

        if (Object.keys(fieldErrors).length > 0) {
            return {
                success: false,
                fieldErrors,
                error: 'Por favor corregí los errores en el formulario'
            };
        }

        const accommodationData = {
            title: title.trim(),
            description: description.trim(),
            price,
            location: location.trim(),
            image_url: imageUrl && imageUrl.trim() ? imageUrl.trim() : null,
            amenities
        };

        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
        const response = await fetch(`${apiUrl}/admin/accommodations`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken.value}`,
            },
            body: JSON.stringify(accommodationData),
        });

        console.log(`createAccommodationAction response`, response);

        if (!response.ok) {
            return {
                success: false,
                error: 'Error al crear el hospedaje'
            };
        }

        revalidatePath('/admin');

        return {
            success: true,
            message: '¡Hospedaje creado exitosamente!'
        };
    } catch (error) {
        console.error('Error creating accommodation:', error);
        return {
            success: false,
            error: 'Error al conectar con el servidor'
        };
    }
}
