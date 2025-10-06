'use client';

import { useActionState } from 'react';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { loginAction, registerAction, ActionResult } from '../actions/auth-actions';

interface ServerAuthFormProps {
    mode: 'login' | 'register';
}

export default function ServerAuthForm({ mode }: ServerAuthFormProps) {
    const router = useRouter();
    const isRegisterMode = mode === 'register';

    const [loginState, loginFormAction, isLoginPending] = useActionState<ActionResult | null, FormData>(
        loginAction,
        null
    );

    const [registerState, registerFormAction, isRegisterPending] = useActionState<ActionResult | null, FormData>(
        registerAction,
        null
    );

    const currentState = isRegisterMode ? registerState : loginState;
    const currentAction = isRegisterMode ? registerFormAction : loginFormAction;
    const isPending = isRegisterMode ? isRegisterPending : isLoginPending;

    // Redirect on successful authentication based on user role
    useEffect(() => {
        console.log('currentState', currentState);
        if (currentState?.success && currentState?.user) {
            // Redirect based on user role
            if (currentState.user.role === 'admin') {
                router.push('/admin');
            } else {
                router.push('/dashboard');
            }
        }
    }, [currentState?.success, currentState?.user, router]);

    const submitButtonText = isRegisterMode ? 'Crear Cuenta' : 'Entrar';
    const loadingText = isRegisterMode ? 'Creando cuenta...' : 'Entrando...';
    const linkText = isRegisterMode ? 'Iniciar sesión' : 'Crear una';
    const linkQuestion = isRegisterMode ? '¿Ya tenés cuenta?' : '¿No tenés cuenta?';
    const linkHref = isRegisterMode ? '/login' : '/register';

    return (
        <div className="w-full max-w-md mx-auto">
            <div className="bg-white shadow-2xl rounded-2xl p-8 border-t-4 border-orange-500">

                {currentState?.error && (
                    <div className="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                        <div className="flex items-center">
                            <span className="text-red-500 mr-2">⚠️</span>
                            <p className="text-red-700 font-medium">{currentState.error}</p>
                        </div>
                    </div>
                )}

                <h1 className="text-2xl font-bold text-gray-900 mb-2 text-center mb-6">
                    {isRegisterMode ? 'Crear Cuenta' : 'Iniciar Sesión'}
                </h1>

                <form action={currentAction} className="space-y-5">
                    {isRegisterMode && (
                        <div>
                            <label htmlFor="username" className="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre de Usuario
                            </label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
                                placeholder="Ingresá tu usuario"
                                disabled={isPending}
                                required
                            />
                            {currentState?.fieldErrors?.username && (
                                <div className="mt-2 space-y-1">
                                    {currentState.fieldErrors.username.map((error, index) => (
                                        <p key={index} className="text-sm text-red-600 flex items-center">
                                            <span className="mr-1">•</span> {error}
                                        </p>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    <div>
                        <label htmlFor="email" className="block text-sm font-semibold text-gray-700 mb-2">
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
                            placeholder="tu@email.com"
                            disabled={isPending}
                            required
                        />
                        {currentState?.fieldErrors?.email && (
                            <div className="mt-2 space-y-1">
                                {currentState.fieldErrors.email.map((error, index) => (
                                    <p key={index} className="text-sm text-red-600 flex items-center">
                                        <span className="mr-1">•</span> {error}
                                    </p>
                                ))}
                            </div>
                        )}
                    </div>

                    <div>
                        <label htmlFor="password" className="block text-sm font-semibold text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                            disabled={isPending}
                            required
                        />
                        {currentState?.fieldErrors?.password && (
                            <div className="mt-2 space-y-1">
                                {currentState.fieldErrors.password.map((error, index) => (
                                    <p key={index} className="text-sm text-red-600 flex items-center">
                                        <span className="mr-1">•</span> {error}
                                    </p>
                                ))}
                            </div>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={isPending}
                        className="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3 px-4 rounded-lg font-bold text-lg hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-300 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        {isPending ? (
                            <span className="flex items-center justify-center">
                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {loadingText}
                            </span>
                        ) : (
                            submitButtonText
                        )}
                    </button>
                </form>

                <div className="mt-8 text-center">
                    <p className="text-gray-600">
                        {linkQuestion}{' '}
                        <a
                            href={linkHref}
                            className="text-orange-600 hover:text-orange-500 font-semibold hover:underline transition-colors"
                        >
                            {linkText}
                        </a>
                    </p>
                </div>

                {/* Back to home link */}
                <div className="mt-6 text-center border-t pt-6">
                    <a
                        href="/"
                        className="text-gray-500 hover:text-gray-700 text-sm transition-colors inline-flex items-center"
                    >
                        <span className="mr-1">←</span> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    );
}