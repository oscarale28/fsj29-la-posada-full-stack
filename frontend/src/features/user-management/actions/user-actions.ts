'use server';

import { cookies } from 'next/headers';
import { revalidatePath } from 'next/cache';

export interface ActionResult {
    success: boolean;
    error?: string;
    message?: string;
}

export async function addAccommodationToUserAction(accommodationId: number): Promise<ActionResult> {
    try {
        const cookieStore = await cookies();
        const authToken = cookieStore.get('auth_token');

        if (!authToken) {
            return {
                success: false,
                error: 'Necesitás estar autenticado para realizar esta acción'
            };
        }

        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
        const response = await fetch(`${apiUrl}/users/accommodations`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken.value}`,
            },
            body: JSON.stringify({ accommodation_id: accommodationId }),
        });

        const data = await response.json();

        if (!response.ok) {
            return {
                success: false,
                error: data.message || 'Error al agregar el hospedaje a tu cuenta'
            };
        }

        // Revalidate dashboard to show new accommodation
        revalidatePath('/dashboard');

        return {
            success: true,
            message: '¡Hospedaje agregado exitosamente a tu cuenta!'
        };
    } catch (error) {
        console.error('Error adding accommodation:', error);
        return {
            success: false,
            error: 'Error al conectar con el servidor'
        };
    }
}

export async function removeAccommodationFromUserAction(accommodationId: number): Promise<ActionResult> {
    try {
        const cookieStore = await cookies();
        const authToken = cookieStore.get('auth_token');

        if (!authToken) {
            return {
                success: false,
                error: 'Necesitás estar autenticado para realizar esta acción'
            };
        }

        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
        const response = await fetch(`${apiUrl}/users/accommodations/${accommodationId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${authToken.value}`,
            },
        });

        const data = await response.json();

        if (!response.ok) {
            return {
                success: false,
                error: data.message || 'Error al eliminar el hospedaje de tu cuenta'
            };
        }

        // Revalidate dashboard to remove accommodation
        revalidatePath('/dashboard');

        return {
            success: true,
            message: 'Hospedaje eliminado de tu cuenta'
        };
    } catch (error) {
        console.error('Error removing accommodation:', error);
        return {
            success: false,
            error: 'Error al conectar con el servidor'
        };
    }
}

export async function getUserAccommodations() {
    try {
        const cookieStore = await cookies();
        const authToken = cookieStore.get('auth_token');

        if (!authToken) {
            return [];
        }

        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
        const response = await fetch(`${apiUrl}/users/accommodations`, {
            headers: {
                'Authorization': `Bearer ${authToken.value}`,
            },
            cache: 'no-store',
        });

        if (!response.ok) {
            return [];
        }

        const data = await response.json();
        return data.data?.accommodations || [];
    } catch (error) {
        console.error('Error fetching user accommodations:', error);
        return [];
    }
}