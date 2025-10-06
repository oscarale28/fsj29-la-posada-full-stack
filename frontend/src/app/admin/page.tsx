import { redirect } from 'next/navigation';
import Link from 'next/link';
import AdminDashboardTabs from '@/features/administration/components/admin-dashboard-tabs';
import { getAllAccommodations } from '@/features/accommodation-management/actions/accommodation-actions';
import { getAuthenticatedUser } from '@/features/authentication/actions/auth-helpers';
import { logoutAction } from '@/features/authentication/actions/auth-actions';

export default async function AdminPage() {
    // Check if user is authenticated and is admin
    const { isAuthenticated, user } = await getAuthenticatedUser();

    if (!isAuthenticated) {
        redirect('/login');
    }

    // Check if user is admin
    if (!user || user.role !== 'admin') {
        redirect('/dashboard');
    }

    // Fetch all accommodations
    const allAccommodations = await getAllAccommodations({ limit: 100 });

    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-blue-50">
            {/* Header/Nav */}
            <header className="bg-white shadow-sm border-b-2 border-green-200">
                <div className="container mx-auto px-4 py-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <Link href="/" className="flex items-center gap-2 hover:opacity-80 transition-opacity">
                                <span className="font-bold text-xl text-gray-900">Panel Admin</span>
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
                {/* Welcome Section */}
                <div className="bg-gradient-to-r from-green-500 via-green-600 to-blue-600 text-white rounded-2xl shadow-xl p-8 md:p-12 mb-12 relative overflow-hidden">
                    <div className="absolute inset-0 opacity-10" style={{
                        backgroundImage: 'url("data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="1"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
                    }}></div>

                    <div className="relative">
                        <div className="flex items-center gap-3 mb-4">
                            <div>
                                <h1 className="text-4xl md:text-5xl font-bold">
                                    Panel de Administración
                                </h1>
                                <p className="text-xl text-green-100 mt-2">
                                    Bienvenido, {user?.username}
                                </p>
                            </div>
                        </div>
                        <p className="text-lg text-white/90">
                            Administrá el catálogo completo de La Posada
                        </p>
                    </div>
                </div>

                {/* Admin Dashboard Tabs */}
                <div className="bg-white rounded-2xl shadow-xl p-8">
                    <AdminDashboardTabs allAccommodations={allAccommodations} />
                </div>
            </main>
        </div>
    );
}

