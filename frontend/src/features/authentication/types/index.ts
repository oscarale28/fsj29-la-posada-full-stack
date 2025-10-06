import { LoginFormData, RegisterFormData } from "../validations/index";


// Authentication domain types
export interface User {
    id: string;
    username: string;
    email: string;
    role: 'admin' | 'user';
    createdAt: string;
    updatedAt: string;
}

export interface AuthFormProps {
    mode: 'login' | 'register';
    onSubmit: (data: LoginFormData | RegisterFormData) => Promise<void>;
    isLoading?: boolean;
    error?: string;
}

export interface AuthResponse {
    user: User;
    token: string;
}

// Re-export validation types for convenience
export type { LoginFormData, RegisterFormData };