// Shared TypeScript types
export interface User {
    id: string;
    email: string;
    role: 'admin' | 'user';
}

export interface Accommodation {
    id: string;
    name: string;
    description: string;
    location: string;
}