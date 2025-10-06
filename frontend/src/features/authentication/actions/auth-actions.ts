'use server';

import { redirect } from 'next/navigation';
import { cookies } from 'next/headers';
import { loginSchema, registerSchema } from '../validations';
import { apiClient } from '../../../shared/utils/api-client';

export interface ActionResult {
    success: boolean;
    error?: string;
    fieldErrors?: Record<string, string[]>;
    user?: {
        id: string;
        username: string;
        email: string;
        role: 'admin' | 'user';
    };
}

export async function loginAction(prevState: ActionResult | null, formData: FormData): Promise<ActionResult> {
    try {
        // Extract form data
        const rawData = {
            email: formData.get('email') as string,
            password: formData.get('password') as string,
        };

        // Validate form data
        const validationResult = loginSchema.safeParse(rawData);
        if (!validationResult.success) {
            const fieldErrors: Record<string, string[]> = {};
            validationResult.error.issues.forEach((issue) => {
                const field = issue.path[0] as string;
                if (!fieldErrors[field]) {
                    fieldErrors[field] = [];
                }
                fieldErrors[field].push(issue.message);
            });
            return {
                success: false,
                fieldErrors,
            };
        }

        // Make API call to backend
        const response = await apiClient.post<any>('/auth/login', validationResult.data);

        console.log(`loginAction response`, response);

        // Backend returns: { success: true, token: '...', user: {...}, message: '...' }
        if (response.success && response.token && response.user) {
            // Set authentication cookie
            const cookieStore = await cookies();
            cookieStore.set('auth_token', response.token, {
                httpOnly: true,
                secure: process.env.NODE_ENV === 'production',
                sameSite: 'strict',
                maxAge: 60 * 60 * 24 * 7, // 7 days
                path: '/',
            });

            // Store user data in cookie for client-side access
            cookieStore.set('user_data', JSON.stringify(response.user), {
                httpOnly: false,
                secure: process.env.NODE_ENV === 'production',
                sameSite: 'strict',
                maxAge: 60 * 60 * 24 * 7, // 7 days
                path: '/',
            });

            console.log('Cookies set successfully');

            return {
                success: true,
                user: response.user
            };
        }

        return { success: true };
    } catch (error: any) {
        return {
            success: false,
            error: error.message || 'Login failed. Please try again.',
        };
    }
}

export async function registerAction(prevState: ActionResult | null, formData: FormData): Promise<ActionResult> {
    try {
        // Extract form data
        const rawData = {
            username: formData.get('username') as string,
            email: formData.get('email') as string,
            password: formData.get('password') as string,
        };

        // Validate form data
        const validationResult = registerSchema.safeParse(rawData);
        if (!validationResult.success) {
            const fieldErrors: Record<string, string[]> = {};
            validationResult.error.issues.forEach((issue) => {
                const field = issue.path[0] as string;
                if (!fieldErrors[field]) {
                    fieldErrors[field] = [];
                }
                fieldErrors[field].push(issue.message);
            });
            return {
                success: false,
                fieldErrors,
            };
        }

        // Make API call to backend
        const response = await apiClient.post<any>('/auth/register', validationResult.data);

        console.log(`registerAction response`, response);

        // Backend returns: { success: true, token: '...', user: {...}, message: '...' }
        if (response.success && response.token && response.user) {
            // Set authentication cookie
            const cookieStore = await cookies();
            cookieStore.set('auth_token', response.token, {
                httpOnly: true,
                secure: process.env.NODE_ENV === 'production',
                sameSite: 'strict',
                maxAge: 60 * 60 * 24 * 7, // 7 days
                path: '/',
            });

            // Store user data in cookie for client-side access
            cookieStore.set('user_data', JSON.stringify(response.user), {
                httpOnly: false,
                secure: process.env.NODE_ENV === 'production',
                sameSite: 'strict',
                maxAge: 60 * 60 * 24 * 7, // 7 days
                path: '/',
            });

            console.log('Cookies set successfully');

            return {
                success: true,
                user: response.user
            };
        }

        return { success: true };
    } catch (error: any) {
        return {
            success: false,
            error: error.message || 'Registration failed. Please try again.',
        };
    }
}

export async function logoutAction(): Promise<void> {
    const cookieStore = await cookies();
    cookieStore.delete('auth_token');
    cookieStore.delete('user_data');
    redirect('/login');
}