import { z } from 'zod';

// Accommodation validation schemas
export const accommodationSchema = z.object({
    title: z
        .string()
        .min(1, 'Title is required')
        .max(200, 'Title must be less than 200 characters'),
    description: z
        .string()
        .min(1, 'Description is required')
        .max(1000, 'Description must be less than 1000 characters'),
    price: z
        .number()
        .min(0, 'Price must be a positive number'),
    location: z
        .string()
        .min(1, 'Location is required')
        .max(200, 'Location must be less than 200 characters'),
    imageUrl: z
        .string()
        .url('Please enter a valid URL')
        .optional()
        .or(z.literal('')),
    amenities: z
        .array(z.string())
        .default([]),
});

export const accommodationFilterSchema = z.object({
    search: z.string().optional(),
    minPrice: z.number().min(0).optional(),
    maxPrice: z.number().min(0).optional(),
    location: z.string().optional(),
    amenities: z.array(z.string()).optional(),
});

// Type exports
export type AccommodationFormData = z.infer<typeof accommodationSchema>;
export type AccommodationFilterData = z.infer<typeof accommodationFilterSchema>;