import { redirect } from 'next/navigation';
import Link from 'next/link';
import { getUserAccommodations } from '@/features/user-management/actions/user-actions';
import { getAllAccommodations } from '@/features/accommodation-management/actions/accommodation-actions';
import { getAuthenticatedUser } from '@/features/authentication/actions/auth-helpers';
import UserDashboardTabs from '@/features/user-management/components/user-dashboard-tabs';
import { logoutAction } from '@/features/authentication/actions/auth-actions';

export default async function DashboardPage() {
    // Check if user is authenticated
    const { isAuthenticated, user } = await getAuthenticatedUser();

    if (!isAuthenticated) {
        redirect('/login');
    }

    // Redirect admins to their own dashboard
    if (user?.role === 'admin') {
        redirect('/admin');
    }

    // Fetch user's accommodations and all available accommodations
    const [userAccommodations, availableAccommodations] = await Promise.all([
        getUserAccommodations(),
        getAllAccommodations({ limit: 100 })
    ]);

    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-blue-50">
            {/* Header/Nav */}
            <header className="bg-white shadow-sm border-b-2 border-orange-200">
                <div className="container mx-auto px-4 py-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <Link href="/" className="flex items-center gap-2 hover:opacity-80 transition-opacity">
                                <div className="bg-gradient-to-br from-orange-500 to-blue-600 text-white w-10 h-10 rounded-lg flex items-center justify-center text-xl">
                                    🏠
                                </div>
                                <span className="font-bold text-xl text-gray-900">La Posada</span>
                            </Link>
                        </div>

                        <nav className="flex items-center gap-4">
                            <form action={logoutAction}>
                                <button
                                    type="submit"
                                    className="text-red-600 hover:text-red-700 font-medium transition-colors"
                                >
                                    Cerrar Sesión
                                </button>
                            </form>
                        </nav>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="container mx-auto px-4 py-12">
                {/* Welcome Card */}
                <div className="bg-gradient-to-r from-orange-500 via-orange-600 to-blue-600 text-white rounded-2xl shadow-xl p-8 md:p-12 mb-12 relative overflow-hidden">
                    <div className="absolute inset-0 opacity-10" style={{
                        backgroundImage: 'url("data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="1"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
                    }}></div>

                    <div className="relative">
                        <h1 className="text-4xl md:text-5xl font-bold mb-4">
                            ¡Hola, {user?.username || 'Usuario'}! 👋
                        </h1>
                        <p className="text-xl text-orange-100 mb-6">
                            Bienvenido a tu panel personal de La Posada
                        </p>

                        {user && (
                            <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 inline-block">
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <p className="text-orange-100 text-sm mb-1">Usuario</p>
                                        <p className="font-semibold text-lg">{user.username}</p>
                                    </div>
                                    <div>
                                        <p className="text-orange-100 text-sm mb-1">Email</p>
                                        <p className="font-semibold text-lg">{user.email}</p>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* User Dashboard Tabs Section */}
                <div className="bg-white rounded-2xl shadow-lg p-8">
                    <UserDashboardTabs
                        userAccommodations={userAccommodations}
                        availableAccommodations={availableAccommodations}
                        userName={user?.username || 'Usuario'}
                    />
                </div>
            </main>
        </div>
    );
}