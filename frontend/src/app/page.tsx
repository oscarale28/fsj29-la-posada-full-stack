import AccommodationGrid from '@/features/accommodation-management/components/accommodation-grid';
import { getAllAccommodations } from '@/features/accommodation-management/actions/accommodation-actions';
import { getAuthenticatedUser } from '@/features/authentication/actions/auth-helpers';
import Link from 'next/link';

export default async function Home() {
  const accommodations = await getAllAccommodations({ limit: 12 });
  const { isAuthenticated } = await getAuthenticatedUser();

  return (
    <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-blue-50">
      {/* Hero Section */}
      <div className="relative bg-gradient-to-br from-orange-500 via-orange-600 to-blue-600 text-white overflow-hidden">
        <div className="absolute inset-0 bg-black/10"></div>
        <div className="absolute inset-0" style={{
          backgroundImage: 'url("data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
        }}></div>

        <div className="relative container mx-auto px-4 py-20 md:py-28">
          <div className="max-w-4xl mx-auto text-center">
            <h1 className="text-5xl md:text-7xl font-bold mb-6 leading-tight">
              Bienvenido a <span className="text-yellow-300">La Posada</span>
            </h1>
            <p className="text-xl md:text-2xl text-orange-100 mb-4">
              ¿Deseas un hospedaje ideal para tu estadía en El Salvador? Hallar posada, como decimos acá, nunca fue tan fácil.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
              {!isAuthenticated ? (
                <>
                  <Link
                    href="/register"
                    className="bg-yellow-400 text-gray-900 px-8 py-4 rounded-xl font-bold text-lg hover:bg-yellow-300 transition-all hover:scale-105 shadow-lg hover:shadow-xl"
                  >
                    Crear Cuenta Gratis
                  </Link>
                  <Link
                    href="/login"
                    className="bg-white/10 backdrop-blur-sm text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-white/20 transition-all border-2 border-white/30"
                  >
                    Iniciar Sesión
                  </Link>
                </>
              ) : (
                <Link
                  href="/dashboard"
                  className="bg-yellow-400 text-gray-900 px-8 py-4 rounded-xl font-bold text-lg hover:bg-yellow-300 transition-all hover:scale-105 shadow-lg"
                >
                  Ir a Mi Panel
                </Link>
              )}
            </div>
          </div>
        </div>

        {/* Wave decoration */}
        <div className="absolute bottom-0 left-0 right-0">
          <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" className="w-full">
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#fffbf0" />
          </svg>
        </div>
      </div>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-16">
        <div className="mb-12 text-center">
          <h2 className="text-4xl font-bold text-gray-900 mb-4">
            Hospedajes Disponibles
          </h2>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">
            Explorá nuestra selección de hospedajes de calidad en todo El Salvador
          </p>
        </div>

        {accommodations.length === 0 ? (
          <div className="text-center py-20 bg-white rounded-2xl shadow-sm">
            <div className="text-8xl mb-6">🏡</div>
            <h3 className="text-2xl font-bold text-gray-800 mb-3">
              Todavía no hay Hospedajes disponibles
            </h3>
            <p className="text-gray-600 mb-8 text-lg">
              Volvé pronto para ver nuevos hospedajes
            </p>
            {isAuthenticated && (
              <Link
                href="/dashboard"
                className="inline-block bg-gradient-to-r from-orange-500 to-orange-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-xl"
              >
                Ver Mi Panel
              </Link>
            )}
          </div>
        ) : (
          <AccommodationGrid
            accommodations={accommodations}
            showSelectButton={false}
            showRemoveButton={false}
            emptyMessage="No se encontraron Hospedajes."
          />
        )}
      </main>

      {/* CTA Section */}
      {!isAuthenticated && (
        <div className="bg-gradient-to-r from-blue-600 to-blue-700 text-white py-16">
          <div className="container mx-auto px-4 text-center">
            <h2 className="text-3xl md:text-4xl font-bold mb-4">
              ¿Listo para encontrar tu posada?
            </h2>
            <p className="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
              Registrate gratis y empezá a guardar tus Hospedajes favoritos
            </p>
            <Link
              href="/register"
              className="inline-block bg-yellow-400 text-gray-900 px-8 py-4 rounded-xl font-bold text-lg hover:bg-yellow-300 transition-all hover:scale-105 shadow-lg"
            >
              Crear Mi Cuenta
            </Link>
          </div>
        </div>
      )}

      {/* Footer */}
      <footer className="bg-gray-900 text-white py-12">
        <div className="container mx-auto px-4">
          <div className="text-center">
            <p className="text-gray-300">© 2025 La Posada. Todos los derechos reservados.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
