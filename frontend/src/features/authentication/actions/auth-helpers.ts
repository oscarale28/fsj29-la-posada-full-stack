'use server';

import { cookies } from 'next/headers';

export interface AuthUser {
    id: number;
    username: string;
    email: string;
    role: 'admin' | 'user';
}

export interface AuthResult {
    isAuthenticated: boolean;
    user: AuthUser | null;
    token: string | null;
}

/**
 * Get the currently authenticated user from cookies
 * @returns AuthResult with user data and authentication status
 */
export async function getAuthenticatedUser(): Promise<AuthResult> {
    try {
        const cookieStore = await cookies();
        const authToken = cookieStore.get('auth_token');
        const userData = cookieStore.get('user_data');

        if (!authToken) {
            return {
                isAuthenticated: false,
                user: null,
                token: null,
            };
        }

        let user: AuthUser | null = null;
        if (userData) {
            try {
                user = JSON.parse(userData.value);
            } catch (error) {
                console.error('Failed to parse user data:', error);
            }
        }

        return {
            isAuthenticated: true,
            user,
            token: authToken.value,
        };
    } catch (error) {
        console.error('Error getting authenticated user:', error);
        return {
            isAuthenticated: false,
            user: null,
            token: null,
        };
    }
}

/**
 * Check if user is authenticated, return token only
 * @returns token string or null
 */
export async function getAuthToken(): Promise<string | null> {
    try {
        const cookieStore = await cookies();
        const authToken = cookieStore.get('auth_token');
        return authToken?.value || null;
    } catch (error) {
        console.error('Error getting auth token:', error);
        return null;
    }
}

