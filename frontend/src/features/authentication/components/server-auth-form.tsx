'use client';

import { useActionState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
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
    }, [currentState, router]);

    const submitButtonText = isRegisterMode ? 'Crear Cuenta' : 'Entrar';
    const loadingText = isRegisterMode ? 'Creando cuenta...' : 'Entrando...';
    const linkText = isRegisterMode ? 'Iniciar sesión' : 'Crear una';
    const linkQuestion = isRegisterMode ? '¿Ya tenés cuenta?' : '¿No tenés cuenta?';
    const linkHref = isRegisterMode ? '/login' : '/register';

    return (
        <div className="w-full max-w-md mx-auto">
            <div className="bg-card shadow-2xl rounded-2xl p-8 border-t-4 border-primary">

                {currentState?.error && (
                    <div className="mb-6 p-4 bg-destructive/10 border-l-4 border-destructive rounded-r-lg">
                        <div className="flex items-center">
                            <span className="text-destructive mr-2">⚠️</span>
                            <p className="text-destructive font-medium">{currentState.error}</p>
                        </div>
                    </div>
                )}

                <h1 className="text-2xl font-bold text-card-foreground mb-2 text-center mb-6">
                    {isRegisterMode ? 'Crear Cuenta' : 'Iniciar Sesión'}
                </h1>

                <form action={currentAction} className="space-y-5">
                    {isRegisterMode && (
                        <div>
                            <label htmlFor="username" className="block text-sm font-semibold text-foreground mb-2">
                                Nombre de Usuario
                            </label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all bg-background text-foreground"
                                placeholder="Ingresá tu usuario"
                                disabled={isPending}
                                required
                            />
                            {currentState?.fieldErrors?.username && (
                                <div className="mt-2 space-y-1">
                                    {currentState.fieldErrors.username.map((error, index) => (
                                        <p key={index} className="text-sm text-destructive flex items-center">
                                            <span className="mr-1">•</span> {error}
                                        </p>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    <div>
                        <label htmlFor="email" className="block text-sm font-semibold text-foreground mb-2">
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all bg-background text-foreground"
                            placeholder="tu@email.com"
                            disabled={isPending}
                            required
                        />
                        {currentState?.fieldErrors?.email && (
                            <div className="mt-2 space-y-1">
                                {currentState.fieldErrors.email.map((error, index) => (
                                    <p key={index} className="text-sm text-destructive flex items-center">
                                        <span className="mr-1">•</span> {error}
                                    </p>
                                ))}
                            </div>
                        )}
                    </div>

                    <div>
                        <label htmlFor="password" className="block text-sm font-semibold text-foreground mb-2">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            className="w-full px-4 py-3 border-2 border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all bg-background text-foreground"
                            placeholder="••••••••"
                            disabled={isPending}
                            required
                        />
                        {currentState?.fieldErrors?.password && (
                            <div className="mt-2 space-y-1">
                                {currentState.fieldErrors.password.map((error, index) => (
                                    <p key={index} className="text-sm text-destructive flex items-center">
                                        <span className="mr-1">•</span> {error}
                                    </p>
                                ))}
                            </div>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={isPending}
                        className="w-full bg-primary text-primary-foreground py-3 px-4 rounded-lg font-bold text-lg hover:bg-primary/90 focus:outline-none focus:ring-4 focus:ring-ring disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
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
                    <p className="text-muted-foreground">
                        {linkQuestion}{' '}
                        <Link
                            href={linkHref}
                            className="text-primary hover:text-primary/90 font-semibold hover:underline transition-colors"
                        >
                            {linkText}
                        </Link>
                    </p>
                </div>

                {/* Back to home link */}
                <div className="mt-6 text-center border-t border-border pt-6">
                    <Link
                        href="/"
                        className="text-muted-foreground hover:text-foreground text-sm transition-colors inline-flex items-center"
                    >
                        <span className="mr-1">←</span> Volver al inicio
                    </Link>
                </div>
            </div>
        </div>
    );
}