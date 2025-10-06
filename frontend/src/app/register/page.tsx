import { redirect } from 'next/navigation';
import { getAuthenticatedUser } from '@/features/authentication/actions/auth-helpers';
import ServerAuthForm from '../../features/authentication/components/server-auth-form';

export default async function RegisterPage() {
    // Check if user is already authenticated
    const { isAuthenticated } = await getAuthenticatedUser();

    if (isAuthenticated) {
        redirect('/dashboard');
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 via-white to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
            <div className="w-full max-w-md">
                <ServerAuthForm mode="register" />
            </div>
        </div>
    );
}