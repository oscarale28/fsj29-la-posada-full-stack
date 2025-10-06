import AccommodationGrid from '@/features/accommodation-management/components/accommodation-grid';
import { getAllAccommodations } from '@/features/accommodation-management/actions/accommodation-actions';
import { getAuthenticatedUser } from '@/features/authentication/actions/auth-helpers';
import Link from 'next/link';

export default async function Home() {
  const accommodations = await getAllAccommodations({ limit: 12 });
  const { isAuthenticated } = await getAuthenticatedUser();

  return (
    <div className="min-h-screen bg-background">
      {/* Hero Section */}
      <div className="relative bg-gradient-to-br from-primary via-secondary to-accent text-primary-foreground overflow-hidden">
        <div className="absolute inset-0 bg-black/10"></div>
        <div className="absolute inset-0" style={{
          backgroundImage: 'url("data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
        }}></div>

        <div className="relative container mx-auto px-4 py-20 md:py-28">
          <div className="max-w-4xl mx-auto text-center">
            <h1 className="text-5xl md:text-7xl font-bold mb-6 leading-tight">
              Bienvenido a <span className="text-accent">La Posada</span>
            </h1>
            <p className="text-xl md:text-2xl text-primary-foreground/90 mb-4">
              ¿Deseas un hospedaje ideal para tu estadía en El Salvador? Hallar posada, como decimos acá, nunca fue tan fácil.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
              {!isAuthenticated ? (
                <>
                  <Link
                    href="/register"
                    className="bg-accent text-accent-foreground px-8 py-4 rounded-xl font-bold text-lg hover:bg-accent/90 transition-all hover:scale-105 shadow-lg hover:shadow-xl"
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
                  className="bg-accent text-accent-foreground px-8 py-4 rounded-xl font-bold text-lg hover:bg-accent/90 transition-all hover:scale-105 shadow-lg"
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
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="oklch(from var(--background) l c h)" />
          </svg>
        </div>
      </div>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-16">
        <div className="mb-12 text-center">
          <h2 className="text-4xl font-bold text-foreground mb-4">
            Hospedajes Disponibles
          </h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Explorá nuestra selección de hospedajes de calidad en todo El Salvador
          </p>
        </div>

        {accommodations.length === 0 ? (
          <div className="text-center py-20 bg-card rounded-2xl shadow-sm border border-border">
            <div className="text-8xl mb-6">🏡</div>
            <h3 className="text-2xl font-bold text-card-foreground mb-3">
              Todavía no hay Hospedajes disponibles
            </h3>
            <p className="text-muted-foreground mb-8 text-lg">
              Volvé pronto para ver nuevos hospedajes
            </p>
            {isAuthenticated && (
              <Link
                href="/dashboard"
                className="inline-block bg-primary text-primary-foreground px-8 py-3 rounded-lg font-semibold hover:bg-primary/90 transition-all shadow-lg hover:shadow-xl"
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
        <div className="bg-gradient-to-r from-secondary to-secondary/90 text-secondary-foreground py-16">
          <div className="container mx-auto px-4 text-center">
            <h2 className="text-3xl md:text-4xl font-bold mb-4">
              ¿Listo para encontrar tu posada?
            </h2>
            <p className="text-xl text-secondary-foreground/80 mb-8 max-w-2xl mx-auto">
              Registrate gratis y empezá a guardar tus Hospedajes favoritos
            </p>
            <Link
              href="/register"
              className="inline-block bg-accent text-accent-foreground px-8 py-4 rounded-xl font-bold text-lg hover:bg-accent/90 transition-all hover:scale-105 shadow-lg"
            >
              Crear Mi Cuenta
            </Link>
          </div>
        </div>
      )}

      {/* Footer */}
      <footer className="bg-foreground text-primary-foreground py-12">
        <div className="container mx-auto px-4">
          <div className="text-center">
            <p className="text-muted-foreground">© 2025 La Posada. Todos los derechos reservados.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
