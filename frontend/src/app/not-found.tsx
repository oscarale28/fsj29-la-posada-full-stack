import Link from 'next/link';

export default function NotFound() {
    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-blue-50 flex items-center justify-center px-4">
            <div className="text-center max-w-2xl">
                {/* Animated 404 */}
                <div className="mb-8">
                    <h1 className="text-9xl font-bold bg-gradient-to-r from-orange-500 to-blue-600 bg-clip-text text-transparent mb-4">
                        404
                    </h1>
                    <div className="text-6xl mb-6">🏠❓</div>
                </div>

                {/* Message */}
                <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    ¡Uy, te perdiste!
                </h2>
                <p className="text-xl text-gray-600 mb-8">
                    La página que buscás no existe. Tal vez la dirección es incorrecta o la página fue movida.
                </p>

                {/* Actions */}
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        href="/"
                        className="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-xl inline-block"
                    >
                        🏠 Volver al Inicio
                    </Link>
                    <Link
                        href="/dashboard"
                        className="bg-white text-gray-900 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-gray-50 transition-all border-2 border-gray-300 inline-block"
                    >
                        Ver Mi Panel
                    </Link>
                </div>

                {/* Helpful links */}
                <div className="mt-12 pt-8 border-t border-gray-200">
                    <p className="text-gray-600 mb-4">Enlaces útiles:</p>
                    <div className="flex flex-wrap gap-4 justify-center">
                        <Link href="/" className="text-orange-600 hover:text-orange-700 font-medium hover:underline">
                            Hospedajes Disponibles
                        </Link>
                        <Link href="/login" className="text-orange-600 hover:text-orange-700 font-medium hover:underline">
                            Iniciar Sesión
                        </Link>
                        <Link href="/register" className="text-orange-600 hover:text-orange-700 font-medium hover:underline">
                            Registrarse
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}

