import { z } from 'zod';

// Authentication validation schemas
export const loginSchema = z.object({
    email: z
        .string()
        .min(1, 'Email is required')
        .email('Please enter a valid email address'),
    password: z
        .string()
        .min(6, 'Password must be at least 6 characters long'),
});

export const registerSchema = z.object({
    username: z
        .string()
        .min(3, 'Username must be at least 3 characters long')
        .max(50, 'Username must be less than 50 characters')
        .regex(/^\w+$/, 'Username can only contain letters, numbers, and underscores'),
    email: z
        .string()
        .min(1, 'Email is required')
        .email('Please enter a valid email address'),
    password: z
        .string()
        .min(6, 'Password must be at least 6 characters long')
        .max(100, 'Password must be less than 100 characters'),
});

// Type exports
export type LoginFormData = z.infer<typeof loginSchema>;
export type RegisterFormData = z.infer<typeof registerSchema>;