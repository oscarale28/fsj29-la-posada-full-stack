// Environment configuration
export const env = {
    API_URL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001/api',
    API_VERSION: process.env.NEXT_PUBLIC_API_VERSION || 'v1',
    JWT_SECRET: process.env.JWT_SECRET || 'fallback-secret',
    JWT_EXPIRES_IN: process.env.NEXT_PUBLIC_JWT_EXPIRES_IN || '7d',
    NODE_ENV: process.env.NODE_ENV || 'development',
} as const;

export const getApiUrl = (endpoint: string) => {
    return `${env.API_URL}/${env.API_VERSION}${endpoint}`;
};