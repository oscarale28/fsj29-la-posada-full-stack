import { logoutAction } from '../actions/auth-actions';

export default function LogoutButton() {
    return (
        <form action={logoutAction}>
            <button
                type="submit"
                className="text-destructive hover:text-destructive/90 font-medium"
            >
                Logout
            </button>
        </form>
    );
}